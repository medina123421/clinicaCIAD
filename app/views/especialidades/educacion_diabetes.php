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

// Función helper para evitar warnings de undefined index
function getValue($array, $key, $default = '') {
    return isset($array[$key]) ? $array[$key] : $default;
}

function isChecked($array, $key) {
    return !empty($array[$key] ?? 0) ? 'checked' : '';
}

function isSelected($array, $key, $value) {
    return ($array[$key] ?? '') == $value ? 'selected' : '';
}
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-graduation-cap text-warning me-2"></i>Educación en Diabetes</h2>
            <?php if ($paciente && $visita): ?>
                <p class="text-muted mb-0">
                    <strong><?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno'] . ' ' . $paciente['apellido_materno']) ?></strong> 
                    - Visita ID: <?= $visita['id_visita'] ?> (<?= date('d/m/Y', strtotime($visita['fecha_visita'])) ?>)
                </p>
            <?php endif; ?>
        </div>
        <a href="../../index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- Mensajes -->
    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
            <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
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
                    <h5 class="mb-0"><i class="fas fa-search me-2"></i>Buscar Paciente</h5>
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
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Visitas Recientes</h5>
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
                                            <i class="fas fa-graduation-cap me-1"></i> Evaluar
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
                <i class="fas fa-traffic-light fs-3 me-3"></i>
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
                    <i class="fas fa-clipboard-list me-1"></i>Diagnóstico Educativo
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="habilidades-tab" data-bs-toggle="tab" data-bs-target="#habilidades" type="button">
                    <i class="fas fa-syringe me-1"></i>Habilidades Técnicas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="problemas-tab" data-bs-toggle="tab" data-bs-target="#problemas" type="button">
                    <i class="fas fa-exclamation-triangle me-1"></i>Resolución de Problemas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="nutricion-tab" data-bs-toggle="tab" data-bs-target="#nutricion" type="button">
                    <i class="fas fa-apple-alt me-1"></i>Educación Nutricional
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="metas-tab" data-bs-toggle="tab" data-bs-target="#metas" type="button">
                    <i class="fas fa-bullseye me-1"></i>Metas y Seguimiento
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="observaciones-tab" data-bs-toggle="tab" data-bs-target="#observaciones" type="button">
                    <i class="fas fa-sticky-note me-1"></i>Observaciones
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
                                           <?= isChecked($datos, 'conocimientos_deficientes_nutricion') ?>>
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
                                    <textarea class="form-control" name="otras_barreras" id="otras_barreras" rows="2"><?= getValue($datos, 'otras_barreras') ?></textarea>
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
                                            <option value="No" <?= isSelected($datos, 'tecnica_seleccion_jeringa', 'No') ?>>No</option>
                                            <option value="En Proceso" <?= isSelected($datos, 'tecnica_seleccion_jeringa', 'En Proceso') ?>>En Proceso</option>
                                            <option value="Sí" <?= isSelected($datos, 'tecnica_seleccion_jeringa', 'Sí') ?>>Sí</option>
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
                        <i class="fas fa-arrow-left me-1"></i> Cancelar
                    </a>
                    <button type="submit" name="save_consulta" class="btn btn-warning btn-lg">
                        <i class="fas fa-save me-2"></i> Guardar Evaluación
                    </button>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Búsqueda de pacientes
    $('#buscar_paciente').on('input', function() {
        var termino = $(this).val();
        if (termino.length >= 2) {
            $.post('../../ajax/buscar_pacientes_diabetes.php', {
                termino: termino
            }, function(data) {
                $('#resultados_busqueda').html(data);
            });
        } else {
            $('#resultados_busqueda').html('');
        }
    });

    // Actualización visual del semáforo (simulación)
    function actualizarSemaforo() {
        var factoresRojos = 0;
        var factoresVerdes = 0;

        // Contar factores críticos
        if ($('#conocimientos_deficientes_nutricion').is(':checked')) factoresRojos++;
        if ($('#manejo_inadecuado_hipoglucemia').is(':checked')) factoresRojos++;
        if ($('#conteo_carbohidratos_nivel').val() === 'Nulo') factoresRojos++;
        if ($('#identificacion_sintomas_hipo').val() === 'No') factoresRojos++;
        if ($('#aplicacion_regla_15').val() === 'No') factoresRojos++;

        // Contar factores positivos
        if ($('#conteo_carbohidratos_nivel').val() === 'Avanzado') factoresVerdes++;
        if ($('#uso_glucometro').val() === 'Sí') factoresVerdes++;
        if ($('#interpretacion_resultados').val() === 'Sí') factoresVerdes++;
        if ($('#evita_refrescos').is(':checked') && $('#evita_jugos').is(':checked')) factoresVerdes++;

        // Mostrar indicador visual (opcional)
        var semaforoTexto = '';
        if (factoresRojos >= 3) {
            semaforoTexto = '<span class="badge bg-danger">Rojo - Requiere intervención intensiva</span>';
        } else if (factoresVerdes >= 3 && factoresRojos <= 1) {
            semaforoTexto = '<span class="badge bg-success">Verde - Paciente empoderado</span>';
        } else {
            semaforoTexto = '<span class="badge bg-warning">Amarillo - En proceso educativo</span>';
        }
        
        // Agregar indicador si no existe
        if ($('#indicador_semaforo').length === 0) {
            $('#form_educacion_diabetes').prepend('<div id="indicador_semaforo" class="alert alert-info text-center mb-3">Semáforo Educativo: ' + semaforoTexto + '</div>');
        } else {
            $('#indicador_semaforo').html('Semáforo Educativo: ' + semaforoTexto);
        }
    }

    // Actualizar semáforo en tiempo real
    $('input, select').on('change', actualizarSemaforo);
});
</script>

<?php include '../../includes/footer.php'; ?>