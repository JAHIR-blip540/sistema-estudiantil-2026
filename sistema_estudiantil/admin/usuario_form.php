<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
require_once '../funciones.php';
requireRole('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editando = $id > 0;
$row = [];
$error = '';

if ($editando) {
    $stmt = mysqli_prepare($conn, 'SELECT * FROM usuarios WHERE id=? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? (mysqli_fetch_assoc($res) ?: []) : [];
    mysqli_stmt_close($stmt);
    if (!$row || !in_array($row['rol'], ['guardia','consejero'], true)) { header('Location: usuarios.php'); exit(); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $rol = trim($_POST['rol'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $row = compact('nombre','email','rol','ubicacion');

    if (strlen($nombre) < 3 || strlen($nombre) > 100) {
        $error = 'El nombre debe tener entre 3 y 100 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un correo electrónico válido.';
    } elseif (!in_array($rol, ['guardia','consejero'], true)) {
        $error = 'Solo se pueden crear usuarios Guardia o Consejero.';
    } elseif ($rol === 'guardia' && ($ubicacion === '' || strlen($ubicacion) > 100)) {
        $error = 'Debes indicar una ubicación válida para el guardia.';
    } else {
        if ($rol !== 'guardia') $ubicacion = '';
        $stmt = mysqli_prepare($conn, 'SELECT id FROM usuarios WHERE email=? AND id<>? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 'si', $email, $id);
        mysqli_stmt_execute($stmt);
        $dup = mysqli_stmt_get_result($stmt);
        $existe = $dup && mysqli_num_rows($dup)>0;
        mysqli_stmt_close($stmt);

        if ($existe) {
            $error = 'Ya existe otro usuario con ese correo electrónico.';
        } elseif ($editando) {
            $stmt = mysqli_prepare($conn, 'UPDATE usuarios SET nombre=?,email=?,rol=?,ubicacion=? WHERE id=?');
            mysqli_stmt_bind_param($stmt, 'ssssi', $nombre,$email,$rol,$ubicacion,$id);
            if (mysqli_stmt_execute($stmt)) { mysqli_stmt_close($stmt); header('Location: usuarios.php'); exit(); }
            $error = 'No se pudo actualizar el usuario.'; mysqli_stmt_close($stmt);
        } else {
            $password = md5('12345');
            $stmt = mysqli_prepare($conn, 'INSERT INTO usuarios (nombre,email,password,rol,ubicacion,activo) VALUES (?,?,?,?,?,1)');
            mysqli_stmt_bind_param($stmt, 'sssss', $nombre,$email,$password,$rol,$ubicacion);
            if (mysqli_stmt_execute($stmt)) { mysqli_stmt_close($stmt); header('Location: usuarios.php'); exit(); }
            $error = 'No se pudo crear el usuario.'; mysqli_stmt_close($stmt);
        }
    }
}
include '../header.php';
function uval($row,$k){return htmlspecialchars($row[$k]??'',ENT_QUOTES,'UTF-8');}
?>
<h1><?php echo $editando ? 'Editar' : 'Nuevo'; ?> Usuario</h1>
<?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="POST">
<div class="row">
<div class="col-md-6"><div class="mb-3"><label>Nombre</label><input type="text" name="nombre" maxlength="100" class="form-control" value="<?php echo uval($row,'nombre'); ?>" required></div></div>
<div class="col-md-6"><div class="mb-3"><label>Email</label><input type="email" name="email" maxlength="100" class="form-control" value="<?php echo uval($row,'email'); ?>" required><small class="text-secondary">No se permite repetir el mismo correo.</small></div></div>
<div class="col-md-6"><div class="mb-3"><label>Rol</label><select name="rol" id="rol" class="form-control" required><option value="guardia" <?php echo (($row['rol']??'')==='guardia')?'selected':''; ?>>Guardia</option><option value="consejero" <?php echo (($row['rol']??'')==='consejero')?'selected':''; ?>>Consejero</option></select></div></div>
<div class="col-md-6"><div class="mb-3"><label>Ubicación (obligatoria para Guardia)</label><input type="text" id="ubicacion" name="ubicacion" maxlength="100" class="form-control" value="<?php echo uval($row,'ubicacion'); ?>" placeholder="Ej: Puerta Principal"></div></div>
</div>
<?php if (!$editando): ?><div class="alert alert-info"><i class="fas fa-info-circle"></i> Contraseña inicial: <strong>12345</strong>. Se recomienda cambiarla después del primer acceso.</div><?php endif; ?>
<div class="form-actions"><button class="btn btn-primary" type="submit"><i class="fas fa-floppy-disk"></i> Guardar</button><a class="btn btn-secondary" href="usuarios.php"><i class="fas fa-xmark"></i> Cancelar</a></div>
</form>
<script>
const rol=document.getElementById('rol'), ubicacion=document.getElementById('ubicacion');
function ajustar(){ubicacion.required=rol.value==='guardia'; if(rol.value!=='guardia') ubicacion.value='';}
rol.addEventListener('change',ajustar); ajustar();
</script>
<?php include '../footer.php'; ?>
