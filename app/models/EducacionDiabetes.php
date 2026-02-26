<?php
require_once __DIR__ . '/../config/database.php';

class EducacionDiabetes {
    private $conn;
    private $table = 'educacion_diabetes';

    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    public function obtenerPorVisita($id_visita) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_visita = :id_visita ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_visita', $id_visita);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar($datos) {
        try {
            // Verificar si ya existe un registro para esta visita
            $existe = $this->obtenerPorVisita($datos['id_visita']);
            
            if ($existe) {
                return $this->actualizar($datos);
            } else {
                return $this->crear($datos);
            }
        } catch (Exception $e) {
            error_log("Error en EducacionDiabetes::guardar: " . $e->getMessage());
            return false;
        }
    }

    private function crear($datos) {
        // Calcular campos automáticos
        $datos = $this->calcularCamposAutomaticos($datos);

        $query = "INSERT INTO " . $this->table . " (
            id_visita, conocimientos_deficientes_nutricion, no_cumple_recomendaciones, 
            ingesta_excesiva_carbohidratos, manejo_inadecuado_hipoglucemia,
            barrera_nivel_educativo, barrera_economica, barrera_apoyo_familiar, 
            barrera_psicologica, otras_barreras,
            tecnica_seleccion_jeringa, tecnica_angulacion_pliegue, tecnica_almacenamiento_insulina,
            rotacion_sitios_abdomen, rotacion_sitios_muslos, rotacion_sitios_brazos, 
            deteccion_lipodistrofias,
            uso_glucometro, uso_lancetero, registro_bitacora, frecuencia_medicion_adecuada, 
            interpretacion_resultados,
            conoce_mecanismo_accion, identifica_efectos_secundarios, olvido_dosis_frecuencia, 
            adherencia_oral_metformina,
            identificacion_sintomas_hipo, aplicacion_regla_15, identificacion_sintomas_hiper, 
            cuando_medir_cetonas, sabe_manejar_dias_enfermedad, plan_accion_crisis,
            conteo_carbohidratos_nivel, lectura_etiquetas, calculo_porciones,
            conoce_uso_suplementos, suplemento_vit_d, suplemento_omega_3, suplemento_creatina, 
            suplemento_proteina_suero,
            evita_refrescos, evita_pan_dulce, evita_jugos, evita_mermeladas, evita_ultraprocesados,
            meta_hba1c_objetivo, meta_glucosa_ayunas_max, meta_reduccion_peso, 
            meta_ejercicio_regular, meta_adherencia_alimentacion,
            metas_cumplidas_anteriores, nuevas_metas_establecidas,
            peso_actual, talla_actual, imc_actual, circunferencia_cintura, 
            porcentaje_grasa, masa_muscular_kg,
            recordatorio_24h_resumen, freq_agua_litros, freq_frutas_verduras,
            semaforo_educativo, nivel_autonomia,
            estado_cambio,
            bdi2_item_01, bdi2_item_02, bdi2_item_03, bdi2_item_04, bdi2_item_05, bdi2_item_06, bdi2_item_07,
            bdi2_item_08, bdi2_item_09, bdi2_item_10, bdi2_item_11, bdi2_item_12, bdi2_item_13, bdi2_item_14,
            bdi2_item_15, bdi2_item_16, bdi2_item_17, bdi2_item_18, bdi2_item_19, bdi2_item_20, bdi2_item_21,
            bdi2_puntuacion_total, bdi2_clasificacion,
            observaciones_educador, material_educativo_entregado, created_by
        ) VALUES (
            :id_visita, :conocimientos_deficientes_nutricion, :no_cumple_recomendaciones,
            :ingesta_excesiva_carbohidratos, :manejo_inadecuado_hipoglucemia,
            :barrera_nivel_educativo, :barrera_economica, :barrera_apoyo_familiar,
            :barrera_psicologica, :otras_barreras,
            :tecnica_seleccion_jeringa, :tecnica_angulacion_pliegue, :tecnica_almacenamiento_insulina,
            :rotacion_sitios_abdomen, :rotacion_sitios_muslos, :rotacion_sitios_brazos,
            :deteccion_lipodistrofias,
            :uso_glucometro, :uso_lancetero, :registro_bitacora, :frecuencia_medicion_adecuada,
            :interpretacion_resultados,
            :conoce_mecanismo_accion, :identifica_efectos_secundarios, :olvido_dosis_frecuencia,
            :adherencia_oral_metformina,
            :identificacion_sintomas_hipo, :aplicacion_regla_15, :identificacion_sintomas_hiper,
            :cuando_medir_cetonas, :sabe_manejar_dias_enfermedad, :plan_accion_crisis,
            :conteo_carbohidratos_nivel, :lectura_etiquetas, :calculo_porciones,
            :conoce_uso_suplementos, :suplemento_vit_d, :suplemento_omega_3, :suplemento_creatina,
            :suplemento_proteina_suero,
            :evita_refrescos, :evita_pan_dulce, :evita_jugos, :evita_mermeladas, :evita_ultraprocesados,
            :meta_hba1c_objetivo, :meta_glucosa_ayunas_max, :meta_reduccion_peso,
            :meta_ejercicio_regular, :meta_adherencia_alimentacion,
            :metas_cumplidas_anteriores, :nuevas_metas_establecidas,
            :peso_actual, :talla_actual, :imc_actual, :circunferencia_cintura,
            :porcentaje_grasa, :masa_muscular_kg,
            :recordatorio_24h_resumen, :freq_agua_litros, :freq_frutas_verduras,
            :semaforo_educativo, :nivel_autonomia,
            :estado_cambio,
            :bdi2_item_01, :bdi2_item_02, :bdi2_item_03, :bdi2_item_04, :bdi2_item_05, :bdi2_item_06, :bdi2_item_07,
            :bdi2_item_08, :bdi2_item_09, :bdi2_item_10, :bdi2_item_11, :bdi2_item_12, :bdi2_item_13, :bdi2_item_14,
            :bdi2_item_15, :bdi2_item_16, :bdi2_item_17, :bdi2_item_18, :bdi2_item_19, :bdi2_item_20, :bdi2_item_21,
            :bdi2_puntuacion_total, :bdi2_clasificacion,
            :observaciones_educador, :material_educativo_entregado, :created_by
        )";

        $stmt = $this->conn->prepare($query);
        return $this->bindParametros($stmt, $datos) && $stmt->execute();
    }

    private function actualizar($datos) {
        // Calcular campos automáticos
        $datos = $this->calcularCamposAutomaticos($datos);

        $query = "UPDATE " . $this->table . " SET
            conocimientos_deficientes_nutricion = :conocimientos_deficientes_nutricion,
            no_cumple_recomendaciones = :no_cumple_recomendaciones,
            ingesta_excesiva_carbohidratos = :ingesta_excesiva_carbohidratos,
            manejo_inadecuado_hipoglucemia = :manejo_inadecuado_hipoglucemia,
            barrera_nivel_educativo = :barrera_nivel_educativo,
            barrera_economica = :barrera_economica,
            barrera_apoyo_familiar = :barrera_apoyo_familiar,
            barrera_psicologica = :barrera_psicologica,
            otras_barreras = :otras_barreras,
            tecnica_seleccion_jeringa = :tecnica_seleccion_jeringa,
            tecnica_angulacion_pliegue = :tecnica_angulacion_pliegue,
            tecnica_almacenamiento_insulina = :tecnica_almacenamiento_insulina,
            rotacion_sitios_abdomen = :rotacion_sitios_abdomen,
            rotacion_sitios_muslos = :rotacion_sitios_muslos,
            rotacion_sitios_brazos = :rotacion_sitios_brazos,
            deteccion_lipodistrofias = :deteccion_lipodistrofias,
            uso_glucometro = :uso_glucometro,
            uso_lancetero = :uso_lancetero,
            registro_bitacora = :registro_bitacora,
            frecuencia_medicion_adecuada = :frecuencia_medicion_adecuada,
            interpretacion_resultados = :interpretacion_resultados,
            conoce_mecanismo_accion = :conoce_mecanismo_accion,
            identifica_efectos_secundarios = :identifica_efectos_secundarios,
            olvido_dosis_frecuencia = :olvido_dosis_frecuencia,
            adherencia_oral_metformina = :adherencia_oral_metformina,
            identificacion_sintomas_hipo = :identificacion_sintomas_hipo,
            aplicacion_regla_15 = :aplicacion_regla_15,
            identificacion_sintomas_hiper = :identificacion_sintomas_hiper,
            cuando_medir_cetonas = :cuando_medir_cetonas,
            sabe_manejar_dias_enfermedad = :sabe_manejar_dias_enfermedad,
            plan_accion_crisis = :plan_accion_crisis,
            conteo_carbohidratos_nivel = :conteo_carbohidratos_nivel,
            lectura_etiquetas = :lectura_etiquetas,
            calculo_porciones = :calculo_porciones,
            conoce_uso_suplementos = :conoce_uso_suplementos,
            suplemento_vit_d = :suplemento_vit_d,
            suplemento_omega_3 = :suplemento_omega_3,
            suplemento_creatina = :suplemento_creatina,
            suplemento_proteina_suero = :suplemento_proteina_suero,
            evita_refrescos = :evita_refrescos,
            evita_pan_dulce = :evita_pan_dulce,
            evita_jugos = :evita_jugos,
            evita_mermeladas = :evita_mermeladas,
            evita_ultraprocesados = :evita_ultraprocesados,
            meta_hba1c_objetivo = :meta_hba1c_objetivo,
            meta_glucosa_ayunas_max = :meta_glucosa_ayunas_max,
            meta_reduccion_peso = :meta_reduccion_peso,
            meta_ejercicio_regular = :meta_ejercicio_regular,
            meta_adherencia_alimentacion = :meta_adherencia_alimentacion,
            metas_cumplidas_anteriores = :metas_cumplidas_anteriores,
            nuevas_metas_establecidas = :nuevas_metas_establecidas,
            peso_actual = :peso_actual,
            talla_actual = :talla_actual,
            imc_actual = :imc_actual,
            circunferencia_cintura = :circunferencia_cintura,
            porcentaje_grasa = :porcentaje_grasa,
            masa_muscular_kg = :masa_muscular_kg,
            recordatorio_24h_resumen = :recordatorio_24h_resumen,
            freq_agua_litros = :freq_agua_litros,
            freq_frutas_verduras = :freq_frutas_verduras,
            semaforo_educativo = :semaforo_educativo,
            nivel_autonomia = :nivel_autonomia,
            estado_cambio = :estado_cambio,
            bdi2_item_01 = :bdi2_item_01,
            bdi2_item_02 = :bdi2_item_02,
            bdi2_item_03 = :bdi2_item_03,
            bdi2_item_04 = :bdi2_item_04,
            bdi2_item_05 = :bdi2_item_05,
            bdi2_item_06 = :bdi2_item_06,
            bdi2_item_07 = :bdi2_item_07,
            bdi2_item_08 = :bdi2_item_08,
            bdi2_item_09 = :bdi2_item_09,
            bdi2_item_10 = :bdi2_item_10,
            bdi2_item_11 = :bdi2_item_11,
            bdi2_item_12 = :bdi2_item_12,
            bdi2_item_13 = :bdi2_item_13,
            bdi2_item_14 = :bdi2_item_14,
            bdi2_item_15 = :bdi2_item_15,
            bdi2_item_16 = :bdi2_item_16,
            bdi2_item_17 = :bdi2_item_17,
            bdi2_item_18 = :bdi2_item_18,
            bdi2_item_19 = :bdi2_item_19,
            bdi2_item_20 = :bdi2_item_20,
            bdi2_item_21 = :bdi2_item_21,
            bdi2_puntuacion_total = :bdi2_puntuacion_total,
            bdi2_clasificacion = :bdi2_clasificacion,
            observaciones_educador = :observaciones_educador,
            material_educativo_entregado = :material_educativo_entregado,
            updated_at = CURRENT_TIMESTAMP
            WHERE id_visita = :id_visita";

        $stmt = $this->conn->prepare($query);
        return $this->bindParametros($stmt, $datos) && $stmt->execute();
    }

    private function bindParametros($stmt, $datos) {
        // Asegurar que todos los campos tengan valores por defecto
        $datos = $this->aplicarValoresPorDefecto($datos);
        
        $stmt->bindParam(':id_visita', $datos['id_visita']);
        
        // 1) Diagnóstico Educativo
        $stmt->bindParam(':conocimientos_deficientes_nutricion', $datos['conocimientos_deficientes_nutricion']);
        $stmt->bindParam(':no_cumple_recomendaciones', $datos['no_cumple_recomendaciones']);
        $stmt->bindParam(':ingesta_excesiva_carbohidratos', $datos['ingesta_excesiva_carbohidratos']);
        $stmt->bindParam(':manejo_inadecuado_hipoglucemia', $datos['manejo_inadecuado_hipoglucemia']);
        
        // Barreras
        $stmt->bindParam(':barrera_nivel_educativo', $datos['barrera_nivel_educativo']);
        $stmt->bindParam(':barrera_economica', $datos['barrera_economica']);
        $stmt->bindParam(':barrera_apoyo_familiar', $datos['barrera_apoyo_familiar']);
        $stmt->bindParam(':barrera_psicologica', $datos['barrera_psicologica']);
        $stmt->bindParam(':otras_barreras', $datos['otras_barreras']);
        
        // 2) Habilidades Técnicas - Insulina
        $stmt->bindParam(':tecnica_seleccion_jeringa', $datos['tecnica_seleccion_jeringa']);
        $stmt->bindParam(':tecnica_angulacion_pliegue', $datos['tecnica_angulacion_pliegue']);
        $stmt->bindParam(':tecnica_almacenamiento_insulina', $datos['tecnica_almacenamiento_insulina']);
        $stmt->bindParam(':rotacion_sitios_abdomen', $datos['rotacion_sitios_abdomen']);
        $stmt->bindParam(':rotacion_sitios_muslos', $datos['rotacion_sitios_muslos']);
        $stmt->bindParam(':rotacion_sitios_brazos', $datos['rotacion_sitios_brazos']);
        $stmt->bindParam(':deteccion_lipodistrofias', $datos['deteccion_lipodistrofias']);
        
        // Habilidades Técnicas - Monitoreo
        $stmt->bindParam(':uso_glucometro', $datos['uso_glucometro']);
        $stmt->bindParam(':uso_lancetero', $datos['uso_lancetero']);
        $stmt->bindParam(':registro_bitacora', $datos['registro_bitacora']);
        $stmt->bindParam(':frecuencia_medicion_adecuada', $datos['frecuencia_medicion_adecuada']);
        $stmt->bindParam(':interpretacion_resultados', $datos['interpretacion_resultados']);
        
        // 3) Medicación Oral
        $stmt->bindParam(':conoce_mecanismo_accion', $datos['conoce_mecanismo_accion']);
        $stmt->bindParam(':identifica_efectos_secundarios', $datos['identifica_efectos_secundarios']);
        $stmt->bindParam(':olvido_dosis_frecuencia', $datos['olvido_dosis_frecuencia']);
        $stmt->bindParam(':adherencia_oral_metformina', $datos['adherencia_oral_metformina']);
        
        // 4) Resolución de Problemas
        $stmt->bindParam(':identificacion_sintomas_hipo', $datos['identificacion_sintomas_hipo']);
        $stmt->bindParam(':aplicacion_regla_15', $datos['aplicacion_regla_15']);
        $stmt->bindParam(':identificacion_sintomas_hiper', $datos['identificacion_sintomas_hiper']);
        $stmt->bindParam(':cuando_medir_cetonas', $datos['cuando_medir_cetonas']);
        $stmt->bindParam(':sabe_manejar_dias_enfermedad', $datos['sabe_manejar_dias_enfermedad']);
        $stmt->bindParam(':plan_accion_crisis', $datos['plan_accion_crisis']);
        
        // 5) Educación Nutricional
        $stmt->bindParam(':conteo_carbohidratos_nivel', $datos['conteo_carbohidratos_nivel']);
        $stmt->bindParam(':lectura_etiquetas', $datos['lectura_etiquetas']);
        $stmt->bindParam(':calculo_porciones', $datos['calculo_porciones']);
        $stmt->bindParam(':conoce_uso_suplementos', $datos['conoce_uso_suplementos']);
        $stmt->bindParam(':suplemento_vit_d', $datos['suplemento_vit_d']);
        $stmt->bindParam(':suplemento_omega_3', $datos['suplemento_omega_3']);
        $stmt->bindParam(':suplemento_creatina', $datos['suplemento_creatina']);
        $stmt->bindParam(':suplemento_proteina_suero', $datos['suplemento_proteina_suero']);
        
        // Alimentos a evitar
        $stmt->bindParam(':evita_refrescos', $datos['evita_refrescos']);
        $stmt->bindParam(':evita_pan_dulce', $datos['evita_pan_dulce']);
        $stmt->bindParam(':evita_jugos', $datos['evita_jugos']);
        $stmt->bindParam(':evita_mermeladas', $datos['evita_mermeladas']);
        $stmt->bindParam(':evita_ultraprocesados', $datos['evita_ultraprocesados']);
        
        // 6) Metas
        $stmt->bindParam(':meta_hba1c_objetivo', $datos['meta_hba1c_objetivo']);
        $stmt->bindParam(':meta_glucosa_ayunas_max', $datos['meta_glucosa_ayunas_max']);
        $stmt->bindParam(':meta_reduccion_peso', $datos['meta_reduccion_peso']);
        $stmt->bindParam(':meta_ejercicio_regular', $datos['meta_ejercicio_regular']);
        $stmt->bindParam(':meta_adherencia_alimentacion', $datos['meta_adherencia_alimentacion']);
        $stmt->bindParam(':metas_cumplidas_anteriores', $datos['metas_cumplidas_anteriores']);
        $stmt->bindParam(':nuevas_metas_establecidas', $datos['nuevas_metas_establecidas']);
        
        // 7) Antropometría
        $stmt->bindParam(':peso_actual', $datos['peso_actual']);
        $stmt->bindParam(':talla_actual', $datos['talla_actual']);
        $stmt->bindParam(':imc_actual', $datos['imc_actual']);
        $stmt->bindParam(':circunferencia_cintura', $datos['circunferencia_cintura']);
        $stmt->bindParam(':porcentaje_grasa', $datos['porcentaje_grasa']);
        $stmt->bindParam(':masa_muscular_kg', $datos['masa_muscular_kg']);
        
        // Recordatorio
        $stmt->bindParam(':recordatorio_24h_resumen', $datos['recordatorio_24h_resumen']);
        $stmt->bindParam(':freq_agua_litros', $datos['freq_agua_litros']);
        $stmt->bindParam(':freq_frutas_verduras', $datos['freq_frutas_verduras']);
        
        // 8) Semáforo y autonomía (calculados automáticamente)
        $stmt->bindParam(':semaforo_educativo', $datos['semaforo_educativo']);
        $stmt->bindParam(':nivel_autonomia', $datos['nivel_autonomia']);

        // Estado de cambio (transteórico)
        $stmt->bindParam(':estado_cambio', $datos['estado_cambio']);

        // BDI-2
        $stmt->bindParam(':bdi2_item_01', $datos['bdi2_item_01']);
        $stmt->bindParam(':bdi2_item_02', $datos['bdi2_item_02']);
        $stmt->bindParam(':bdi2_item_03', $datos['bdi2_item_03']);
        $stmt->bindParam(':bdi2_item_04', $datos['bdi2_item_04']);
        $stmt->bindParam(':bdi2_item_05', $datos['bdi2_item_05']);
        $stmt->bindParam(':bdi2_item_06', $datos['bdi2_item_06']);
        $stmt->bindParam(':bdi2_item_07', $datos['bdi2_item_07']);
        $stmt->bindParam(':bdi2_item_08', $datos['bdi2_item_08']);
        $stmt->bindParam(':bdi2_item_09', $datos['bdi2_item_09']);
        $stmt->bindParam(':bdi2_item_10', $datos['bdi2_item_10']);
        $stmt->bindParam(':bdi2_item_11', $datos['bdi2_item_11']);
        $stmt->bindParam(':bdi2_item_12', $datos['bdi2_item_12']);
        $stmt->bindParam(':bdi2_item_13', $datos['bdi2_item_13']);
        $stmt->bindParam(':bdi2_item_14', $datos['bdi2_item_14']);
        $stmt->bindParam(':bdi2_item_15', $datos['bdi2_item_15']);
        $stmt->bindParam(':bdi2_item_16', $datos['bdi2_item_16']);
        $stmt->bindParam(':bdi2_item_17', $datos['bdi2_item_17']);
        $stmt->bindParam(':bdi2_item_18', $datos['bdi2_item_18']);
        $stmt->bindParam(':bdi2_item_19', $datos['bdi2_item_19']);
        $stmt->bindParam(':bdi2_item_20', $datos['bdi2_item_20']);
        $stmt->bindParam(':bdi2_item_21', $datos['bdi2_item_21']);
        $stmt->bindParam(':bdi2_puntuacion_total', $datos['bdi2_puntuacion_total']);
        $stmt->bindParam(':bdi2_clasificacion', $datos['bdi2_clasificacion']);
        
        // Metadatos
        $stmt->bindParam(':observaciones_educador', $datos['observaciones_educador']);
        $stmt->bindParam(':material_educativo_entregado', $datos['material_educativo_entregado']);
        $stmt->bindParam(':created_by', $datos['created_by']);
        
        return true;
    }

    private function calcularCamposAutomaticos($datos) {
        // Calcular IMC si se proporcionan peso y talla
        if (!empty($datos['peso_actual']) && !empty($datos['talla_actual'])) {
            $peso = floatval($datos['peso_actual']);
            $talla = floatval($datos['talla_actual']) / 100; // Convertir cm a metros
            $datos['imc_actual'] = round($peso / ($talla * $talla), 2);
        }

        // Calcular Semáforo Educativo y Nivel de Autonomía
        $datos = $this->calcularSemaforoEducativo($datos);

        // Calcular BDI-2 (total y clasificación) y normalizar estado de cambio
        $datos = $this->calcularBdi2($datos);
        $datos = $this->normalizarEstadoCambio($datos);

        return $datos;
    }

    private function normalizarEstadoCambio($datos)
    {
        $permitidos = ['Precontemplación', 'Contemplación', 'Preparación', 'Acción', 'Mantenimiento', 'Recaída'];
        $val = $datos['estado_cambio'] ?? null;
        if ($val === '') $val = null;
        if ($val !== null && !in_array($val, $permitidos, true)) {
            $val = null;
        }
        $datos['estado_cambio'] = $val;
        return $datos;
    }

    private function calcularBdi2($datos)
    {
        $items = [];
        for ($i = 1; $i <= 21; $i++) {
            $k = 'bdi2_item_' . str_pad((string)$i, 2, '0', STR_PAD_LEFT);
            if (!array_key_exists($k, $datos) || $datos[$k] === '' || $datos[$k] === null) {
                $items[$k] = null;
                continue;
            }
            $v = (int)$datos[$k];
            if ($v < 0) $v = 0;
            if ($v > 3) $v = 3;
            $items[$k] = $v;
        }

        $completo = true;
        foreach ($items as $v) {
            if ($v === null) {
                $completo = false;
                break;
            }
        }

        foreach ($items as $k => $v) {
            $datos[$k] = $v;
        }

        if (!$completo) {
            $datos['bdi2_puntuacion_total'] = null;
            $datos['bdi2_clasificacion'] = null;
            return $datos;
        }

        $total = array_sum($items);
        if ($total <= 13) $clas = 'Mínima';
        elseif ($total <= 19) $clas = 'Leve';
        elseif ($total <= 28) $clas = 'Moderada';
        else $clas = 'Severa';

        $datos['bdi2_puntuacion_total'] = $total;
        $datos['bdi2_clasificacion'] = $clas;
        return $datos;
    }

    private function calcularSemaforoEducativo($datos) {
        $puntos_rojos = 0;
        $puntos_verdes = 0;

        // Factores críticos (ROJO)
        if (!empty($datos['conocimientos_deficientes_nutricion'])) $puntos_rojos++;
        if (!empty($datos['manejo_inadecuado_hipoglucemia'])) $puntos_rojos++;
        if ($datos['conteo_carbohidratos_nivel'] == 'Nulo') $puntos_rojos++;
        if ($datos['identificacion_sintomas_hipo'] == 'No') $puntos_rojos++;
        if ($datos['aplicacion_regla_15'] == 'No') $puntos_rojos++;

        // Factores positivos (VERDE)
        if ($datos['conteo_carbohidratos_nivel'] == 'Avanzado') $puntos_verdes++;
        if ($datos['uso_glucometro'] == 'Sí') $puntos_verdes++;
        if ($datos['registro_bitacora'] == 'Sí') $puntos_verdes++;
        if ($datos['interpretacion_resultados'] == 'Sí') $puntos_verdes++;
        if (!empty($datos['evita_refrescos']) && !empty($datos['evita_jugos'])) $puntos_verdes++;

        // Determinar semáforo
        if ($puntos_rojos >= 3) {
            $datos['semaforo_educativo'] = 'Rojo';
            $datos['nivel_autonomia'] = 'Dependiente';
        } elseif ($puntos_verdes >= 3 && $puntos_rojos <= 1) {
            $datos['semaforo_educativo'] = 'Verde';
            $datos['nivel_autonomia'] = 'Autónomo';
        } else {
            $datos['semaforo_educativo'] = 'Amarillo';
            $datos['nivel_autonomia'] = 'Semi-autónomo';
        }

        return $datos;
    }

    public function obtenerEstadisticas() {
        $query = "SELECT 
            semaforo_educativo,
            COUNT(*) as total,
            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM " . $this->table . "), 2) as porcentaje
            FROM " . $this->table . " 
            GROUP BY semaforo_educativo";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function aplicarValoresPorDefecto($datos) {
        $defaults = [
            // 1) Diagnóstico Educativo
            'conocimientos_deficientes_nutricion' => 0,
            'no_cumple_recomendaciones' => 0,
            'ingesta_excesiva_carbohidratos' => 0,
            'manejo_inadecuado_hipoglucemia' => 0,
            
            // Barreras
            'barrera_nivel_educativo' => 0,
            'barrera_economica' => 0,
            'barrera_apoyo_familiar' => 0,
            'barrera_psicologica' => 0,
            'otras_barreras' => '',
            
            // 2) Habilidades Técnicas - Insulina
            'tecnica_seleccion_jeringa' => 'No',
            'tecnica_angulacion_pliegue' => 'No',
            'tecnica_almacenamiento_insulina' => 'No',
            'rotacion_sitios_abdomen' => 'No',
            'rotacion_sitios_muslos' => 'No',
            'rotacion_sitios_brazos' => 'No',
            'deteccion_lipodistrofias' => 'No',
            
            // Habilidades Técnicas - Monitoreo
            'uso_glucometro' => 'No',
            'uso_lancetero' => 'No',
            'registro_bitacora' => 'No',
            'frecuencia_medicion_adecuada' => 'No',
            'interpretacion_resultados' => 'No',
            
            // 3) Medicación Oral
            'conoce_mecanismo_accion' => 0,
            'identifica_efectos_secundarios' => 0,
            'olvido_dosis_frecuencia' => 'Nunca',
            'adherencia_oral_metformina' => 0,
            
            // 4) Resolución de Problemas
            'identificacion_sintomas_hipo' => 'No',
            'aplicacion_regla_15' => 'No',
            'identificacion_sintomas_hiper' => 'No',
            'cuando_medir_cetonas' => 'No',
            'sabe_manejar_dias_enfermedad' => 0,
            'plan_accion_crisis' => 0,
            
            // 5) Educación Nutricional
            'conteo_carbohidratos_nivel' => 'Nulo',
            'lectura_etiquetas' => 'No',
            'calculo_porciones' => 'No',
            'conoce_uso_suplementos' => 0,
            'suplemento_vit_d' => 0,
            'suplemento_omega_3' => 0,
            'suplemento_creatina' => 0,
            'suplemento_proteina_suero' => 0,
            
            // Alimentos a evitar
            'evita_refrescos' => 0,
            'evita_pan_dulce' => 0,
            'evita_jugos' => 0,
            'evita_mermeladas' => 0,
            'evita_ultraprocesados' => 0,
            
            // 6) Metas
            'meta_hba1c_objetivo' => 7.0,
            'meta_glucosa_ayunas_max' => 130,
            'meta_reduccion_peso' => 0,
            'meta_ejercicio_regular' => 0,
            'meta_adherencia_alimentacion' => 0,
            'metas_cumplidas_anteriores' => '',
            'nuevas_metas_establecidas' => '',
            
            // 7) Antropometría
            'peso_actual' => null,
            'talla_actual' => null,
            'imc_actual' => null,
            'circunferencia_cintura' => null,
            'porcentaje_grasa' => null,
            'masa_muscular_kg' => null,
            
            // Recordatorio
            'recordatorio_24h_resumen' => '',
            'freq_agua_litros' => '< 1 litro',
            'freq_frutas_verduras' => '0-2 porciones',

            // Estado de cambio (transteórico)
            'estado_cambio' => null,

            // BDI-2
            'bdi2_item_01' => null,
            'bdi2_item_02' => null,
            'bdi2_item_03' => null,
            'bdi2_item_04' => null,
            'bdi2_item_05' => null,
            'bdi2_item_06' => null,
            'bdi2_item_07' => null,
            'bdi2_item_08' => null,
            'bdi2_item_09' => null,
            'bdi2_item_10' => null,
            'bdi2_item_11' => null,
            'bdi2_item_12' => null,
            'bdi2_item_13' => null,
            'bdi2_item_14' => null,
            'bdi2_item_15' => null,
            'bdi2_item_16' => null,
            'bdi2_item_17' => null,
            'bdi2_item_18' => null,
            'bdi2_item_19' => null,
            'bdi2_item_20' => null,
            'bdi2_item_21' => null,
            'bdi2_puntuacion_total' => null,
            'bdi2_clasificacion' => null,
            
            // Metadatos
            'observaciones_educador' => '',
            'material_educativo_entregado' => '',
            'created_by' => 1
        ];
        
        // Combinar datos recibidos con valores por defecto
        return array_merge($defaults, $datos);
    }
}
?>