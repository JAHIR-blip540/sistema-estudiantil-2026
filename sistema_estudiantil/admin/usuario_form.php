<?php
require_once '../db.php'; require_once '../funciones.php'; requireRole('admin');
$id=(int)($_GET['id']??0); $editando=$id>0; $row=[]; $error='';
$tipoInicial=(($_GET['tipo']??'')==='padre')?'padre':'guardia';
if($editando){$stmt=mysqli_prepare($conn,'SELECT * FROM usuarios WHERE id=? LIMIT 1');mysqli_stmt_bind_param($stmt,'i',$id);mysqli_stmt_execute($stmt);$res=mysqli_stmt_get_result($stmt);$row=$res?(mysqli_fetch_assoc($res)?:[]):[];mysqli_stmt_close($stmt);if(!$row || $row['rol']==='admin'){header('Location: usuarios.php');exit();}}
if($_SERVER['REQUEST_METHOD']==='POST'){
 $nombre=trim($_POST['nombre']??'');$email=strtolower(trim($_POST['email']??''));$rol=trim($_POST['rol']??'');$ubicacion=trim($_POST['ubicacion']??'');$telefono=normalizarTelefono($_POST['telefono']??'');$row=compact('nombre','email','rol','ubicacion','telefono');
 if(strlen($nombre)<3||strlen($nombre)>100)$error='El nombre debe tener entre 3 y 100 caracteres.';
 elseif(!filter_var($email,FILTER_VALIDATE_EMAIL))$error='Ingresa un correo electrónico válido.';
 elseif(!in_array($rol,['guardia','consejero','padre'],true))$error='Selecciona un rol válido.';
 elseif($rol==='guardia'&&($ubicacion===''||strlen($ubicacion)>100))$error='Debes indicar una ubicación válida para el guardia.';
 elseif($rol==='padre'&&!validarTelefonoHN($telefono))$error='El teléfono del padre debe contener exactamente 8 dígitos.';
 else {
  if($rol!=='guardia')$ubicacion=''; if($rol!=='padre')$telefono='';
  $stmt=mysqli_prepare($conn,'SELECT id FROM usuarios WHERE (email=? OR (?<>\'\' AND telefono=?)) AND id<>? LIMIT 1');mysqli_stmt_bind_param($stmt,'sssi',$email,$telefono,$telefono,$id);mysqli_stmt_execute($stmt);$dup=mysqli_stmt_get_result($stmt);$existe=$dup&&mysqli_num_rows($dup)>0;mysqli_stmt_close($stmt);
  if($existe)$error='Ya existe otro usuario con ese correo o teléfono.';
  elseif($editando){$stmt=mysqli_prepare($conn,'UPDATE usuarios SET nombre=?,email=?,rol=?,ubicacion=?,telefono=? WHERE id=?');mysqli_stmt_bind_param($stmt,'sssssi',$nombre,$email,$rol,$ubicacion,$telefono,$id);if(mysqli_stmt_execute($stmt)){mysqli_stmt_close($stmt);header('Location: usuarios.php');exit();}$error='No se pudo actualizar el usuario.';mysqli_stmt_close($stmt);}
  else {$password=md5('Padre123');$stmt=mysqli_prepare($conn,'INSERT INTO usuarios (nombre,email,password,rol,ubicacion,telefono,activo) VALUES (?,?,?,?,?,?,1)');mysqli_stmt_bind_param($stmt,'ssssss',$nombre,$email,$password,$rol,$ubicacion,$telefono);if(mysqli_stmt_execute($stmt)){mysqli_stmt_close($stmt);header('Location: usuarios.php');exit();}$error='No se pudo crear el usuario.';mysqli_stmt_close($stmt);}
 }
}
include '../header.php'; function uval($row,$k){return htmlspecialchars($row[$k]??'',ENT_QUOTES,'UTF-8');}$rolActual=$row['rol']??$tipoInicial;
?>
<div class="page-heading-actions"><h1><?php echo $editando?'Editar':'Nuevo'; ?> Usuario</h1><a class="btn btn-secondary" href="usuarios.php"><i class="fas fa-arrow-left"></i> Volver</a></div>
<?php if($error):?><div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?php echo htmlspecialchars($error);?></div><?php endif;?>
<form method="POST"><div class="row">
<div class="col-md-6"><div class="mb-3"><label>Nombre</label><input type="text" name="nombre" maxlength="100" class="form-control" value="<?php echo uval($row,'nombre');?>" required></div></div>
<div class="col-md-6"><div class="mb-3"><label>Email</label><input type="email" name="email" maxlength="100" class="form-control" value="<?php echo uval($row,'email');?>" required></div></div>
<div class="col-md-6"><div class="mb-3"><label>Rol</label><select name="rol" id="rol" class="form-control" required><option value="guardia" <?php echo $rolActual==='guardia'?'selected':'';?>>Guardia</option><option value="consejero" <?php echo $rolActual==='consejero'?'selected':'';?>>Consejero</option><option value="padre" <?php echo $rolActual==='padre'?'selected':'';?>>Padre</option></select></div></div>
<div class="col-md-6" id="ubicacionBox"><div class="mb-3"><label>Ubicación (Guardia)</label><input type="text" id="ubicacion" name="ubicacion" maxlength="100" class="form-control" value="<?php echo uval($row,'ubicacion');?>" placeholder="Ej: Puerta Principal"></div></div>
<div class="col-md-6" id="telefonoBox"><div class="mb-3"><label>Teléfono del Padre</label><input type="tel" id="telefono" name="telefono" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" class="form-control" value="<?php echo uval($row,'telefono');?>" placeholder="8 dígitos"><small class="text-secondary">Debe coincidir con el teléfono registrado en el estudiante para vincularlos.</small></div></div>
</div>
<?php if(!$editando):?><div class="alert alert-info"><i class="fas fa-info-circle"></i> Contraseña inicial: <strong>Padre123</strong>. El usuario debe cambiarla después del primer acceso.</div><?php endif;?>
<div class="form-actions"><button class="btn btn-primary" type="submit"><i class="fas fa-floppy-disk"></i> Guardar</button><a class="btn btn-secondary" href="usuarios.php"><i class="fas fa-xmark"></i> Cancelar</a></div></form>
<script>const rol=document.getElementById('rol'),ub=document.getElementById('ubicacion'),tel=document.getElementById('telefono'),ubBox=document.getElementById('ubicacionBox'),telBox=document.getElementById('telefonoBox');function ajustar(){const g=rol.value==='guardia',p=rol.value==='padre';ub.required=g;tel.required=p;ubBox.style.display=g?'block':'none';telBox.style.display=p?'block':'none';if(!g)ub.value='';if(!p)tel.value='';}rol.addEventListener('change',ajustar);ajustar();</script>
<?php include '../footer.php'; ?>
