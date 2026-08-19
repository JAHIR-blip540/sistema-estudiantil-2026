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
    $stmt = mysqli_prepare($conn, 'SELECT * FROM estudiantes WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? (mysqli_fetch_assoc($res) ?: []) : [];
    mysqli_stmt_close($stmt);
    if (!$row) { header('Location: estudiantes.php'); exit(); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dni = preg_replace('/[\s-]+/', '', trim($_POST['dni'] ?? ''));
    $curso = trim($_POST['curso'] ?? '');
    $seccion = strtoupper(trim($_POST['seccion'] ?? ''));
    $jornada = trim($_POST['jornada'] ?? '');
    $fecha_nac = trim($_POST['fecha_nac'] ?? '');
    $telefono_padre = normalizarTelefono($_POST['telefono_padre'] ?? '');
    $email_padre = strtolower(trim($_POST['email_padre'] ?? ''));
    $edad = calcularEdad($fecha_nac);

    $row = compact('nombre','apellido','dni','curso','seccion','jornada','fecha_nac','telefono_padre','email_padre');

    if (!validarNombrePersona($nombre) || !validarNombrePersona($apellido)) {
        $error = 'Nombre y apellido deben contener letras y tener entre 2 y 50 caracteres.';
    } elseif (!validarDNI($dni)) {
        $error = 'El DNI/identificación debe contener entre 8 y 20 caracteres alfanuméricos.';
    } elseif ($edad < 0) {
        $error = 'La fecha de nacimiento no es válida ni puede estar en el futuro.';
    } elseif ($edad < 11) {
        $error = 'No se puede registrar al estudiante: debe tener al menos 11 años cumplidos.';
    } elseif (!in_array($curso, cursosPermitidos(), true)) {
        $error = 'El grado o carrera seleccionado no es válido.';
    } elseif (!in_array($seccion, ['A','B','C','D'], true)) {
        $error = 'La sección seleccionada no es válida.';
    } elseif (!in_array($jornada, ['Matutina','Vespertina'], true)) {
        $error = 'La jornada seleccionada no es válida.';
    } elseif (!validarTelefonoHN($telefono_padre)) {
        $error = 'El teléfono del padre debe contener exactamente 8 dígitos.';
    } elseif ($email_padre !== '' && !filter_var($email_padre, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo del padre no tiene un formato válido.';
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT id FROM estudiantes WHERE dni = ? AND id <> ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 'si', $dni, $id);
        mysqli_stmt_execute($stmt);
        $dup = mysqli_stmt_get_result($stmt);
        $dniExiste = $dup && mysqli_num_rows($dup) > 0;
        mysqli_stmt_close($stmt);

        if ($dniExiste) {
            $error = 'Ya existe otro estudiante registrado con ese DNI/identificación.';
        } elseif ($editando) {
            $stmt = mysqli_prepare($conn, 'UPDATE estudiantes SET nombre=?, apellido=?, dni=?, curso=?, seccion=?, jornada=?, fecha_nac=?, telefono_padre=?, email_padre=? WHERE id=?');
            mysqli_stmt_bind_param($stmt, 'sssssssssi', $nombre,$apellido,$dni,$curso,$seccion,$jornada,$fecha_nac,$telefono_padre,$email_padre,$id);
            if (mysqli_stmt_execute($stmt)) { mysqli_stmt_close($stmt); header('Location: estudiantes.php?msg=actualizado'); exit(); }
            $error = 'No se pudo actualizar el estudiante.'; mysqli_stmt_close($stmt);
        } else {
            $codigo = generarCodigoEstudiante($conn);
            $carnet = generarCarnetNumber($conn);
            $codigo_qr = generarCodigoQR($conn);
            $stmt = mysqli_prepare($conn, 'INSERT INTO estudiantes (nombre,apellido,dni,codigo_estudiante,curso,seccion,jornada,fecha_nac,telefono_padre,email_padre,codigo_qr,carnet_number,activo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1)');
            mysqli_stmt_bind_param($stmt, 'ssssssssssss', $nombre,$apellido,$dni,$codigo,$curso,$seccion,$jornada,$fecha_nac,$telefono_padre,$email_padre,$codigo_qr,$carnet);
            if (mysqli_stmt_execute($stmt)) { mysqli_stmt_close($stmt); header('Location: estudiantes.php?msg=creado'); exit(); }
            $error = 'No se pudo registrar el estudiante.'; mysqli_stmt_close($stmt);
        }
    }
}

include '../header.php';
function val($row,$key){ return htmlspecialchars($row[$key] ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<h1><?php echo $editando ? 'Editar' : 'Nuevo'; ?> Estudiante</h1>
<p class="text-secondary">Validaciones activas: edad mínima 11 años, identificación única, teléfono de 8 dígitos y datos académicos permitidos.</p>
<?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="POST" novalidate>
<div class="row">
<div class="col-md-6"><div class="mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control" minlength="2" maxlength="50" value="<?php echo val($row,'nombre'); ?>" required></div></div>
<div class="col-md-6"><div class="mb-3"><label>Apellido</label><input type="text" name="apellido" class="form-control" minlength="2" maxlength="50" value="<?php echo val($row,'apellido'); ?>" required></div></div>
<div class="col-md-6"><div class="mb-3"><label>DNI / Identificación</label><input type="text" name="dni" class="form-control" minlength="8" maxlength="20" value="<?php echo val($row,'dni'); ?>" required><small class="text-secondary">Debe ser único para cada estudiante.</small></div></div>
<div class="col-md-6"><div class="mb-3"><label>Fecha de Nacimiento</label><input type="date" name="fecha_nac" class="form-control" max="<?php echo date('Y-m-d', strtotime('-11 years')); ?>" value="<?php echo val($row,'fecha_nac'); ?>" required><small class="text-secondary">Edad mínima: 11 años cumplidos.</small></div></div>
<div class="col-md-4"><div class="mb-3"><label>Curso</label><select name="curso" class="form-control" required>
<option value="">Seleccionar grado y carrera...</option>
<optgroup label="Educación Básica">
<?php foreach(['7mo','8vo','9no'] as $c): ?><option value="<?php echo $c; ?>" <?php echo (($row['curso']??'')===$c)?'selected':''; ?>><?php echo $c; ?></option><?php endforeach; ?>
</optgroup>
<optgroup label="BTP en Informática"><?php foreach(['10° Informática','11° Informática','12° Informática'] as $c): ?><option value="<?php echo $c; ?>" <?php echo (($row['curso']??'')===$c)?'selected':''; ?>><?php echo $c; ?></option><?php endforeach; ?></optgroup>
<optgroup label="BTP en Agronomía"><?php foreach(['10° Agronomía','11° Agronomía','12° Agronomía'] as $c): ?><option value="<?php echo $c; ?>" <?php echo (($row['curso']??'')===$c)?'selected':''; ?>><?php echo $c; ?></option><?php endforeach; ?></optgroup>
<optgroup label="BTP en Electromecánica"><?php foreach(['10° Electromecánica','11° Electromecánica','12° Electromecánica'] as $c): ?><option value="<?php echo $c; ?>" <?php echo (($row['curso']??'')===$c)?'selected':''; ?>><?php echo $c; ?></option><?php endforeach; ?></optgroup>
</select></div></div>
<div class="col-md-4"><div class="mb-3"><label>Sección</label><select name="seccion" class="form-control" required><option value="">Seleccionar...</option><?php foreach(['A','B','C','D'] as $s): ?><option value="<?php echo $s; ?>" <?php echo (($row['seccion']??'')===$s)?'selected':''; ?>><?php echo $s; ?></option><?php endforeach; ?></select></div></div>
<div class="col-md-4"><div class="mb-3"><label>Jornada</label><select name="jornada" class="form-control" required><?php foreach(['Matutina','Vespertina'] as $j): ?><option value="<?php echo $j; ?>" <?php echo (($row['jornada']??'Matutina')===$j)?'selected':''; ?>><?php echo $j; ?></option><?php endforeach; ?></select></div></div>
<div class="col-md-6"><div class="mb-3"><label>Teléfono del Padre</label><input type="tel" name="telefono_padre" class="form-control" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" value="<?php echo val($row,'telefono_padre'); ?>" required><small class="text-secondary">8 dígitos. Se usa para vincular la cuenta del padre.</small></div></div>
<div class="col-md-6"><div class="mb-3"><label>Email del Padre</label><input type="email" name="email_padre" maxlength="100" class="form-control" value="<?php echo val($row,'email_padre'); ?>"></div></div>
</div>
<div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Guardar</button><a href="estudiantes.php" class="btn btn-secondary"><i class="fas fa-xmark"></i> Cancelar</a></div>
</form>
<?php include '../footer.php'; ?>
