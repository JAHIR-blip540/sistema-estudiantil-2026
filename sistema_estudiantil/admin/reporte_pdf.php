<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
require_once '../funciones.php';

// Permitir acceso a admin y consejero
if ($_SESSION['user_rol'] == 'admin') {
    requireRole('admin');
} elseif ($_SESSION['user_rol'] == 'consejero') {
    requireRole('consejero');
} else {
    header('Location: ../login.php');
    exit();
}

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'diario';
$curso = isset($_GET['curso']) ? $_GET['curso'] : '';
$seccion = isset($_GET['seccion']) ? $_GET['seccion'] : '';

// Configurar cabeceras para PDF
header('Content-Type: text/html');
header('Content-Disposition: inline; filename="reporte_asistencia.html"');

// Generar contenido según tipo
$html = '';
$titulo = '';

if ($tipo == 'diario') {
    $fecha = date('Y-m-d');
    $titulo = 'Reporte Diario de Asistencia - ' . date('d/m/Y');
    
    $html .= '<div class="reporte-header">
        <h1>CEMG Tecnico "Dr. Jorge Fidel Duron"</h1>
        <h3>DHS Access Control</h3>
        <p>Reporte de Asistencia Diario</p>
        <p>Fecha: ' . date('d/m/Y') . '</p>
    </div>';
    
    // Usando DATE(fecha_hora)
    $sql = "SELECT a.*, e.nombre, e.apellido, e.curso, e.seccion, e.jornada 
            FROM asistencia a 
            JOIN estudiantes e ON a.estudiante_id = e.id 
            WHERE DATE(a.fecha_hora) = CURDATE() 
            ORDER BY a.fecha_hora DESC";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $html .= '<table>
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Curso</th>
                    <th>Sección</th>
                    <th>Tipo</th>
                    <th>Hora</th>
                </tr>
            </thead>
            <tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            $tipo_asistencia = $row['tipo'] == 'entrada' ? '🟢 Entrada' : '🔴 Salida';
            $html .= '<tr>
                <td>' . $row['nombre'] . ' ' . $row['apellido'] . '</td>
                <td>' . $row['curso'] . '</td>
                <td>' . $row['seccion'] . '</td>
                <td>' . $tipo_asistencia . '</td>
                <td>' . date('H:i:s', strtotime($row['fecha_hora'])) . '</td>
            </tr>';
        }
        $html .= '</tbody></table>';
        
        // Estadísticas
        $sql_entradas = "SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE() AND tipo = 'entrada'";
        $result_entradas = mysqli_query($conn, $sql_entradas);
        $entradas = mysqli_fetch_assoc($result_entradas)['total'];
        
        $sql_salidas = "SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE() AND tipo = 'salida'";
        $result_salidas = mysqli_query($conn, $sql_salidas);
        $salidas = mysqli_fetch_assoc($result_salidas)['total'];
        
        $html .= '<div style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px;">
            <p><strong>📊 Resumen del día:</strong></p>
            <p>🟢 Entradas (Asistencias): ' . $entradas . ' | 🔴 Salidas: ' . $salidas . '</p>
            <p class="total">Total registros: ' . ($entradas + $salidas) . '</p>
        </div>';
    } else {
        $html .= '<div style="text-align: center; padding: 30px; background: #fff3cd; border-radius: 8px; margin: 20px 0;">
            <h3 style="color: #856404;">⚠️ No hay registros de asistencia para hoy</h3>
            <p style="color: #856404;">Fecha actual: ' . date('d/m/Y') . '</p>
            <p style="color: #856404;">Registra una asistencia desde el escáner QR del guardia.</p>
        </div>';
    }
    
} elseif ($tipo == 'mensual') {
    $mes = date('m');
    $year = date('Y');
    $nombre_mes = date('F', mktime(0, 0, 0, $mes, 1));
    $titulo = 'Reporte Mensual de Asistencia - ' . $nombre_mes . ' ' . $year;
    
    $html .= '<div class="reporte-header">
        <h1>CEMG Tecnico "Dr. Jorge Fidel Duron"</h1>
        <h3>DHS Access Control</h3>
        <p>Reporte de Asistencia Mensual</p>
        <p>Mes: ' . $nombre_mes . ' de ' . $year . '</p>
    </div>';
    
    $sql = "SELECT a.*, e.nombre, e.apellido, e.curso, e.seccion, e.jornada 
            FROM asistencia a 
            JOIN estudiantes e ON a.estudiante_id = e.id 
            WHERE MONTH(a.fecha_hora) = '$mes' AND YEAR(a.fecha_hora) = '$year'
            ORDER BY a.fecha_hora DESC";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $html .= '<table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Estudiante</th>
                    <th>Curso</th>
                    <th>Sección</th>
                    <th>Tipo</th>
                    <th>Hora</th>
                </tr>
            </thead>
            <tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            $tipo_asistencia = $row['tipo'] == 'entrada' ? '🟢 Entrada' : '🔴 Salida';
            $html .= '<tr>
                <td>' . date('d/m/Y', strtotime($row['fecha_hora'])) . '</td>
                <td>' . $row['nombre'] . ' ' . $row['apellido'] . '</td>
                <td>' . $row['curso'] . '</td>
                <td>' . $row['seccion'] . '</td>
                <td>' . $tipo_asistencia . '</td>
                <td>' . date('H:i:s', strtotime($row['fecha_hora'])) . '</td>
            </tr>';
        }
        $html .= '</tbody></table>';
        
        $sql_total = "SELECT 
                      COUNT(*) as total, 
                      SUM(CASE WHEN tipo = 'entrada' THEN 1 ELSE 0 END) as entradas,
                      SUM(CASE WHEN tipo = 'salida' THEN 1 ELSE 0 END) as salidas
                      FROM asistencia 
                      WHERE MONTH(fecha_hora) = '$mes' AND YEAR(fecha_hora) = '$year'";
        $result_total = mysqli_query($conn, $sql_total);
        $stats = mysqli_fetch_assoc($result_total);
        
        $html .= '<div style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px;">
            <p><strong>📊 Resumen del mes:</strong></p>
            <p>🟢 Entradas (Asistencias): ' . $stats['entradas'] . ' | 🔴 Salidas: ' . $stats['salidas'] . '</p>
            <p class="total">Total registros: ' . $stats['total'] . '</p>
        </div>';
    } else {
        $html .= '<p style="text-align: center; color: #666;">No hay registros de asistencia para este mes</p>';
    }
    
} elseif ($tipo == 'curso') {
    $curso = isset($_GET['curso']) ? $_GET['curso'] : '';
    $seccion = isset($_GET['seccion']) ? $_GET['seccion'] : '';
    $titulo = 'Reporte por Curso - ' . $curso . ($seccion ? ' - Sección ' . $seccion : '');
    
    $html .= '<div class="reporte-header">
        <h1>CEMG Tecnico "Dr. Jorge Fidel Duron"</h1>
        <h3>DHS Access Control</h3>
        <p>Reporte de Asistencia por Curso</p>
        <p>Curso: ' . $curso . ($seccion ? ' - Sección ' . $seccion : 'Todas las secciones') . '</p>
        <p>Fecha: ' . date('d/m/Y') . '</p>
    </div>';
    
    $sql = "SELECT a.*, e.nombre, e.apellido, e.curso, e.seccion, e.jornada 
            FROM asistencia a 
            JOIN estudiantes e ON a.estudiante_id = e.id 
            WHERE e.curso = '$curso'";
    
    if ($seccion) {
        $sql .= " AND e.seccion = '$seccion'";
    }
    
    $sql .= " ORDER BY a.fecha_hora DESC";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $html .= '<table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Estudiante</th>
                    <th>Sección</th>
                    <th>Tipo</th>
                    <th>Hora</th>
                </tr>
            </thead>
            <tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            $tipo_asistencia = $row['tipo'] == 'entrada' ? '🟢 Entrada' : '🔴 Salida';
            $html .= '<tr>
                <td>' . date('d/m/Y', strtotime($row['fecha_hora'])) . '</td>
                <td>' . $row['nombre'] . ' ' . $row['apellido'] . '</td>
                <td>' . $row['seccion'] . '</td>
                <td>' . $tipo_asistencia . '</td>
                <td>' . date('H:i:s', strtotime($row['fecha_hora'])) . '</td>
            </tr>';
        }
        $html .= '</tbody></table>';
        
        $sql_stats = "SELECT 
                      COUNT(*) as total, 
                      SUM(CASE WHEN tipo = 'entrada' THEN 1 ELSE 0 END) as entradas,
                      SUM(CASE WHEN tipo = 'salida' THEN 1 ELSE 0 END) as salidas
                      FROM asistencia a 
                      JOIN estudiantes e ON a.estudiante_id = e.id 
                      WHERE e.curso = '$curso'";
        if ($seccion) {
            $sql_stats .= " AND e.seccion = '$seccion'";
        }
        $result_stats = mysqli_query($conn, $sql_stats);
        $stats = mysqli_fetch_assoc($result_stats);
        
        $html .= '<div style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px;">
            <p><strong>📊 Resumen del curso:</strong></p>
            <p>🟢 Entradas (Asistencias): ' . $stats['entradas'] . ' | 🔴 Salidas: ' . $stats['salidas'] . '</p>
            <p class="total">Total registros: ' . $stats['total'] . '</p>
        </div>';
    } else {
        $html .= '<p style="text-align: center; color: #666;">No hay registros de asistencia para este curso</p>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo; ?></title>
    <style>
        @page { margin: 2cm; }
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 20px;
        }
        .reporte-header { 
            text-align: center; 
            border-bottom: 2px solid #0D47A1; 
            padding-bottom: 20px; 
            margin-bottom: 20px; 
        }
        .reporte-header h1 { 
            color: #0D47A1; 
            margin: 0; 
            font-size: 24px;
        }
        .reporte-header h3 { 
            color: #666; 
            margin: 5px 0; 
        }
        .reporte-header p { 
            color: #999; 
            margin: 0; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        table th { 
            background: #0D47A1; 
            color: white; 
            padding: 10px; 
            text-align: left; 
        }
        table td { 
            padding: 8px; 
            border-bottom: 1px solid #ddd; 
        }
        table tr:nth-child(even) { 
            background: #f9f9f9; 
        }
        .footer { 
            text-align: center; 
            margin-top: 30px; 
            color: #999; 
            font-size: 12px; 
            border-top: 1px solid #ddd; 
            padding-top: 20px; 
        }
        .total { 
            font-weight: bold; 
            color: #0D47A1; 
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
        .btn-print {
            background: #0D47A1;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin: 20px 0;
        }
        .btn-print:hover {
            background: #0D47A1;
            opacity: 0.8;
        }
        .btn-back {
            background: #666;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin: 20px 0 20px 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-back:hover {
            background: #888;
            color: white;
        }
    </style>

    <style>
        .copyright-footer { margin-top: 24px; padding: 12px 8px; text-align: center; color: #B0BEC5; font-size: 12px; line-height: 1.5; border-top: 1px solid rgba(128,128,128,.25); }
        .copyright-footer div:first-child { font-weight: 600; }
        @media print { .copyright-footer { color: #333; border-top: 1px solid #bbb; font-size: 10px; } }
    </style>
</head>
<body>
    <?php echo $html; ?>
    
    <div class="footer">
        Reporte generado el <?php echo date('d/m/Y H:i:s'); ?> | DHS Access Control
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir / Guardar PDF
        </button>
        <a href="javascript:history.back()" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <footer class="copyright-footer">
        <div>© 2026 Jahir Alexander Zuniga Enamorado</div>
        <div>Duodecimo Grado en Informatica Año 2026</div>
    </footer>
</body>
</html>