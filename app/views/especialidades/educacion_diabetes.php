<?php
/**
 * Vista de Educación en Diabetes
 * Formulario completo: Diagnóstico educativo, Habilidades técnicas, Resolución de problemas, Educación nutricional, Metas y seguimiento
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/EducacionDiabetes.php';
require_once __DIR__ . '/../../models/Visita.php';
require_once __DIR__ . '/../../models/Paciente.php';

$id_visita = $_GET['id_visita'] ?? null;
$message = '';
$message_type = '';

$database = new Database();
$db = $database->getConnection();

$educacionDiabetes = new EducacionDiabetes($db);
$visitaModel = new Visita($db);
$pacienteModel = new Paciente($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_consulta'])) {
    try {
        // Preparar datos del formulario
        $datos_formulario = [
            'id_visita' => $_POST['id_visita'] ?? null,
            
            // 1) Diagnóstico Educativo
            'conocimientos_deficientes_nutricion' => isset($_POST['conocimientos_deficientes_nutricion']) ? 1 : 0,
            'no_cumple_recomendaciones' => isset($_POST['no_cumple_recomendaciones']) ? 1 : 0,
            'ingesta_excesiva_carbohidratos' => isset($_POST['ingesta_excesiva_carbohidratos']) ? 1 : 0,
            'manejo_inadecuado_hipoglucemia' => isset($_POST['manejo_inadecuado_hipoglucemia']) ? 1 : 0,
            
            // Barreras
            'barrera_nivel_educativo' => isset($_POST['barrera_nivel_educativo']) ? 1 : 0,
            'barrera_economica' => isset($_POST['barrera_economica']) ? 1 : 0,
            'barrera_apoyo_familiar' => isset($_POST['barrera_apoyo_familiar']) ? 1 : 0,
            'barrera_psicologica' => isset($_POST['barrera_psicologica']) ? 1 : 0,
            'otras_barreras' => $_POST['otras_barreras'] ?? '',
            
            // 2) Habilidades Técnicas - Insulina
            'tecnica_seleccion_jeringa' => $_POST['tecnica_seleccion_jeringa'] ?? 'No',
            'tecnica_angulacion_pliegue' => $_POST['tecnica_angulacion_pliegue'] ?? 'No',
            'tecnica_almacenamiento_insulina' => $_POST['tecnica_almacenamiento_insulina'] ?? 'No',
            'rotacion_sitios_abdomen' => $_POST['rotacion_sitios_abdomen'] ?? 'No',
            'rotacion_sitios_muslos' => $_POST['rotacion_sitios_muslos'] ?? 'No',
            'rotacion_sitios_brazos' => $_POST['rotacion_sitios_brazos'] ?? 'No',
            'deteccion_lipodistrofias' => $_POST['deteccion_lipodistrofias'] ?? 'No',
            
            // Habilidades Técnicas - Monitoreo
            'uso_glucometro' => $_POST['uso_glucometro'] ?? 'No',
            'uso_lancetero' => $_POST['uso_lancetero'] ?? 'No',
            'registro_bitacora' => $_POST['registro_bitacora'] ?? 'No',
            'frecuencia_medicion_adecuada' => $_POST['frecuencia_medicion_adecuada'] ?? 'No',
            'interpretacion_resultados' => $_POST['interpretacion_resultados'] ?? 'No',
            
            // 3) Medicación Oral
            'conoce_mecanismo_accion' => isset($_POST['conoce_mecanismo_accion']) ? 1 : 0,
            'identifica_efectos_secundarios' => isset($_POST['identifica_efectos_secundarios']) ? 1 : 0,
            'olvido_dosis_frecuencia' => $_POST['olvido_dosis_frecuencia'] ?? 'Nunca',
            'adherencia_oral_metformina' => isset($_POST['adherencia_oral_metformina']) ? 1 : 0,
            
            // 4) Resolución de Problemas
            'identificacion_sintomas_hipo' => $_POST['identificacion_sintomas_hipo'] ?? 'No',
            'aplicacion_regla_15' => $_POST['aplicacion_regla_15'] ?? 'No',
            'identificacion_sintomas_hiper' => $_POST['identificacion_sintomas_hiper'] ?? 'No',
            'cuando_medir_cetonas' => $_POST['cuando_medir_cetonas'] ?? 'No',
            'sabe_manejar_dias_enfermedad' => isset($_POST['sabe_manejar_dias_enfermedad']) ? 1 : 0,
            'plan_accion_crisis' => isset($_POST['plan_accion_crisis']) ? 1 : 0,
            
            // 5) Educación Nutricional
            'conteo_carbohidratos_nivel' => $_POST['conteo_carbohidratos_nivel'] ?? 'Nulo',
            'lectura_etiquetas' => $_POST['lectura_etiquetas'] ?? 'No',
            'calculo_porciones' => $_POST['calculo_porciones'] ?? 'No',
            'conoce_uso_suplementos' => isset($_POST['conoce_uso_suplementos']) ? 1 : 0,
            'suplemento_vit_d' => isset($_POST['suplemento_vit_d']) ? 1 : 0,
            'suplemento_omega_3' => isset($_POST['suplemento_omega_3']) ? 1 : 0,
            'suplemento_creatina' => isset($_POST['suplemento_creatina']) ? 1 : 0,
            'suplemento_proteina_suero' => isset($_POST['suplemento_proteina_suero']) ? 1 : 0,
            
            // Alimentos a evitar
            'evita_refrescos' => isset($_POST['evita_refrescos']) ? 1 : 0,
            'evita_pan_dulce' => isset($_POST['evita_pan_dulce']) ? 1 : 0,
            'evita_jugos' => isset($_POST['evita_jugos']) ? 1 : 0,
            'evita_mermeladas' => isset($_POST['evita_mermeladas']) ? 1 : 0,
            'evita_ultraprocesados' => isset($_POST['evita_ultraprocesados']) ? 1 : 0,
            
            // 6) Metas
            'meta_hba1c_objetivo' => !empty($_POST['meta_hba1c_objetivo']) ? floatval($_POST['meta_hba1c_objetivo']) : 7.0,
            'meta_glucosa_ayunas_max' => !empty($_POST['meta_glucosa_ayunas_max']) ? intval($_POST['meta_glucosa_ayunas_max']) : 130,
            'meta_reduccion_peso' => isset($_POST['meta_reduccion_peso']) ? 1 : 0,
            'meta_ejercicio_regular' => isset($_POST['meta_ejercicio_regular']) ? 1 : 0,
            'meta_adherencia_alimentacion' => isset($_POST['meta_adherencia_alimentacion']) ? 1 : 0,
            'metas_cumplidas_anteriores' => $_POST['metas_cumplidas_anteriores'] ?? '',
            'nuevas_metas_establecidas' => $_POST['nuevas_metas_establecidas'] ?? '',
            
            // 7) Antropometría
            'peso_actual' => !empty($_POST['peso_actual']) ? floatval($_POST['peso_actual']) : null,
            'talla_actual' => !empty($_POST['talla_actual']) ? floatval($_POST['talla_actual']) : null,
            'imc_actual' => null, // Se calcula automáticamente
            'circunferencia_cintura' => !empty($_POST['circunferencia_cintura']) ? floatval($_POST['circunferencia_cintura']) : null,
            'porcentaje_grasa' => !empty($_POST['porcentaje_grasa']) ? floatval($_POST['porcentaje_grasa']) : null,
            'masa_muscular_kg' => !empty($_POST['masa_muscular_kg']) ? floatval($_POST['masa_muscular_kg']) : null,
            
            // Recordatorio
            'recordatorio_24h_resumen' => $_POST['recordatorio_24h_resumen'] ?? '',
            'freq_agua_litros' => $_POST['freq_agua_litros'] ?? '< 1 litro',
            'freq_frutas_verduras' => $_POST['freq_frutas_verduras'] ?? '0-2 porciones',

            // Estado de cambio (transteórico)
            'estado_cambio' => $_POST['estado_cambio'] ?? null,

            // BDI-2 (21 ítems 0–3; total y clasificación se calculan en el modelo)
            'bdi2_item_01' => $_POST['bdi2_item_01'] ?? null,
            'bdi2_item_02' => $_POST['bdi2_item_02'] ?? null,
            'bdi2_item_03' => $_POST['bdi2_item_03'] ?? null,
            'bdi2_item_04' => $_POST['bdi2_item_04'] ?? null,
            'bdi2_item_05' => $_POST['bdi2_item_05'] ?? null,
            'bdi2_item_06' => $_POST['bdi2_item_06'] ?? null,
            'bdi2_item_07' => $_POST['bdi2_item_07'] ?? null,
            'bdi2_item_08' => $_POST['bdi2_item_08'] ?? null,
            'bdi2_item_09' => $_POST['bdi2_item_09'] ?? null,
            'bdi2_item_10' => $_POST['bdi2_item_10'] ?? null,
            'bdi2_item_11' => $_POST['bdi2_item_11'] ?? null,
            'bdi2_item_12' => $_POST['bdi2_item_12'] ?? null,
            'bdi2_item_13' => $_POST['bdi2_item_13'] ?? null,
            'bdi2_item_14' => $_POST['bdi2_item_14'] ?? null,
            'bdi2_item_15' => $_POST['bdi2_item_15'] ?? null,
            'bdi2_item_16' => $_POST['bdi2_item_16'] ?? null,
            'bdi2_item_17' => $_POST['bdi2_item_17'] ?? null,
            'bdi2_item_18' => $_POST['bdi2_item_18'] ?? null,
            'bdi2_item_19' => $_POST['bdi2_item_19'] ?? null,
            'bdi2_item_20' => $_POST['bdi2_item_20'] ?? null,
            'bdi2_item_21' => $_POST['bdi2_item_21'] ?? null,
            
            // Metadatos
            'observaciones_educador' => $_POST['observaciones_educador'] ?? '',
            'material_educativo_entregado' => $_POST['material_educativo_entregado'] ?? '',
            'created_by' => $_SESSION['usuario_id'] ?? 1
        ];
        
        if ($educacionDiabetes->guardar($datos_formulario)) {
            $message = "Evaluación de Educación en Diabetes guardada correctamente.";
            $message_type = "success";
            
            // Recargar datos
            $datos = $educacionDiabetes->obtenerPorVisita($id_visita) ?: [];
        } else {
            $message = "Error al guardar la evaluación.";
            $message_type = "danger";
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

$datos = [];
$visita = null;
$paciente = null;

if ($id_visita) {
    $datos = $educacionDiabetes->obtenerPorVisita($id_visita) ?: [];
    $visita = $visitaModel->obtenerPorId($id_visita);
    if ($visita) {
        $paciente = $pacienteModel->obtenerPorId($visita['id_paciente']);
    }
} else {
    $query_recent = "SELECT v.*,
                            CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) as nombre_completo,
                            p.fecha_nacimiento,
                            TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) as edad
                     FROM visitas v
                     JOIN pacientes p ON v.id_paciente = p.id_paciente
                     ORDER BY v.fecha_visita DESC
                     LIMIT 10";
    
    $stmt_recent = $db->prepare($query_recent);
    $stmt_recent->execute();
    $visitas_recientes = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);
}

include '../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-mortarboard-fill text-warning me-2"></i>Educación en Diabetes</h2>
            <?php if ($paciente && $visita): ?>
                <p class="text-muted mb-0">
                    <strong><?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno'] . ' ' . $paciente['apellido_materno']) ?></strong> 
                    - Visita ID: <?= $visita['id_visita'] ?> (<?= date('d/m/Y', strtotime($visita['fecha_visita'])) ?>)
                </p>
            <?php endif; ?>
        </div>
        <a href="../../index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- Mensajes -->
    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
            <i class="bi bi-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!$id_visita): ?>
    <!-- Búsqueda de Pacientes -->
    <div class="row">
        <div class="col-md-8 mx-auto">
                <div class="card shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="bi bi-search me-2"></i>Buscar Paciente</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <input type="text" id="buscar_paciente" class="form-control form-control-lg" 
                               placeholder="Escriba el nombre del paciente...">
                    </div>
                    <div id="resultados_busqueda"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visitas Recientes -->
    <?php if (!empty($visitas_recientes)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Visitas Recientes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Edad</th>
                                    <th>Fecha Visita</th>
                                    <th>ID Visita</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($visitas_recientes as $visita_reciente): ?>
                                <tr>
                                    <td><?= htmlspecialchars($visita_reciente['nombre_completo']) ?></td>
                                    <td><?= $visita_reciente['edad'] ?> años</td>
                                    <td><?= date('d/m/Y', strtotime($visita_reciente['fecha_visita'])) ?></td>
                                    <td>ID: <?= $visita_reciente['id_visita'] ?></td>
                                    <td>
                                        <a href="?id_visita=<?= $visita_reciente['id_visita'] ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-mortarboard-fill me-1"></i> Evaluar
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>

    <!-- Semáforo Educativo -->
    <?php if (!empty($datos) && isset($datos['semaforo_educativo'])): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-<?= $datos['semaforo_educativo'] == 'Verde' ? 'success' : ($datos['semaforo_educativo'] == 'Amarillo' ? 'warning' : 'danger') ?> d-flex align-items-center">
                <i class="bi bi-traffic-light fs-3 me-3"></i>
                <div>
                    <h5 class="mb-1">Semáforo Educativo: <?= $datos['semaforo_educativo'] ?></h5>
                    <p class="mb-0">Nivel de Autonomía: <strong><?= $datos['nivel_autonomia'] ?? 'No definido' ?></strong></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Formulario Principal -->
    <form method="POST" id="form_educacion_diabetes">
        <input type="hidden" name="id_visita" value="<?= $id_visita ?>">
        
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="educacionTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="diagnostico-tab" data-bs-toggle="tab" data-bs-target="#diagnostico" type="button">
                    <i class="bi bi-clipboard-check me-1"></i>Diagnóstico Educativo
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="habilidades-tab" data-bs-toggle="tab" data-bs-target="#habilidades" type="button">
                    <i class="bi bi-syringe me-1"></i>Habilidades Técnicas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="problemas-tab" data-bs-toggle="tab" data-bs-target="#problemas" type="button">
                    <i class="bi bi-exclamation-triangle me-1"></i>Resolución de Problemas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="psico-tab" data-bs-toggle="tab" data-bs-target="#psico" type="button">
                    <i class="bi bi-emoji-smile me-1"></i>Apoyo psicoeducativo
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="nutricion-tab" data-bs-toggle="tab" data-bs-target="#nutricion" type="button">
                    <i class="bi bi-apple me-1"></i>Educación Nutricional
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="metas-tab" data-bs-toggle="tab" data-bs-target="#metas" type="button">
                    <i class="bi bi-bullseye me-1"></i>Metas y Seguimiento
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="observaciones-tab" data-bs-toggle="tab" data-bs-target="#observaciones" type="button">
                    <i class="bi bi-stickies me-1"></i>Observaciones
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="educacionTabsContent">
            
            <!-- Tab 1: Diagnóstico Educativo -->
            <div class="tab-pane fade show active" id="diagnostico" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>Diagnósticos Educativos</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="conocimientos_deficientes_nutricion" id="conocimientos_deficientes_nutricion" 
                                           <?= !empty($datos['conocimientos_deficientes_nutricion']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="conocimientos_deficientes_nutricion">
                                        Conocimientos deficientes sobre alimentación y nutrición
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="no_cumple_recomendaciones" id="no_cumple_recomendaciones"
                                           <?= (!empty($datos['no_cumple_recomendaciones'])) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="no_cumple_recomendaciones">
                                        No cumplimiento de recomendaciones nutricionales
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="ingesta_excesiva_carbohidratos" id="ingesta_excesiva_carbohidratos"
                                           <?= (!empty($datos['ingesta_excesiva_carbohidratos'])) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="ingesta_excesiva_carbohidratos">
                                        Ingesta excesiva de carbohidratos simples
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="manejo_inadecuado_hipoglucemia" id="manejo_inadecuado_hipoglucemia"
                                           <?= (!empty($datos['manejo_inadecuado_hipoglucemia'])) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="manejo_inadecuado_hipoglucemia">
                                        Manejo inadecuado de episodios de hipoglucemia
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0"><i class="fas fa-ban me-2"></i>Barreras de Aprendizaje</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="barrera_nivel_educativo" id="barrera_nivel_educativo"
                                           <?= (!empty($datos['barrera_nivel_educativo'])) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="barrera_nivel_educativo">
                                        Nivel educativo limitado
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="barrera_economica" id="barrera_economica"
                                           <?= (!empty($datos['barrera_economica'])) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="barrera_economica">
                                        Limitaciones económicas
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="barrera_apoyo_familiar" id="barrera_apoyo_familiar"
                                           <?= (!empty($datos['barrera_apoyo_familiar'])) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="barrera_apoyo_familiar">
                                        Falta de apoyo familiar
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="barrera_psicologica" id="barrera_psicologica"
                                           <?= (!empty($datos['barrera_psicologica'])) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="barrera_psicologica">
                                        Barreras psicológicas (duelo, negación, depresión)
                                    </label>
                                </div>
                                <div class="mb-0">
                                    <label for="otras_barreras" class="form-label">Otras barreras:</label>
                                    <textarea class="form-control" name="otras_barreras" id="otras_barreras" rows="2"><?= $datos['otras_barreras'] ?? '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Habilidades Técnicas -->
            <div class="tab-pane fade" id="habilidades" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0"><i class="fas fa-syringe me-2"></i>Técnica de Inyección de Insulina</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="tecnica_seleccion_jeringa" class="form-label">Selección de jeringa/aguja:</label>
                                        <select class="form-select" name="tecnica_seleccion_jeringa" id="tecnica_seleccion_jeringa">
                                            <option value="No" <?= ($datos['tecnica_seleccion_jeringa'] ?? '') == 'No' ? 'selected' : '' ?>>No</option>
                                            <option value="En Proceso" <?= ($datos['tecnica_seleccion_jeringa'] ?? '') == 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                                            <option value="Sí" <?= ($datos['tecnica_seleccion_jeringa'] ?? '') == 'Sí' ? 'selected' : '' ?>>Sí</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tecnica_angulacion_pliegue" class="form-label">Angulación y pliegue:</label>
                                        <select class="form-select" name="tecnica_angulacion_pliegue" id="tecnica_angulacion_pliegue">
                                            <option value="No" <?= ($datos['tecnica_angulacion_pliegue'] ?? '') == 'No' ? 'selected' : '' ?>>No</option>
                                            <option value="En Proceso" <?= ($datos['tecnica_angulacion_pliegue'] ?? '') == 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                                            <option value="Sí" <?= ($datos['tecnica_angulacion_pliegue'] ?? '') == 'Sí' ? 'selected' : '' ?>>Sí</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0"><i class="fas fa-tint me-2"></i>Automonitoreo de Glucosa</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="uso_glucometro" class="form-label">Uso del glucómetro:</label>
                                        <select class="form-select" name="uso_glucometro" id="uso_glucometro">
                                            <option value="No" <?= ($datos['uso_glucometro'] ?? '') == 'No' ? 'selected' : '' ?>>No</option>
                                            <option value="En Proceso" <?= ($datos['uso_glucometro'] ?? '') == 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                                            <option value="Sí" <?= ($datos['uso_glucometro'] ?? '') == 'Sí' ? 'selected' : '' ?>>Sí</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="interpretacion_resultados" class="form-label">Interpretación de resultados:</label>
                                        <select class="form-select" name="interpretacion_resultados" id="interpretacion_resultados">
                                            <option value="No" <?= ($datos['interpretacion_resultados'] ?? '') == 'No' ? 'selected' : '' ?>>No</option>
                                            <option value="En Proceso" <?= ($datos['interpretacion_resultados'] ?? '') == 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                                            <option value="Sí" <?= ($datos['interpretacion_resultados'] ?? '') == 'Sí' ? 'selected' : '' ?>>Sí</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Resolución de Problemas -->
            <div class="tab-pane fade" id="problemas" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0"><i class="fas fa-arrow-down me-2"></i>Prevención de Hipoglucemia</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="identificacion_sintomas_hipo" class="form-label">Identificación de síntomas:</label>
                                    <select class="form-select" name="identificacion_sintomas_hipo" id="identificacion_sintomas_hipo">
                                        <option value="No" <?= ($datos['identificacion_sintomas_hipo'] ?? '') == 'No' ? 'selected' : '' ?>>No</option>
                                        <option value="En Proceso" <?= ($datos['identificacion_sintomas_hipo'] ?? '') == 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                                        <option value="Sí" <?= ($datos['identificacion_sintomas_hipo'] ?? '') == 'Sí' ? 'selected' : '' ?>>Sí</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="aplicacion_regla_15" class="form-label">Aplicación "Regla de los 15":</label>
                                    <select class="form-select" name="aplicacion_regla_15" id="aplicacion_regla_15">
                                        <option value="No" <?= ($datos['aplicacion_regla_15'] ?? '') == 'No' ? 'selected' : '' ?>>No</option>
                                        <option value="En Proceso" <?= ($datos['aplicacion_regla_15'] ?? '') == 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                                        <option value="Sí" <?= ($datos['aplicacion_regla_15'] ?? '') == 'Sí' ? 'selected' : '' ?>>Sí</option>
                                    </select>
                                    <div class="form-text">15g de carbohidratos, esperar 15 minutos</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0"><i class="fas fa-arrow-up me-2"></i>Prevención de Hiperglucemia</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="identificacion_sintomas_hiper" class="form-label">Identificación de síntomas:</label>
                                    <select class="form-select" name="identificacion_sintomas_hiper" id="identificacion_sintomas_hiper">
                                        <option value="No" <?= ($datos['identificacion_sintomas_hiper'] ?? '') == 'No' ? 'selected' : '' ?>>No</option>
                                        <option value="En Proceso" <?= ($datos['identificacion_sintomas_hiper'] ?? '') == 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                                        <option value="Sí" <?= ($datos['identificacion_sintomas_hiper'] ?? '') == 'Sí' ? 'selected' : '' ?>>Sí</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Apoyo psicoeducativo (BDI-2 + Estado de cambio) -->
            <div class="tab-pane fade" id="psico" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-clipboard-heart me-2"></i>Inventario de Depresión de Beck II (BDI-2)</h6>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted">
                                    Cada ítem se califica de 0 a 3. La suma total se calcula en el servidor y se clasifica como:
                                    0–13 Mínima, 14–19 Leve, 20–28 Moderada, 29–63 Severa.
                                </p>
                                <div class="row g-2">
                                    <?php
                                    // Helper inline para obtener valor de item BDI-2
                                    function bdiVal($datos, $n) {
                                        $key = 'bdi2_item_' . str_pad((string)$n, 2, '0', STR_PAD_LEFT);
                                        return isset($datos[$key]) ? (int)$datos[$key] : '';
                                    }
                                    for ($i = 1; $i <= 21; $i++):
                                        $key = 'bdi2_item_' . str_pad((string)$i, 2, '0', STR_PAD_LEFT);
                                        $val = isset($datos[$key]) ? (int)$datos[$key] : '';
                                    ?>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Ítem <?= $i ?></label>
                                            <select class="form-select form-select-sm" name="<?= $key ?>" id="<?= $key ?>">
                                                <option value="">-</option>
                                                <option value="0" <?= $val === 0 ? 'selected' : '' ?>>0</option>
                                                <option value="1" <?= $val === 1 ? 'selected' : '' ?>>1</option>
                                                <option value="2" <?= $val === 2 ? 'selected' : '' ?>>2</option>
                                                <option value="3" <?= $val === 3 ? 'selected' : '' ?>>3</option>
                                            </select>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0"><i class="bi bi-emoji-neutral me-2"></i>Resultado BDI-2</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-1">
                                    Puntuación total:
                                    <strong id="bdi2_total">
                                        <?= isset($datos['bdi2_puntuacion_total']) ? (int)$datos['bdi2_puntuacion_total'] : '-' ?>
                                    </strong>
                                </p>
                                <p class="mb-3">
                                    Clasificación:
                                    <span id="bdi2_clasificacion" class="fw-bold">
                                        <?= htmlspecialchars($datos['bdi2_clasificacion'] ?? '-') ?>
                                    </span>
                                </p>
                                <small class="text-muted d-block">
                                    * El cálculo definitivo se realiza en el servidor al guardar.
                                </small>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="bi bi-signpost-split me-2"></i>Estado de cambio</h6>
                            </div>
                            <div class="card-body">
                                <label for="estado_cambio" class="form-label">Etapa de cambio de conducta:</label>
                                <select name="estado_cambio" id="estado_cambio" class="form-select">
                                    <option value="">Seleccione...</option>
                                    <?php
                                    $estados = ['Precontemplación', 'Contemplación', 'Preparación', 'Acción', 'Mantenimiento', 'Recaída'];
                                    $estadoActual = $datos['estado_cambio'] ?? '';
                                    foreach ($estados as $e):
                                    ?>
                                        <option value="<?= $e ?>" <?= $estadoActual === $e ? 'selected' : '' ?>><?= $e ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Educación Nutricional -->
            <div class="tab-pane fade" id="nutricion" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Conteo de Carbohidratos</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="conteo_carbohidratos_nivel" class="form-label">Nivel de comprensión:</label>
                                    <select class="form-select" name="conteo_carbohidratos_nivel" id="conteo_carbohidratos_nivel">
                                        <option value="Nulo" <?= ($datos['conteo_carbohidratos_nivel'] ?? '') == 'Nulo' ? 'selected' : '' ?>>Nulo</option>
                                        <option value="Básico" <?= ($datos['conteo_carbohidratos_nivel'] ?? '') == 'Básico' ? 'selected' : '' ?>>Básico</option>
                                        <option value="Avanzado" <?= ($datos['conteo_carbohidratos_nivel'] ?? '') == 'Avanzado' ? 'selected' : '' ?>>Avanzado</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0"><i class="fas fa-ban me-2"></i>Alimentos a Evitar</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="evita_refrescos" id="evita_refrescos"
                                           <?= (!empty($datos['evita_refrescos'])) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="evita_refrescos">
                                        Refrescos
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="evita_jugos" id="evita_jugos"
                                           <?= (!empty($datos['evita_jugos'])) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="evita_jugos">
                                        Jugos
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 5: Metas y Seguimiento -->
            <div class="tab-pane fade" id="metas" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-bullseye me-2"></i>Metas de Control</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="meta_hba1c_objetivo" class="form-label">HbA1c objetivo (%):</label>
                                        <input type="number" class="form-control" name="meta_hba1c_objetivo" id="meta_hba1c_objetivo" 
                                               step="0.1" min="6" max="10" value="<?= $datos['meta_hba1c_objetivo'] ?? '7.0' ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="peso_actual" class="form-label">Peso (kg):</label>
                                        <input type="number" class="form-control" name="peso_actual" id="peso_actual" 
                                               step="0.1" min="30" max="200" value="<?= $datos['peso_actual'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-history me-2"></i>Seguimiento de Progreso</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="nuevas_metas_establecidas" class="form-label">Nuevas metas establecidas:</label>
                                    <textarea class="form-control" name="nuevas_metas_establecidas" id="nuevas_metas_establecidas" rows="3"><?= $datos['nuevas_metas_establecidas'] ?? '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 6: Observaciones -->
            <div class="tab-pane fade" id="observaciones" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Observaciones del Educador</h6>
                            </div>
                            <div class="card-body">
                                <textarea class="form-control" name="observaciones_educador" id="observaciones_educador" rows="8" 
                                          placeholder="Observaciones, comentarios adicionales, progreso del paciente..."><?= $datos['observaciones_educador'] ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="fas fa-book-open me-2"></i>Material Educativo Entregado</h6>
                            </div>
                            <div class="card-body">
                                <textarea class="form-control" name="material_educativo_entregado" id="material_educativo_entregado" rows="8" 
                                          placeholder="Lista del material educativo proporcionado al paciente..."><?= $datos['material_educativo_entregado'] ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="../../index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Cancelar
                    </a>
                    <button type="submit" name="save_consulta" class="btn btn-warning btn-lg">
                        <i class="bi bi-save me-2"></i> Guardar Evaluación
                    </button>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Búsqueda de pacientes (AJAX GET + JSON)
    var buscarInput = document.getElementById('buscar_paciente');
    var resultados = document.getElementById('resultados_busqueda');

    if (buscarInput && resultados) {
        var debounce = function (fn, ms) {
            var t;
            return function () {
                clearTimeout(t);
                t = setTimeout(fn.bind(this), ms);
            };
        };

        buscarInput.addEventListener('input', debounce(function () {
            var termino = this.value.trim();
            if (termino.length < 2) {
                resultados.innerHTML = '';
                return;
            }
            resultados.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-warning"></div></div>';

            var xhr = new XMLHttpRequest();
            xhr.open('GET', '../../ajax/buscar_pacientes_diabetes.php?search=' + encodeURIComponent(termino));
            xhr.onload = function () {
                try {
                    var r = JSON.parse(xhr.responseText);
                    if (r.success && r.html) {
                        resultados.innerHTML = r.html;
                    } else {
                        resultados.innerHTML = '<div class="p-4 text-center text-muted">No se encontraron pacientes</div>';
                    }
                } catch (e) {
                    resultados.innerHTML = '<div class="p-4 text-center text-danger">Error al buscar</div>';
                }
            };
            xhr.send();
        }, 400));
    }

    // Indicador de semáforo educativo en tiempo real (cliente)
    function actualizarSemaforoPreview() {
        var factoresRojos = 0;
        var factoresVerdes = 0;

        var conocimientosDef = document.getElementById('conocimientos_deficientes_nutricion');
        var manejoHipo = document.getElementById('manejo_inadecuado_hipoglucemia');
        var conteoNivel = document.getElementById('conteo_carbohidratos_nivel');
        var identHipo = document.getElementById('identificacion_sintomas_hipo');
        var regla15 = document.getElementById('aplicacion_regla_15');
        var usoGluco = document.getElementById('uso_glucometro');
        var interpRes = document.getElementById('interpretacion_resultados');
        var evitaRef = document.getElementById('evita_refrescos');
        var evitaJugos = document.getElementById('evita_jugos');

        if (conocimientosDef && conocimientosDef.checked) factoresRojos++;
        if (manejoHipo && manejoHipo.checked) factoresRojos++;
        if (conteoNivel && conteoNivel.value === 'Nulo') factoresRojos++;
        if (identHipo && identHipo.value === 'No') factoresRojos++;
        if (regla15 && regla15.value === 'No') factoresRojos++;

        if (conteoNivel && conteoNivel.value === 'Avanzado') factoresVerdes++;
        if (usoGluco && usoGluco.value === 'Sí') factoresVerdes++;
        if (interpRes && interpRes.value === 'Sí') factoresVerdes++;
        if (evitaRef && evitaRef.checked && evitaJugos && evitaJugos.checked) factoresVerdes++;

        var semaforoTexto = '';
        if (factoresRojos >= 3) {
            semaforoTexto = '<span class="badge bg-danger">Rojo - Requiere intervención intensiva</span>';
        } else if (factoresVerdes >= 3 && factoresRojos <= 1) {
            semaforoTexto = '<span class="badge bg-success">Verde - Paciente empoderado</span>';
        } else {
            semaforoTexto = '<span class="badge bg-warning text-dark">Amarillo - En proceso educativo</span>';
        }

        var form = document.getElementById('form_educacion_diabetes');
        if (!form) return;
        var indicador = document.getElementById('indicador_semaforo');
        if (!indicador) {
            indicador = document.createElement('div');
            indicador.id = 'indicador_semaforo';
            indicador.className = 'alert alert-info text-center mb-3';
            indicador.innerHTML = 'Semáforo Educativo: ' + semaforoTexto;
            form.prepend(indicador);
        } else {
            indicador.innerHTML = 'Semáforo Educativo: ' + semaforoTexto;
        }
    }

    // Hook de cambios para actualizar semáforo preview
    document.querySelectorAll('#form_educacion_diabetes input, #form_educacion_diabetes select').forEach(function (el) {
        el.addEventListener('change', actualizarSemaforoPreview);
    });
});
</script>

<?php include '../../includes/footer.php'; ?>