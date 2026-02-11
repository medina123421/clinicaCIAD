-- ============================================================================
-- CONSULTAS SQL DE EJEMPLO
-- Ejemplos útiles para la aplicación web
-- ============================================================================

USE clinica_diabetes;

-- ============================================================================
-- CONSULTAS DE PACIENTES
-- ============================================================================

-- 1. Buscar paciente por nombre o expediente
SELECT 
    p.id_paciente,
    p.numero_expediente,
    CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', IFNULL(p.apellido_materno, '')) AS nombre_completo,
    p.edad,
    p.sexo,
    p.telefono,
    p.celular,
    p.email
FROM pacientes p
WHERE p.activo = TRUE
  AND (p.numero_expediente LIKE '%BUSQUEDA%' 
   OR p.nombre LIKE '%BUSQUEDA%' 
   OR p.apellido_paterno LIKE '%BUSQUEDA%')
ORDER BY p.apellido_paterno, p.nombre;

-- 2. Obtener información completa de un paciente
SELECT 
    p.*,
    COUNT(DISTINCT v.id_visita) AS total_visitas,
    MAX(v.fecha_visita) AS ultima_visita,
    COUNT(DISTINCT t.id_tratamiento) AS tratamientos_activos
FROM pacientes p
LEFT JOIN visitas v ON p.id_paciente = v.id_paciente
LEFT JOIN tratamientos t ON p.id_paciente = t.id_paciente AND t.activo = TRUE
WHERE p.id_paciente = ?
GROUP BY p.id_paciente;

-- 3. Pacientes con próxima cita
SELECT 
    p.numero_expediente,
    CONCAT(p.nombre, ' ', p.apellido_paterno) AS nombre_paciente,
    v.proxima_cita,
    DATEDIFF(v.proxima_cita, CURDATE()) AS dias_restantes
FROM pacientes p
JOIN visitas v ON p.id_paciente = v.id_paciente
WHERE v.proxima_cita IS NOT NULL
  AND v.proxima_cita >= CURDATE()
  AND p.activo = TRUE
ORDER BY v.proxima_cita ASC;

-- ============================================================================
-- CONSULTAS DE VISITAS Y DATOS CLÍNICOS
-- ============================================================================

-- 4. Historial de visitas de un paciente
SELECT 
    v.id_visita,
    v.fecha_visita,
    v.tipo_visita,
    v.motivo_consulta,
    v.diagnostico,
    CONCAT(u.nombre, ' ', u.apellido_paterno) AS doctor,
    dc.peso,
    dc.imc,
    dc.presion_arterial_sistolica,
    dc.presion_arterial_diastolica,
    dc.glucosa_capilar
FROM visitas v
JOIN usuarios u ON v.id_doctor = u.id_usuario
LEFT JOIN datos_clinicos dc ON v.id_visita = dc.id_visita
WHERE v.id_paciente = ?
ORDER BY v.fecha_visita DESC;

-- 5. Última visita con datos clínicos completos
SELECT 
    v.*,
    dc.*,
    CONCAT(u.nombre, ' ', u.apellido_paterno) AS doctor
FROM visitas v
JOIN datos_clinicos dc ON v.id_visita = dc.id_visita
JOIN usuarios u ON v.id_doctor = u.id_usuario
WHERE v.id_paciente = ?
ORDER BY v.fecha_visita DESC
LIMIT 1;

-- ============================================================================
-- CONSULTAS DE ANÁLISIS CLÍNICOS CON INTERPRETACIÓN
-- ============================================================================

-- 6. Historial de glucosa y HbA1c con interpretación
SELECT 
    ag.fecha_analisis,
    ag.glucosa_ayunas,
    ag.glucosa_postprandial_2h,
    ag.hemoglobina_glicosilada,
    ag.interpretacion_glucosa_ayunas,
    ag.interpretacion_hba1c,
    CASE 
        WHEN ag.interpretacion_hba1c = 'Normal' THEN '🟢'
        WHEN ag.interpretacion_hba1c = 'Precaución' THEN '🟡'
        WHEN ag.interpretacion_hba1c = 'Alerta' THEN '🔴'
    END AS semaforo
FROM analisis_glucosa ag
JOIN visitas v ON ag.id_visita = v.id_visita
WHERE v.id_paciente = ?
ORDER BY ag.fecha_analisis DESC;

-- 7. Último análisis completo de un paciente
SELECT 
    v.fecha_visita,
    ag.glucosa_ayunas,
    ag.hemoglobina_glicosilada,
    ag.interpretacion_hba1c,
    apl.colesterol_total,
    apl.ldl,
    apl.hdl,
    apl.trigliceridos,
    apr.creatinina_serica,
    apr.tasa_filtracion_glomerular,
    apr.interpretacion_tfg
