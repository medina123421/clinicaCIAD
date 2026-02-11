<?php
/**
 * Modelo de Análisis Clínicos
 * Manejo de datos para Glucosa, Perfil Renal y Perfil Lipídico
 */

class Analisis
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Busca una visita existente en la fecha dada para el paciente, 
     * o crea una nueva visita 'Control' automáticamente.
     */
    public function obtenerOCrearVisita($id_paciente, $fecha_analisis, $id_usuario)
    {
        // 1. Buscar visita existente en esa fecha (ignorando hora)
        $query = "SELECT id_visita FROM visitas 
                  WHERE id_paciente = :id_paciente 
                  AND DATE(fecha_visita) = :fecha
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_paciente', $id_paciente);
        $stmt->bindParam(':fecha', $fecha_analisis);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['id_visita'];
        }

        // 2. Si no existe, crear nueva visita automática
        $query = "INSERT INTO visitas 
                  (id_paciente, id_doctor, fecha_visita, tipo_visita, motivo_consulta, estatus, created_by)
                  VALUES 
                  (:id_paciente, :id_doctor, :fecha_visita, 'Control', 'Registro automático por Análisis Clínico', 'Completada', :created_by)";

        $stmt = $this->conn->prepare($query);

        // Asumimos visita a las 8:00 AM si se crea automática
        $fecha_completa = $fecha_analisis . ' 08:00:00';

        $stmt->bindParam(':id_paciente', $id_paciente);
        $stmt->bindParam(':id_doctor', $id_usuario); // El usuario que registra figura como doctor
        $stmt->bindParam(':fecha_visita', $fecha_completa);
        $stmt->bindParam(':created_by', $id_usuario);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    // ==========================================
    // GLUCOSA
    // ==========================================
    public function registrarGlucosa($datos)
    {
        $query = "INSERT INTO analisis_glucosa
                  (id_visita, fecha_analisis, glucosa_ayunas, glucosa_postprandial_2h, 
                   hemoglobina_glicosilada, interpretacion_glucosa_ayunas, interpretacion_hba1c, observaciones, created_by)
                  VALUES
                  (:id_visita, :fecha_analisis, :glucosa_ayunas, :glucosa_postprandial_2h,
                   :hemoglobina_glicosilada, :interpretacion_glucosa_ayunas, :interpretacion_hba1c, :observaciones, :created_by)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id_visita', $datos['id_visita']);
        $stmt->bindParam(':fecha_analisis', $datos['fecha_analisis']);
        $stmt->bindParam(':glucosa_ayunas', $datos['glucosa_ayunas']);
        $stmt->bindParam(':glucosa_postprandial_2h', $datos['glucosa_postprandial_2h']);
        $stmt->bindParam(':hemoglobina_glicosilada', $datos['hemoglobina_glicosilada']);
        $stmt->bindParam(':interpretacion_glucosa_ayunas', $datos['interpretacion_glucosa_ayunas']);
        $stmt->bindParam(':interpretacion_hba1c', $datos['interpretacion_hba1c']);
        $stmt->bindParam(':observaciones', $datos['observaciones']);
        $stmt->bindParam(':created_by', $datos['created_by']);

        return $stmt->execute();
    }

    // ==========================================
    // PERFIL RENAL
    // ==========================================
    public function registrarPerfilRenal($datos)
    {
        $query = "INSERT INTO analisis_perfil_renal
                  (id_visita, fecha_analisis, creatinina_serica, tasa_filtracion_glomerular, 
                   urea, bun, microalbuminuria, relacion_albumina_creatinina,
                   interpretacion_tfg, interpretacion_microalbuminuria, observaciones, created_by)
                  VALUES
                  (:id_visita, :fecha_analisis, :creatinina_serica, :tfc, 
                   :urea, :bun, :microalbuminuria, :rac,
                   :interpretacion_tfg, :interpretacion_microalbuminuria, :observaciones, :created_by)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id_visita', $datos['id_visita']);
        $stmt->bindParam(':fecha_analisis', $datos['fecha_analisis']);
        $stmt->bindParam(':creatinina_serica', $datos['creatinina_serica']);
        $stmt->bindParam(':tfc', $datos['tasa_filtracion_glomerular']);
        $stmt->bindParam(':urea', $datos['urea']);
        $stmt->bindParam(':bun', $datos['bun']);
        $stmt->bindParam(':microalbuminuria', $datos['microalbuminuria']);
        $stmt->bindParam(':rac', $datos['relacion_albumina_creatinina']);
        $stmt->bindParam(':interpretacion_tfg', $datos['interpretacion_tfg']);
        $stmt->bindParam(':interpretacion_microalbuminuria', $datos['interpretacion_microalbuminuria']);
        $stmt->bindParam(':observaciones', $datos['observaciones']);
        $stmt->bindParam(':created_by', $datos['created_by']);

        return $stmt->execute();
    }

    // ==========================================
    // PERFIL LIPÍDICO
    // ==========================================
    public function registrarPerfilLipidico($datos)
    {
        $query = "INSERT INTO analisis_perfil_lipidico
                  (id_visita, fecha_analisis, colesterol_total, ldl, hdl, trigliceridos,
                   interpretacion_colesterol, interpretacion_ldl, interpretacion_hdl, interpretacion_trigliceridos,
                   observaciones, created_by)
                  VALUES
                  (:id_visita, :fecha_analisis, :colesterol_total, :ldl, :hdl, :trigliceridos,
                   :interpretacion_colesterol, :interpretacion_ldl, :interpretacion_hdl, :interpretacion_trigliceridos,
                   :observaciones, :created_by)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id_visita', $datos['id_visita']);
        $stmt->bindParam(':fecha_analisis', $datos['fecha_analisis']);
        $stmt->bindParam(':colesterol_total', $datos['colesterol_total']);
        $stmt->bindParam(':ldl', $datos['ldl']);
        $stmt->bindParam(':hdl', $datos['hdl']);
        $stmt->bindParam(':trigliceridos', $datos['trigliceridos']);
        $stmt->bindParam(':interpretacion_colesterol', $datos['interpretacion_colesterol']);
        $stmt->bindParam(':interpretacion_ldl', $datos['interpretacion_ldl']);
        $stmt->bindParam(':interpretacion_hdl', $datos['interpretacion_hdl']);
        $stmt->bindParam(':interpretacion_trigliceridos', $datos['interpretacion_trigliceridos']);
        $stmt->bindParam(':observaciones', $datos['observaciones']);
        $stmt->bindParam(':created_by', $datos['created_by']);

        return $stmt->execute();
    }

    // ==========================================
    // BIOMETRÍA HEMÁTICA
    // ==========================================
    public function registrarBiometriaHematica($datos)
    {
        $query = "INSERT INTO lab_biometria_hematica
                  (id_visita, fecha_analisis, eritrocitos, hemoglobina, hematocrito, vgm, hgm, cmhg, ide,
                   leucocitos, neutrofilos_perc, linfocitos_perc, mid_perc, neutrofilos_abs, linfocitos_abs, mid_abs,
                   plaquetas, observaciones, created_by)
                  VALUES
                  (:id_visita, :fecha_analisis, :eritrocitos, :hemoglobina, :hematocrito, :vgm, :hgm, :cmhg, :ide,
                   :leucocitos, :neutrofilos_perc, :linfocitos_perc, :mid_perc, :neutrofilos_abs, :linfocitos_abs, :mid_abs,
                   :plaquetas, :observaciones, :created_by)";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(':id_visita', $datos['id_visita']);
        $stmt->bindParam(':fecha_analisis', $datos['fecha_analisis']);

        $campos = [
            'eritrocitos',
            'hemoglobina',
            'hematocrito',
            'vgm',
            'hgm',
            'cmhg',
            'ide',
            'leucocitos',
            'neutrofilos_perc',
            'linfocitos_perc',
            'mid_perc',
            'neutrofilos_abs',
            'linfocitos_abs',
            'mid_abs',
            'plaquetas'
        ];

        foreach ($campos as $campo) {
            $stmt->bindValue(":$campo", $datos[$campo] ?? null);
        }

        $stmt->bindParam(':observaciones', $datos['observaciones']);
        $stmt->bindParam(':created_by', $datos['created_by']);

        return $stmt->execute();
    }

    // ==========================================
    // QUÍMICA SANGUÍNEA
    // ==========================================
    public function registrarQuimicaSanguinea($datos)
    {
        $query = "INSERT INTO lab_quimica_sanguinea
                  (id_visita, fecha_analisis, glucosa, urea, bun, creatinina, acido_urico, colesterol, trigliceridos, observaciones, created_by)
                  VALUES
                  (:id_visita, :fecha_analisis, :glucosa, :urea, :bun, :creatinina, :acido_urico, :colesterol, :trigliceridos, :observaciones, :created_by)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id_visita', $datos['id_visita']);
        $stmt->bindParam(':fecha_analisis', $datos['fecha_analisis']);

        $campos = ['glucosa', 'urea', 'bun', 'creatinina', 'acido_urico', 'colesterol', 'trigliceridos'];
        foreach ($campos as $campo) {
            $stmt->bindValue(":$campo", $datos[$campo] ?? null);
        }

        $stmt->bindParam(':observaciones', $datos['observaciones']);
        $stmt->bindParam(':created_by', $datos['created_by']);

        return $stmt->execute();
    }

    // ==========================================
    // EXAMEN GENERAL DE ORINA
    // ==========================================
    public function registrarExamenOrina($datos)
    {
        $query = "INSERT INTO lab_examen_orina
                  (id_visita, fecha_analisis, color, aspecto, densidad, ph, leucocitos_quimico, nitritos, proteinas, 
                   glucosa_quimico, sangre_quimico, cetonas, urobilinogeno, bilirrubina, 
                   celulas_escamosas, celulas_cilindricas, celulas_urotelio, cristales, celulas_renales, 
                   leucocitos_micro, cilindros, eritrocitos_micro, dismorficos, bacterias, hongos, levaduras, parasitos,
                   observaciones, created_by)
                  VALUES
                  (:id_visita, :fecha_analisis, :color, :aspecto, :densidad, :ph, :leucocitos_quimico, :nitritos, :proteinas, 
                   :glucosa_quimico, :sangre_quimico, :cetonas, :urobilinogeno, :bilirrubina, 
                   :celulas_escamosas, :celulas_cilindricas, :celulas_urotelio, :cristales, :celulas_renales, 
                   :leucocitos_micro, :cilindros, :eritrocitos_micro, :dismorficos, :bacterias, :hongos, :levaduras, :parasitos,
                   :observaciones, :created_by)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_visita', $datos['id_visita']);
        $stmt->bindParam(':fecha_analisis', $datos['fecha_analisis']);

        $campos = [
            'color',
            'aspecto',
            'densidad',
            'ph',
            'leucocitos_quimico',
            'nitritos',
            'proteinas',
            'glucosa_quimico',
            'sangre_quimico',
            'cetonas',
            'urobilinogeno',
            'bilirrubina',
            'celulas_escamosas',
            'celulas_cilindricas',
            'celulas_urotelio',
            'cristales',
            'celulas_renales',
            'leucocitos_micro',
            'cilindros',
            'eritrocitos_micro',
            'dismorficos',
            'bacterias',
            'hongos',
            'levaduras',
            'parasitos'
        ];

        foreach ($campos as $campo) {
            $stmt->bindValue(":$campo", $datos[$campo] ?? null);
        }

        $stmt->bindParam(':observaciones', $datos['observaciones']);
        $stmt->bindParam(':created_by', $datos['created_by']);

        return $stmt->execute();
    }

    // ==========================================
    // PERFIL HEPÁTICO
    // ==========================================
    public function registrarPerfilHepatico($datos)
    {
        $query = "INSERT INTO lab_perfil_hepatico
                  (id_visita, fecha_analisis, bilirrubina_total, bilirrubina_directa, bilirrubina_indirecta,
                   alt_gpt, ast_got, fosfatasa_alcalina, ggt, proteinas_totales, albumina, globulina,
                   observaciones, created_by)
                  VALUES
                  (:id_visita, :fecha_analisis, :bilirrubina_total, :bilirrubina_directa, :bilirrubina_indirecta,
                   :alt_gpt, :ast_got, :fosfatasa_alcalina, :ggt, :proteinas_totales, :albumina, :globulina,
                   :observaciones, :created_by)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_visita', $datos['id_visita']);
        $stmt->bindParam(':fecha_analisis', $datos['fecha_analisis']);

        $campos = ['bilirrubina_total', 'bilirrubina_directa', 'bilirrubina_indirecta', 'alt_gpt', 'ast_got', 'fosfatasa_alcalina', 'ggt', 'proteinas_totales', 'albumina', 'globulina'];
        foreach ($campos as $campo) {
            $stmt->bindValue(":$campo", $datos[$campo] ?? null);
        }

        $stmt->bindParam(':observaciones', $datos['observaciones']);
        $stmt->bindParam(':created_by', $datos['created_by']);

        return $stmt->execute();
    }

    // ==========================================
    // PERFIL TIROIDEO
    // ==========================================
    public function registrarPerfilTiroideo($datos)
    {
        $query = "INSERT INTO lab_perfil_tiroideo
                  (id_visita, fecha_analisis, t3_total, t3_libre, t4_total, t4_libre, tsh, observaciones, created_by)
                  VALUES
                  (:id_visita, :fecha_analisis, :t3_total, :t3_libre, :t4_total, :t4_libre, :tsh, :observaciones, :created_by)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_visita', $datos['id_visita']);
        $stmt->bindParam(':fecha_analisis', $datos['fecha_analisis']);

        $campos = ['t3_total', 't3_libre', 't4_total', 't4_libre', 'tsh'];
        foreach ($campos as $campo) {
            $stmt->bindValue(":$campo", $datos[$campo] ?? null);
        }

        $stmt->bindParam(':observaciones', $datos['observaciones']);
        $stmt->bindParam(':created_by', $datos['created_by']);

        return $stmt->execute();
    }

    // ==========================================
    // INSULINA
    // ==========================================
    public function registrarInsulina($datos)
    {
        $query = "INSERT INTO lab_insulina
                  (id_visita, fecha_analisis, insulina_basal, observaciones, created_by)
                  VALUES
                  (:id_visita, :fecha_analisis, :insulina_basal, :observaciones, :created_by)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_visita', $datos['id_visita']);
        $stmt->bindParam(':fecha_analisis', $datos['fecha_analisis']);
        $stmt->bindValue(':insulina_basal', $datos['insulina_basal'] ?? null);
        $stmt->bindParam(':observaciones', $datos['observaciones']);
        $stmt->bindParam(':created_by', $datos['created_by']);

        return $stmt->execute();
    }

    // ==========================================
    // REPORTE GENERAL
    // ==========================================
    public function obtenerReporteGeneral($fecha_inicio = null, $fecha_fin = null)
    {
        $condicion_fecha = "";
        if ($fecha_inicio && $fecha_fin) {
            $condicion_fecha = " AND DATE(v.fecha_visita) BETWEEN '$fecha_inicio' AND '$fecha_fin' ";
        }

        $query = "SELECT v.id_visita, v.fecha_visita, CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) as nombre_completo, p.numero_expediente,
                         lbh.id_biometria, lqs.id_quimica, leo.id_orina, lph.id_hepatico, lpt.id_tiroideo, li.id_insulina
                  FROM visitas v
                  JOIN pacientes p ON v.id_paciente = p.id_paciente
                  LEFT JOIN lab_biometria_hematica lbh ON v.id_visita = lbh.id_visita
                  LEFT JOIN lab_quimica_sanguinea lqs ON v.id_visita = lqs.id_visita
                  LEFT JOIN lab_examen_orina leo ON v.id_visita = leo.id_visita
                  LEFT JOIN lab_perfil_hepatico lph ON v.id_visita = lph.id_visita
                  LEFT JOIN lab_perfil_tiroideo lpt ON v.id_visita = lpt.id_visita
                  LEFT JOIN lab_insulina li ON v.id_visita = li.id_visita
                  WHERE (lbh.id_biometria IS NOT NULL 
                     OR lqs.id_quimica IS NOT NULL 
                     OR leo.id_orina IS NOT NULL
                     OR lph.id_hepatico IS NOT NULL
                     OR lpt.id_tiroideo IS NOT NULL
                     OR li.id_insulina IS NOT NULL)
                  $condicion_fecha
                  ORDER BY v.fecha_visita DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // ==========================================
    // OBTENER RESULTADOS COMPLETOS POR VISITA
    // ==========================================
    public function obtenerResultadosPorVisita($id_visita)
    {
        $query = "SELECT v.fecha_visita, 
                          p.nombre, p.apellido_paterno, p.apellido_materno, p.numero_expediente, p.fecha_nacimiento, p.sexo,
                         lbh.*, lqs.*, leo.*, lph.*, lpt.*, li.*
                  FROM visitas v
                  JOIN pacientes p ON v.id_paciente = p.id_paciente
                  LEFT JOIN lab_biometria_hematica lbh ON v.id_visita = lbh.id_visita
                  LEFT JOIN lab_quimica_sanguinea lqs ON v.id_visita = lqs.id_visita
                  LEFT JOIN lab_examen_orina leo ON v.id_visita = leo.id_visita
                  LEFT JOIN lab_perfil_hepatico lph ON v.id_visita = lph.id_visita
                  LEFT JOIN lab_perfil_tiroideo lpt ON v.id_visita = lpt.id_visita
                  LEFT JOIN lab_insulina li ON v.id_visita = li.id_visita
                  WHERE v.id_visita = :id_visita
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_visita', $id_visita);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>