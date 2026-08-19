<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
require_once '../funciones.php';
requireRole('padre');

include '../header.php';

// Obtener estudiantes asociados al padre
$telefono = '';
if (isset($_SESSION['user_phone'])) {
    $telefono = $_SESSION['user_phone'];
} else {
    $sql = "SELECT telefono FROM usuarios WHERE id = {$_SESSION['user_id']}";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $telefono = isset($row['telefono']) ? $row['telefono'] : '';
    $_SESSION['user_phone'] = $telefono;
}

// Buscar estudiantes con ese teléfono
$estudiantes = array();
if (!empty($telefono)) {
    $sql = "SELECT * FROM estudiantes WHERE telefono_padre = '$telefono' AND activo = 1";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $estudiantes[] = $row;
    }
}
?>

<h1><i class="fas fa-home"></i> Panel de Padre</h1>
<p class="text-secondary">Bienvenido, <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''; ?></p>

<div class="account-toolbar mb-3">
    <div>
        <strong><i class="fas fa-shield-halved"></i> Seguridad de mi cuenta</strong>
        <div class="text-secondary small">Puedes cambiar tu contraseña cuando lo necesites.</div>
    </div>
    <a href="cambiar_password.php" class="btn btn-warning"><i class="fas fa-key"></i> Cambiar mi contraseña</a>
</div>

<?php if (empty($estudiantes)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No tienes estudiantes asociados. Contacta al administrador.
    </div>
<?php else: ?>
    <?php foreach ($estudiantes as $estudiante): ?>
        <div class="card-dashboard mt-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3><i class="fas fa-user-graduate"></i> <?php echo $estudiante['nombre'] . ' ' . $estudiante['apellido']; ?></h3>
                    <p class="text-secondary">
                        Código: <?php echo $estudiante['codigo_estudiante']; ?> | 
                        Carnet: <?php echo $estudiante['carnet_number']; ?>
                    </p>
                    <p class="text-secondary">
                        <?php echo $estudiante['curso'] . ' - ' . $estudiante['seccion']; ?> | 
                        Jornada: <?php echo $estudiante['jornada']; ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="perfil.php?id=<?php echo $estudiante['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Ver Perfil
                    </a>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-6">
                    <h5>📊 Asistencia de Hoy</h5>
                    <?php
                    $sql = "SELECT * FROM asistencia WHERE estudiante_id = {$estudiante['id']} AND fecha = CURDATE()";
                    $result_asistencia = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result_asistencia) > 0) {
                        while ($row = mysqli_fetch_assoc($result_asistencia)) {
                            $icon = $row['tipo'] == 'entrada' ? '🟢' : '🔴';
                            echo "<p>$icon {$row['tipo']} - " . date('H:i', strtotime($row['fecha_hora'])) . "</p>";
                        }
                    } else {
                        echo "<p class='text-secondary'>Sin registros hoy</p>";
                    }
                    ?>
                </div>
                <div class="col-md-6">
                    <h5>🔔 Últimas Alertas</h5>
                    <?php
                    $sql = "SELECT * FROM alertas WHERE estudiante_id = {$estudiante['id']} ORDER BY creado DESC LIMIT 3";
                    $result_alerta = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result_alerta) > 0) {
                        while ($row = mysqli_fetch_assoc($result_alerta)) {
                            echo "<p class='text-secondary'><small>📌 {$row['mensaje']}</small></p>";
                        }
                    } else {
                        echo "<p class='text-secondary'>Sin alertas</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../footer.php'; ?>