FROM visitas v
LEFT JOIN analisis_glucosa ag ON v.id_visita = ag.id_visita
LEFT JOIN analisis_perfil_lipidico apl ON v.id_visita = apl.id_visita
LEFT JOIN analisis_perfil_renal apr ON v.id_visita = apr.id_visita
WHERE v.id_paciente = ?
ORDER BY v.fecha_visita DESC
LIMIT 1;

-- 8. Comparar último análisis con el anterior
SELECT 
    'Actual' AS periodo,
    ag.fecha_analisis,
    ag.glucosa_ayunas,
    ag.hemoglobina_glicosilada,
    apl.colesterol_total,
    apl.ldl
FROM analisis_glucosa ag
JOIN visitas v ON ag.id_visita = v.id_visita
LEFT JOIN analisis_perfil_lipidico apl ON v.id_visita = apl.id_visita
WHERE v.id_paciente = ?
ORDER BY ag.fecha_analisis DESC
LIMIT 1

UNION ALL

SELECT 
    'Anterior' AS periodo,
    ag.fecha_analisis,
    ag.glucosa_ayunas,
    ag.hemoglobina_glicosilada,
    apl.colesterol_total,
    apl.ldl
FROM analisis_glucosa ag
JOIN visitas v ON ag.id_visita = v.id_visita
LEFT JOIN analisis_perfil_lipidico apl ON v.id_visita = apl.id_visita
WHERE v.id_paciente = ?
ORDER BY ag.fecha_analisis DESC
LIMIT 1 OFFSET 1;

-- ============================================================================
-- CONSULTAS PARA GRÁFICAS
-- ============================================================================

-- 9. Datos para gráfica de glucosa en ayunas (últimos 12 meses)
SELECT 
    ag.fecha_analisis AS fecha,
    ag.glucosa_ayunas AS valor,
    ag.interpretacion_glucosa_ayunas AS interpretacion
FROM analisis_glucosa ag
JOIN visitas v ON ag.id_visita = v.id_visita
WHERE v.id_paciente = ?
  AND ag.fecha_analisis >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
  AND ag.glucosa_ayunas IS NOT NULL
ORDER BY ag.fecha_analisis ASC;

-- 10. Datos para gráfica de HbA1c (últimos 24 meses)
SELECT 
    ag.fecha_analisis AS fecha,
    ag.hemoglobina_glicosilada AS valor,
    ag.interpretacion_hba1c AS interpretacion,
    CASE 
        WHEN ag.hemoglobina_glicosilada < 5.7 THEN 'Normal'
        WHEN ag.hemoglobina_glicosilada < 6.5 THEN 'Prediabetes'
        ELSE 'Diabetes'
    END AS categoria
FROM analisis_glucosa ag
JOIN visitas v ON ag.id_visita = v.id_visita
WHERE v.id_paciente = ?
  AND ag.fecha_analisis >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
  AND ag.hemoglobina_glicosilada IS NOT NULL
ORDER BY ag.fecha_analisis ASC;

-- 11. Datos para gráfica de peso e IMC
SELECT 
    v.fecha_visita AS fecha,
    dc.peso,
    dc.imc,
    CASE 
        WHEN dc.imc < 18.5 THEN 'Bajo peso'
        WHEN dc.imc < 25 THEN 'Normal'
        WHEN dc.imc < 30 THEN 'Sobrepeso'
        ELSE 'Obesidad'
    END AS categoria_imc
FROM datos_clinicos dc
JOIN visitas v ON dc.id_visita = v.id_visita
WHERE v.id_paciente = ?
  AND dc.peso IS NOT NULL
  AND dc.imc IS NOT NULL
ORDER BY v.fecha_visita ASC;

-- 12. Datos para gráfica de presión arterial
SELECT 
    v.fecha_visita AS fecha,
    dc.presion_arterial_sistolica AS sistolica,
    dc.presion_arterial_diastolica AS diastolica,
    CASE 
        WHEN dc.presion_arterial_sistolica < 120 THEN 'Normal'
        WHEN dc.presion_arterial_sistolica < 140 THEN 'Prehipertensión'
        ELSE 'Hipertensión'
    END AS categoria
FROM datos_clinicos dc
JOIN visitas v ON dc.id_visita = v.id_visita
WHERE v.id_paciente = ?
  AND dc.presion_arterial_sistolica IS NOT NULL
ORDER BY v.fecha_visita ASC;

-- 13. Datos para gráfica de perfil lipídico
SELECT 
    apl.fecha_analisis AS fecha,
    apl.colesterol_total,
    apl.ldl,
    apl.hdl,
    apl.trigliceridos
FROM analisis_perfil_lipidico apl
JOIN visitas v ON apl.id_visita = v.id_visita
WHERE v.id_paciente = ?
  AND apl.fecha_analisis >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
