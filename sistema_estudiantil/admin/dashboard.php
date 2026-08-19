<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
requireRole('admin');
include '../header.php';
?>

<h1><i class="fas fa-tachometer-alt"></i> Panel de Administración</h1>
<p class="text-secondary">Bienvenido, <?php echo $_SESSION['user_name']; ?></p>

<div class="row mt-4">
    <?php
    $sql = "SELECT COUNT(*) as total FROM estudiantes";
    $result = mysqli_query($conn, $sql);
    $total_estudiantes = mysqli_fetch_assoc($result)['total'];
    ?>
    <div class="col-md-3">
        <div class="card-dashboard">
            <i class="fas fa-users fa-2x" style="color: #00BCD4;"></i>
            <h3 class="stat-number"><?php echo $total_estudiantes; ?></h3>
            <p class="stat-label">Total Estudiantes</p>
        </div>
    </div>
    
    <?php
    // SOLO ENTRADAS
    $sql = "SELECT COUNT(*) as total FROM asistencia WHERE DATE(fecha_hora) = CURDATE() AND tipo = 'entrada'";
    $result = mysqli_query($conn, $sql);
    $asistencias = mysqli_fetch_assoc($result)['total'];
    ?>
    <div class="col-md-3">
        <div class="card-dashboard">
            <i class="fas fa-check-circle fa-2x" style="color: #00E676;"></i>
            <h3 class="stat-number"><?php echo $asistencias; ?></h3>
            <p class="stat-label">Asistencias Hoy (Entradas)</p>
        </div>
    </div>
    
    <?php
    $sql = "SELECT COUNT(*) as total FROM alertas WHERE leido = 0";
    $result = mysqli_query($conn, $sql);
    $alertas = mysqli_fetch_assoc($result)['total'];
    ?>
    <div class="col-md-3">
        <div class="card-dashboard">
            <i class="fas fa-bell fa-2x" style="color: #FFC107;"></i>
            <h3 class="stat-number"><?php echo $alertas; ?></h3>
            <p class="stat-label">Alertas Pendientes</p>
        </div>
    </div>
    
    <?php
    require_once __DIR__ . '/../presencia.php';
    // Cuenta únicamente al personal que mantiene una sesión activa:
    // Administrador, Consejero y Guardia.
    $personal = presenciaTotalActivos();
    ?>
    <div class="col-md-3">
        <div class="card-dashboard">
            <i class="fas fa-user-shield fa-2x" style="color: #2979FF;"></i>
            <h3 class="stat-number" id="personal-activo-count"><?php echo $personal; ?></h3>
            <p class="stat-label">Personal Activo</p>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card-dashboard">
            <h5>⚡ Acciones Rápidas</h5>
            <div class="d-grid gap-2 mt-3">
                <a href="estudiantes.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Gestionar Estudiantes
                </a>
                <a href="usuarios.php" class="btn btn-secondary">
                    <i class="fas fa-users-cog"></i> Gestionar Personal
                </a>
                <a href="reportes.php" class="btn btn-info">
                    <i class="fas fa-file-alt"></i> Reportes
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-dashboard">
            <h5>📋 Últimos Registros</h5>
            <ul class="list-group list-group-flush bg-transparent">
                <?php
                $sql = "SELECT * FROM estudiantes ORDER BY creado DESC LIMIT 5";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<li class='list-group-item bg-transparent text-light'>
                        <i class='fas fa-user'></i> {$row['nombre']} {$row['apellido']}
                        <small class='text-secondary'> - {$row['codigo_estudiante']}</small>
                    </li>";
                }
                ?>
            </ul>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>