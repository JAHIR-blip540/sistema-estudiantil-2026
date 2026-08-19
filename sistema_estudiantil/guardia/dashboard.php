<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
requireRole('guardia');
include '../header.php';
?>

<h1><i class="fas fa-shield-alt"></i> Panel de Guardia</h1>
<p class="text-secondary">Bienvenido, <?php echo $_SESSION['user_name']; ?></p>
<p class="text-secondary">📍 Ubicación: <?php echo $_SESSION['ubicacion']; ?></p>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card-dashboard" style="border: 2px solid #00BCD4;">
            <div class="text-center">
                <i class="fas fa-qrcode fa-3x" style="color: #00BCD4;"></i>
                <h3 class="mt-2">Escáner QR</h3>
                <p class="text-secondary">Apunta la cámara al código QR del estudiante</p>
                <a href="scanner_real.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-camera"></i> 📷 Abrir Escáner
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card-dashboard">
            <h5>📊 Asistencias de Hoy</h5>
            <?php
            // SOLO ENTRADAS
            $sql = "SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE() AND tipo = 'entrada'";
            $result = mysqli_query($conn, $sql);
            $total = mysqli_fetch_assoc($result)['total'];
            ?>
            <h3 class="stat-number"><?php echo $total; ?></h3>
            <p class="stat-label">Entradas registradas hoy</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard">
            <h5>🟢 Entradas Hoy</h5>
            <?php
            $sql = "SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE() AND tipo = 'entrada'";
            $result = mysqli_query($conn, $sql);
            $entradas = mysqli_fetch_assoc($result)['total'];
            ?>
            <h3 class="stat-number" style="color: #00E676;"><?php echo $entradas; ?></h3>
            <p class="stat-label">Entradas registradas</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard">
            <h5>🔴 Salidas Hoy</h5>
            <?php
            $sql = "SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE() AND tipo = 'salida'";
            $result = mysqli_query($conn, $sql);
            $salidas = mysqli_fetch_assoc($result)['total'];
            ?>
            <h3 class="stat-number" style="color: #FF1744;"><?php echo $salidas; ?></h3>
            <p class="stat-label">Salidas registradas</p>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card-dashboard">
            <h5>📋 Últimos Registros</h5>
            <div class="table-responsive">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Tipo</th>
                            <th>Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM asistencia ORDER BY fecha_hora DESC LIMIT 10";
                        $result = mysqli_query($conn, $sql);
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $tipo = $row['tipo'] == 'entrada' ? '🟢 Entrada' : '🔴 Salida';
                                echo "<tr>
                                    <td>{$row['estudiante_nombre']}</td>
                                    <td>$tipo</td>
                                    <td>" . date('H:i:s', strtotime($row['fecha_hora'])) . "</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' class='text-center text-secondary'>No hay registros</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>