ORDER BY apl.fecha_analisis ASC;

-- ============================================================================
-- CONSULTAS DE TRATAMIENTOS
-- ============================================================================

-- 14. Tratamientos activos de un paciente
SELECT 
    mc.nombre_generico,
    mc.nombre_comercial,
    mc.categoria,
    t.dosis,
    t.frecuencia,
    t.fecha_inicio,
    DATEDIFF(CURDATE(), t.fecha_inicio) AS dias_tratamiento,
    t.indicaciones
FROM tratamientos t
JOIN medicamentos_catalogo mc ON t.id_medicamento = mc.id_medicamento
WHERE t.id_paciente = ?
  AND t.activo = TRUE
ORDER BY mc.categoria, mc.nombre_generico;

-- 15. Historial de ajustes de un tratamiento
SELECT 
    at.fecha_ajuste,
    at.dosis_anterior,
    at.dosis_nueva,
    at.motivo_ajuste,
    CONCAT(u.nombre, ' ', u.apellido_paterno) AS doctor
FROM ajustes_tratamiento at
JOIN usuarios u ON at.created_by = u.id_usuario
WHERE at.id_tratamiento = ?
ORDER BY at.fecha_ajuste DESC;

-- ============================================================================
-- CONSULTAS DE GLUCOMETRÍAS Y EVENTOS
-- ============================================================================

-- 16. Glucometrías recientes (últimos 30 días)
SELECT 
    DATE(g.fecha_hora) AS fecha,
    g.momento,
    g.glucosa,
    g.observaciones,
    CASE 
        WHEN g.glucosa < 70 THEN '🔴 Hipoglucemia'
        WHEN g.glucosa <= 140 THEN '🟢 Normal'
        WHEN g.glucosa <= 180 THEN '🟡 Elevada'
        ELSE '🔴 Muy alta'
    END AS estado
FROM glucometrias g
WHERE g.id_paciente = ?
  AND g.fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
ORDER BY g.fecha_hora DESC;

-- 17. Promedio de glucosa por momento del día (últimos 30 días)
SELECT 
    g.momento,
    COUNT(*) AS mediciones,
    ROUND(AVG(g.glucosa), 2) AS promedio,
    ROUND(MIN(g.glucosa), 2) AS minimo,
    ROUND(MAX(g.glucosa), 2) AS maximo
FROM glucometrias g
WHERE g.id_paciente = ?
  AND g.fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY g.momento
ORDER BY FIELD(g.momento, 'Ayunas', 'Preprandial', 'Postprandial', 'Antes de dormir', 'Madrugada', 'Otro');

-- 18. Eventos de hipoglucemia (últimos 6 meses)
SELECT 
    h.fecha_hora,
    h.glucosa,
    h.sintomas,
    h.severidad,
    h.tratamiento_aplicado
FROM hipoglucemias h
WHERE h.id_paciente = ?
  AND h.fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
ORDER BY h.fecha_hora DESC;

-- ============================================================================
-- CONSULTAS DE COMPLICACIONES
-- ============================================================================

-- 19. Última evaluación de complicaciones microvasculares
SELECT 
    v.fecha_visita,
    cm.revision_pies,
    cm.ulcera_pies,
    cm.neuropatia,
    cm.nefropatia_diabetica,
    cm.retinopatia_diabetica,
    cm.ultima_revision_oftalmologica,
    cm.observaciones
FROM complicaciones_microvasculares cm
JOIN visitas v ON cm.id_visita = v.id_visita
WHERE v.id_paciente = ?
ORDER BY v.fecha_visita DESC
LIMIT 1;

-- 20. Última evaluación de complicaciones macrovasculares
SELECT 
    v.fecha_visita,
    cmac.enfermedad_coronaria,
    cmac.infarto_previo,
    cmac.claudicacion_intermitente,
    cmac.evento_cerebrovascular,
    cmac.observaciones
FROM complicaciones_macrovasculares cmac
JOIN visitas v ON cmac.id_visita = v.id_visita
WHERE v.id_paciente = ?
ORDER BY v.fecha_visita DESC
LIMIT 1;

-- ============================================================================
-- CONSULTAS DE REPORTES Y ESTADÍSTICAS
-- ============================================================================

-- 21. Resumen de control del paciente
SELECT 
    p.numero_expediente,
    CONCAT(p.nombre, ' ', p.apellido_paterno) AS nombre_paciente,
    p.edad,
    TIMESTAMPDIFF(YEAR, p.fecha_registro, CURDATE()) AS años_seguimiento,
    COUNT(DISTINCT v.id_visita) AS total_visitas,
    MAX(v.fecha_visita) AS ultima_visita,
    (SELECT ag.hemoglobina_glicosilada 
     FROM analisis_glucosa ag 
     JOIN visitas v2 ON ag.id_visita = v2.id_visita 
     WHERE v2.id_paciente = p.id_paciente 
     ORDER BY ag.fecha_analisis DESC LIMIT 1) AS ultima_hba1c,
    (SELECT dc.imc 
     FROM datos_clinicos dc 
     JOIN visitas v3 ON dc.id_visita = v3.id_visita 
     WHERE v3.id_paciente = p.id_paciente 
     ORDER BY v3.fecha_visita DESC LIMIT 1) AS ultimo_imc,
    COUNT(DISTINCT t.id_tratamiento) AS tratamientos_activos
