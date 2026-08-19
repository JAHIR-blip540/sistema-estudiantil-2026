<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
require_once '../funciones.php';
requireRole('admin');

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$error = '';
$success = '';

// Verificar que el usuario existe
$sql = "SELECT * FROM usuarios WHERE id = $id AND rol IN ('guardia', 'consejero', 'padre')";
$result = mysqli_query($conn, $sql);
$usuario = mysqli_fetch_assoc($result);

if (!$usuario) {
    header('Location: usuarios.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nueva_password = $_POST['nueva_password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';
    
    if (!validarPasswordSegura($nueva_password)) {
        $error = '❌ La contraseña debe tener al menos 8 caracteres e incluir letras y números';
    } elseif ($nueva_password != $confirmar_password) {
        $error = '❌ Las contraseñas no coinciden';
    } else {
        $password_hashed = md5($nueva_password);
        $sql_update = "UPDATE usuarios SET password = '$password_hashed' WHERE id = $id";
        
        if (mysqli_query($conn, $sql_update)) {
            $success = '✅ Contraseña actualizada correctamente';
        } else {
            $error = '❌ Error al actualizar: ' . mysqli_error($conn);
        }
    }
}

include '../header.php';
?>

<h1><i class="fas fa-key"></i> Cambiar Contraseña</h1>
<p class="text-secondary">Usuario: <strong><?php echo $usuario['nombre']; ?></strong> (<?php echo $usuario['rol']; ?>)</p>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card-dashboard">
            <form method="POST">
                <div class="mb-3">
                    <label>Nueva Contraseña</label>
                    <input type="password" name="nueva_password" class="form-control" minlength="8" required>
                    <small class="text-secondary">Mínimo 8 caracteres, con letras y números</small>
                </div>
                <div class="mb-3">
                    <label>Confirmar Contraseña</label>
                    <input type="password" name="confirmar_password" class="form-control" minlength="8" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Contraseña
                    </button>
                    <a href="usuarios.php" class="btn btn-secondary"><i class="fas fa-xmark"></i> Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>