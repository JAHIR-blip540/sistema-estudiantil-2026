<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
requireRole('consejero');
include '../header.php';

// Estadísticas - SOLO ENTRADAS como asistencias
$sql = "SELECT COUNT(*) as total FROM estudiantes WHERE activo = 1";
$result = mysqli_query($conn, $sql);
$total_estudiantes = mysqli_fetch_assoc($result)['total'];

// SOLO ENTRADAS
$sql = "SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE() AND tipo = 'entrada'";
$result = mysqli_query($conn, $sql);
$entradas = mysqli_fetch_assoc($result)['total'];

// Salidas por separado
$sql = "SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE() AND tipo = 'salida'";
$result = mysqli_query($conn, $sql);
$salidas = mysqli_fetch_assoc($result)['total'];

$ausentes = $total_estudiantes - $entradas;
?>

<h1><i class="fas fa-chart-line"></i> Panel de Consejero</h1>
<p class="text-secondary">Bienvenido, <?php echo $_SESSION['user_name']; ?></p>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card-dashboard">
            <i class="fas fa-users fa-2x" style="color: #00BCD4;"></i>
            <h3 class="stat-number"><?php echo $total_estudiantes; ?></h3>
            <p class="stat-label">Total Estudiantes</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-dashboard">
            <i class="fas fa-check-circle fa-2x" style="color: #00E676;"></i>
            <h3 class="stat-number"><?php echo $entradas; ?></h3>
            <p class="stat-label">Presentes Hoy (Entradas)</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-dashboard">
            <i class="fas fa-times-circle fa-2x" style="color: #FF1744;"></i>
            <h3 class="stat-number"><?php echo $ausentes; ?></h3>
            <p class="stat-label">Ausentes Hoy</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-dashboard">
            <i class="fas fa-sign-out-alt fa-2x" style="color: #FFC107;"></i>
            <h3 class="stat-number"><?php echo $salidas; ?></h3>
            <p class="stat-label">Salidas Hoy</p>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card-dashboard">
            <h5><i class="fas fa-bell"></i> Alertas Recientes</h5>
            <div class="table-responsive">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Mensaje</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM alertas ORDER BY creado DESC LIMIT 10";
                        $result = mysqli_query($conn, $sql);
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>
                                    <td>{$row['estudiante_nombre']}</td>
                                    <td>{$row['mensaje']}</td>
                                    <td>" . date('d/m/Y H:i', strtotime($row['creado'])) . "</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' class='text-center text-secondary'>No hay alertas</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>