<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
requireRole('padre');

$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Verificar que el padre tenga acceso a este estudiante
$telefono = $_SESSION['user_phone'] ?? '';
$sql = "SELECT * FROM estudiantes WHERE id = $id AND telefono_padre = '$telefono' AND activo = 1";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header('Location: dashboard.php');
    exit();
}

$estudiante = mysqli_fetch_assoc($result);
include '../header.php';
?>

<h1><i class="fas fa-id-card"></i> Perfil del Estudiante</h1>
<a href="dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Volver</a>

<div class="row">
    <div class="col-md-6">
        <div class="card-dashboard">
            <h5>📋 Datos Personales</h5>
            <p><strong>Nombre:</strong> <?php echo $estudiante['nombre'] . ' ' . $estudiante['apellido']; ?></p>
            <p><strong>Código:</strong> <?php echo $estudiante['codigo_estudiante']; ?></p>
            <p><strong>Carnet:</strong> <span class="badge bg-info"><?php echo $estudiante['carnet_number']; ?></span></p>
            <p><strong>Curso:</strong> <?php echo $estudiante['curso']; ?></p>
            <p><strong>Sección:</strong> <?php echo $estudiante['seccion']; ?></p>
            <p><strong>Jornada:</strong> <?php echo $estudiante['jornada']; ?></p>
            <p><strong>Fecha de Nacimiento:</strong> <?php echo date('d/m/Y', strtotime($estudiante['fecha_nac'])); ?></p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-dashboard">
            <h5>📊 Código QR</h5>
            <div style="background: white; padding: 20px; border-radius: 12px; display: inline-block;">
                <img src="<?php echo generarQR($estudiante['codigo_qr']); ?>" alt="QR" style="width:150px;">
            </div>
            <p class="text-secondary mt-2">QR: <?php echo $estudiante['codigo_qr']; ?></p>
        </div>
        
        <div class="card-dashboard mt-3">
            <h5>📊 Resumen de Asistencia</h5>
            <div class="table-responsive">
                <table class="table table-dark table-sm">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM asistencia WHERE estudiante_id = {$estudiante['id']} ORDER BY fecha_hora DESC LIMIT 10";
                        $result_asistencia = mysqli_query($conn, $sql);
                        if (mysqli_num_rows($result_asistencia) > 0) {
                            while ($row = mysqli_fetch_assoc($result_asistencia)) {
                                $icon = $row['tipo'] == 'entrada' ? '🟢' : '🔴';
                                echo "<tr>
                                    <td>" . date('d/m/Y', strtotime($row['fecha_hora'])) . "</td>
                                    <td>$icon {$row['tipo']}</td>
                                    <td>" . date('H:i', strtotime($row['fecha_hora'])) . "</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' class='text-center text-secondary'>Sin registros</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>