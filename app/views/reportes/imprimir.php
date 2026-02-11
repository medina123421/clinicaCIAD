<?php
/**
 * Impresión de Reporte Individual
 */

require_once '../../config/database.php';
require_once '../../models/Analisis.php';

$id_visita = $_GET['id'] ?? null;
if (!$id_visita)
    die("ID de visita no especificado.");

$database = new Database();
$db = $database->getConnection();
$analisis_model = new Analisis($db);

$detalle = $analisis_model->obtenerResultadosPorVisita($id_visita);

if (!$detalle)
    die("No se encontraron resultados para esta visita.");

// Calcular Edad
$fecha_nac = new DateTime($detalle['fecha_nacimiento']);
$hoy = new DateTime();
$edad = $hoy->diff($fecha_nac)->y;

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Resultado de Análisis -
        <?= htmlspecialchars($detalle['numero_expediente']) ?>
    </title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .logo-area {
            text-align: left;
            vertical-align: middle;
        }

        .info-lab {
            text-align: right;
            font-size: 10px;
            color: #555;
        }

        .patient-info {
            width: 100%;
            margin-bottom: 20px;
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
        }

        .patient-info td {
            padding: 4px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            color: #000;
        }

        h3 {
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            color: #004085;
            text-transform: uppercase;
            font-size: 14px;
            margin-top: 20px;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .results-table th {
            background-color: #eee;
            text-align: left;
            padding: 6px;
            border-bottom: 1px solid #aaa;
            font-size: 11px;
        }

        .results-table td {
            padding: 5px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        .range {
            font-size: 10px;
            color: #666;
            font-style: italic;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()"
            style="padding: 10px 20px; background: #0d6efd; color: white; border: none; cursor: pointer;">Imprimir
            Reporte</button>
        <button onclick="window.close()"
            style="padding: 10px 20px; background: #6c757d; color: white; border: none; cursor: pointer;">Cerrar</button>
    </div>

    <!-- Encabezado -->
    <table class="header-table">
        <tr>
            <td class="logo-area">
                <h1 style="margin: 0; font-size: 24px; color: #333;">Laboratorio Clínico "InvesLab"</h1>
                <span style="font-size: 12px; font-weight: bold;">Experiencia y Calidad a su servicio</span>
            </td>
            <td class="info-lab">
                5 de mayo #41, Col. Vicente Guerrero, CP: 40080, Iguala, Gro.<br>
                Celular y WhatsApp: 733 151 1178<br>
                Horario: Lunes a Viernes 07:00 am - 06:00 pm
            </td>
        </tr>
    </table>

    <!-- Datos del Paciente -->
    <table class="patient-info">
        <tr>
            <td width="15%"><span class="label">Paciente:</span></td>
            <td width="45%">
                <?= htmlspecialchars($detalle['nombre'] . ' ' . $detalle['apellido_paterno'] . ' ' . $detalle['apellido_materno']) ?>
            </td>
            <td width="10%"><span class="label">Edad:</span></td>
            <td width="10%">
                <?= $edad ?> años
            </td>
            <td width="10%"><span class="label">Sexo:</span></td>
            <td width="10%">
                <?= $detalle['sexo'] ?>
            </td>
        </tr>
        <tr>
            <td><span class="label">Expediente:</span></td>
            <td>
                <?= htmlspecialchars($detalle['numero_expediente']) ?>
            </td>
            <td><span class="label">Fecha:</span></td>
            <td colspan="3">
                <?= date('d/m/Y', strtotime($detalle['fecha_visita'])) ?>
            </td>
        </tr>
    </table>

    <!-- ================= BIOMETRÍA HEMÁTICA ================= -->
    <?php if ($detalle['id_biometria']): ?>
        <h3>Biometría Hemática</h3>
        <table class="results-table">
            <thead>
                <tr>
                    <th width="40%">Parámetro</th>
                    <th width="30%">Resultado</th>
                    <th width="30%">Valores de Referencia</th>
                </tr>
            </thead>
            <tbody>
                <!-- Serie Roja -->
                <tr>
                    <td>Eritrocitos</td>
                    <td><b>
                            <?= $detalle['eritrocitos'] ?>
                        </b> 10⁶/µL</td>
                    <td class="range">M: 4.5-5.9 | F: 4.0-5.2</td>
                </tr>
                <tr>
                    <td>Hemoglobina</td>
                    <td><b>
                            <?= $detalle['hemoglobina'] ?>
                        </b> g/dL</td>
                    <td class="range">M: 14.0-17.5 | F: 12.3-15.3</td>
                </tr>
                <tr>
                    <td>Hematocrito</td>
                    <td><b>
                            <?= $detalle['hematocrito'] ?>
                        </b> %</td>
                    <td class="range">M: 42-50 | F: 37-47</td>
                </tr>
                <tr>
                    <td>V.G.M (Vol. Globular Medio)</td>
                    <td>
                        <?= $detalle['vgm'] ?> fL
                    </td>
                    <td class="range">80 - 100</td>
                </tr>
                <tr>
                    <td>H.G.M</td>
                    <td>
                        <?= $detalle['hgm'] ?> pg
                    </td>
                    <td class="range">27 - 33</td>
                </tr>
                <tr>
                    <td>C.M.H.G</td>
                    <td>
                        <?= $detalle['cmhg'] ?> g/dL
                    </td>
                    <td class="range">32 - 36</td>
                </tr>
                <tr>
                    <td>IDE / RDW</td>
                    <td>
                        <?= $detalle['ide'] ?> %
                    </td>
                    <td class="range">11 - 15</td>
                </tr>

                <!-- Serie Blanca -->
                <tr style="background-color: #f8f9fa;">
                    <td colspan="3"><b>Leucocitos (Serie Blanca)</b></td>
                </tr>
                <tr>
                    <td>Leucocitos Totales</td>
                    <td><b>
                            <?= $detalle['leucocitos'] ?>
                        </b> 10³/µL</td>
                    <td class="range">4.5 - 11.0</td>
                </tr>
                <tr>
                    <td>Neutrófilos %</td>
                    <td>
                        <?= $detalle['neutrofilos_perc'] ?> %
                    </td>
                    <td class="range">40 - 70 %</td>
                </tr>
                <tr>
                    <td>Linfocitos %</td>
                    <td>
                        <?= $detalle['linfocitos_perc'] ?> %
                    </td>
                    <td class="range">20 - 45 %</td>
                </tr>
                <tr>
                    <td>MID % (Mono/Eos)</td>
                    <td>
                        <?= $detalle['mid_perc'] ?> %
                    </td>
                    <td class="range">0 - 10 %</td>
                </tr>

                <!-- Plaquetas -->
                <tr style="background-color: #f8f9fa;">
                    <td colspan="3"><b>Plaquetas</b></td>
                </tr>
                <tr>
                    <td>Recuento Total</td>
                    <td><b>
                            <?= $detalle['plaquetas'] ?>
                        </b> 10³/µL</td>
                    <td class="range">150 - 450</td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- ================= QUÍMICA SANGUÍNEA ================= -->
    <?php if ($detalle['id_quimica']): ?>
        <h3>Química Sanguínea</h3>
        <table class="results-table">
            <thead>
                <tr>
                    <th width="40%">Parámetro</th>
                    <th width="30%">Resultado</th>
                    <th width="30%">Valores de Referencia</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($detalle['glucosa']): ?>
                    <tr>
                        <td>Glucosa</td>
                        <td><b>
                                <?= $detalle['glucosa'] ?>
                            </b> mg/dL</td>
                        <td class="range">70 - 100</td>
                    </tr>
                <?php endif; ?>
                <?php if ($detalle['urea']): ?>
                    <tr>
                        <td>Urea</td>
                        <td><b>
                                <?= $detalle['urea'] ?>
                            </b> mg/dL</td>
                        <td class="range">15 - 45</td>
                    </tr>
                <?php endif; ?>
                <?php if ($detalle['bun']): ?>
                    <tr>
                        <td>BUN</td>
                        <td><b>
                                <?= $detalle['bun'] ?>
                            </b> mg/dL</td>
                        <td class="range">7 - 20</td>
                    </tr>
                <?php endif; ?>
                <?php if ($detalle['creatinina']): ?>
                    <tr>
                        <td>Creatinina</td>
                        <td><b>
                                <?= $detalle['creatinina'] ?>
                            </b> mg/dL</td>
                        <td class="range">0.6 - 1.2</td>
                    </tr>
                <?php endif; ?>
                <?php if ($detalle['acido_urico']): ?>
                    <tr>
                        <td>Ácido Úrico</td>
                        <td><b>
                                <?= $detalle['acido_urico'] ?>
                            </b> mg/dL</td>
                        <td class="range">3.5 - 7.2</td>
                    </tr>
                <?php endif; ?>
                <?php if ($detalle['colesterol']): ?>
                    <tr>
                        <td>Colesterol</td>
                        <td><b>
                                <?= $detalle['colesterol'] ?>
                            </b> mg/dL</td>
                        <td class="range">
                            < 200</td>
                    </tr>
                <?php endif; ?>
                <?php if ($detalle['trigliceridos']): ?>
                    <tr>
                        <td>Triglicéridos</td>
                        <td><b>
                                <?= $detalle['trigliceridos'] ?>
                            </b> mg/dL</td>
                        <td class="range">
                            < 150</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- ================= EGO ================= -->
    <?php if ($detalle['id_orina']): ?>
        <h3>Examen General de Orina</h3>
        <table class="results-table">
            <thead>
                <tr>
                    <th width="33%">Examen Físico</th>
                    <th width="33%">Examen Químico</th>
                    <th width="33%">Microscópico (Sedimento)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td valign="top">
                        Color:
                        <?= $detalle['color'] ?><br>
                        Aspecto:
                        <?= $detalle['aspecto'] ?>
                    </td>
                    <td valign="top">
                        Densidad:
                        <?= $detalle['densidad'] ?><br>
                        pH:
                        <?= $detalle['ph'] ?><br>
                        Leucocitos:
                        <?= $detalle['leucocitos_quimico'] ?><br>
                        Nitritos:
                        <?= $detalle['nitritos'] ?><br>
                        Proteínas:
                        <?= $detalle['proteinas'] ?><br>
                        Glucosa:
                        <?= $detalle['glucosa_quimico'] ?><br>
                        Cetonas:
                        <?= $detalle['cetonas'] ?><br>
                        Bilirrubina:
                        <?= $detalle['bilirrubina'] ?>
                    </td>
                    <td valign="top">
                        Cél. Escamosas:
                        <?= $detalle['celulas_escamosas'] ?><br>
                        Cél. Cilíndricas:
                        <?= $detalle['celulas_cilindricas'] ?><br>
                        Leucocitos:
                        <?= $detalle['leucocitos_micro'] ?><br>
                        Eritrocitos:
                        <?= $detalle['eritrocitos_micro'] ?><br>
                        Bacterias:
                        <?= $detalle['bacterias'] ?><br>
                        Hongos:
                        <?= $detalle['hongos'] ?><br>
                        Parásitos:
                        <?= $detalle['parasitos'] ?>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="footer">
        <p>_______________________________________<br>QBP. Yeni Mastache Piña - Cédula Prof: 4004301</p>
        <p>Este informe es confidencial y para uso exclusivo médico.</p>
    </div>

</body>

</html>