FROM pacientes p
LEFT JOIN visitas v ON p.id_paciente = v.id_paciente
LEFT JOIN tratamientos t ON p.id_paciente = t.id_paciente AND t.activo = TRUE
WHERE p.id_paciente = ?
GROUP BY p.id_paciente;

-- 22. Pacientes con control inadecuado (HbA1c > 7%)
SELECT 
    p.numero_expediente,
    CONCAT(p.nombre, ' ', p.apellido_paterno) AS nombre_paciente,
    ag.hemoglobina_glicosilada,
    ag.fecha_analisis,
    DATEDIFF(CURDATE(), ag.fecha_analisis) AS dias_desde_analisis
FROM pacientes p
JOIN visitas v ON p.id_paciente = v.id_paciente
JOIN analisis_glucosa ag ON v.id_visita = ag.id_visita
WHERE p.activo = TRUE
  AND ag.hemoglobina_glicosilada > 7.0
  AND ag.id_analisis IN (
      SELECT ag2.id_analisis
      FROM analisis_glucosa ag2
      JOIN visitas v2 ON ag2.id_visita = v2.id_visita
      WHERE v2.id_paciente = p.id_paciente
      ORDER BY ag2.fecha_analisis DESC
      LIMIT 1
  )
ORDER BY ag.hemoglobina_glicosilada DESC;

-- 23. Estadísticas generales de la clínica
SELECT 
    COUNT(DISTINCT p.id_paciente) AS total_pacientes_activos,
    COUNT(DISTINCT CASE WHEN v.fecha_visita >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN p.id_paciente END) AS pacientes_vistos_ultimo_mes,
    COUNT(DISTINCT v.id_visita) AS total_visitas,
    COUNT(DISTINCT CASE WHEN v.fecha_visita >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN v.id_visita END) AS visitas_ultimo_mes,
    ROUND(AVG(CASE WHEN ag.fecha_analisis >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) THEN ag.hemoglobina_glicosilada END), 2) AS hba1c_promedio_trimestre
FROM pacientes p
LEFT JOIN visitas v ON p.id_paciente = v.id_paciente
LEFT JOIN analisis_glucosa ag ON v.id_visita = ag.id_visita
WHERE p.activo = TRUE;

-- ============================================================================
-- CONSULTAS DE INTERPRETACIÓN AUTOMÁTICA
-- ============================================================================

-- 24. Obtener interpretación de un valor de glucosa
SELECT 
    i.nivel_alerta,
    i.mensaje,
    i.recomendacion
FROM interpretaciones i
WHERE i.parametro = 'glucosa_ayunas'
  AND i.activo = TRUE
  AND (
      (i.condicion = 'menor_que' AND ? < i.valor_referencia)
      OR (i.condicion = 'mayor_igual' AND ? >= i.valor_referencia)
      OR (i.condicion = 'entre' AND ? >= i.valor_referencia AND ? <= i.valor_referencia_max)
  )
ORDER BY 
    CASE i.nivel_alerta
        WHEN 'Alerta' THEN 1
        WHEN 'Precaución' THEN 2
        WHEN 'Normal' THEN 3
    END
LIMIT 1;

-- 25. Obtener rangos de referencia para un parámetro
SELECT 
    parametro,
    unidad,
    valor_minimo_normal,
    valor_maximo_normal,
    valor_minimo_precaucion,
    valor_maximo_precaucion,
    descripcion
FROM rangos_referencia
WHERE parametro = ?
  AND activo = TRUE;

-- ============================================================================
-- CONSULTAS DE ANEXOS
-- ============================================================================

-- 26. Anexos de un paciente
SELECT 
    a.id_anexo,
    a.tipo_archivo,
    a.nombre_archivo,
    a.descripcion,
    a.fecha_subida,
    v.fecha_visita,
    CONCAT(u.nombre, ' ', u.apellido_paterno) AS subido_por
FROM anexos a
LEFT JOIN visitas v ON a.id_visita = v.id_visita
LEFT JOIN usuarios u ON a.subido_por = u.id_usuario
WHERE a.id_paciente = ?
ORDER BY a.fecha_subida DESC;

-- ============================================================================
-- FIN DE CONSULTAS DE EJEMPLO
-- ============================================================================
