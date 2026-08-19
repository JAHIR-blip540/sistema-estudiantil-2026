<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
require_once '../funciones.php';
requireRole('padre');

$error = '';
$success = '';
$userId = (int)($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual = $_POST['password_actual'] ?? '';
    $nueva = $_POST['nueva_password'] ?? '';
    $confirmar = $_POST['confirmar_password'] ?? '';

    if ($actual === '' || $nueva === '' || $confirmar === '') {
        $error = 'Completa todos los campos.';
    } elseif (!validarPasswordSegura($nueva)) {
        $error = 'La nueva contraseña debe tener al menos 8 caracteres e incluir letras y números.';
    } elseif ($nueva !== $confirmar) {
        $error = 'La nueva contraseña y su confirmación no coinciden.';
    } elseif ($actual === $nueva) {
        $error = 'La nueva contraseña debe ser diferente de la contraseña actual.';
    } else {
        $actualHash = md5($actual);
        $stmt = mysqli_prepare($conn, "SELECT id FROM usuarios WHERE id = ? AND rol = 'padre' AND password = ? AND activo = 1 LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'is', $userId, $actualHash);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if (!$resultado || mysqli_num_rows($resultado) !== 1) {
            $error = 'La contraseña actual no es correcta.';
        } else {
            $nuevoHash = md5($nueva);
            $update = mysqli_prepare($conn, "UPDATE usuarios SET password = ? WHERE id = ? AND rol = 'padre'");
            mysqli_stmt_bind_param($update, 'si', $nuevoHash, $userId);
            if (mysqli_stmt_execute($update)) {
                $success = 'Contraseña actualizada correctamente.';
            } else {
                $error = 'No se pudo actualizar la contraseña. Inténtalo nuevamente.';
            }
            mysqli_stmt_close($update);
        }
        mysqli_stmt_close($stmt);
    }
}

include '../header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
    <div>
        <h1><i class="fas fa-key"></i> Cambiar mi contraseña</h1>
        <p class="text-secondary mb-0">Actualiza de forma segura la contraseña de tu cuenta de padre.</p>
    </div>
    <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al panel</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-circle-check"></i> <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">
        <div class="card-dashboard">
            <form method="POST" autocomplete="off">
                <div class="mb-3">
                    <label for="password_actual">Contraseña actual</label>
                    <div class="password-field">
                        <input id="password_actual" type="password" name="password_actual" class="form-control" autocomplete="current-password" required>
                        <button class="password-toggle" type="button" data-target="password_actual" aria-label="Mostrar u ocultar contraseña"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="nueva_password">Nueva contraseña</label>
                    <div class="password-field">
                        <input id="nueva_password" type="password" name="nueva_password" class="form-control" minlength="8" autocomplete="new-password" required>
                        <button class="password-toggle" type="button" data-target="nueva_password" aria-label="Mostrar u ocultar contraseña"><i class="fas fa-eye"></i></button>
                    </div>
                    <small class="text-secondary">Mínimo 8 caracteres e incluye letras y números.</small>
                </div>
                <div class="mb-4">
                    <label for="confirmar_password">Confirmar nueva contraseña</label>
                    <div class="password-field">
                        <input id="confirmar_password" type="password" name="confirmar_password" class="form-control" minlength="8" autocomplete="new-password" required>
                        <button class="password-toggle" type="button" data-target="confirmar_password" aria-label="Mostrar u ocultar contraseña"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Guardar nueva contraseña</button>
                    <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-xmark"></i> Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.password-toggle').forEach(function (button) {
    button.addEventListener('click', function () {
        const input = document.getElementById(button.dataset.target);
        const icon = button.querySelector('i');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
    });
});
</script>

<?php include '../footer.php'; ?>
