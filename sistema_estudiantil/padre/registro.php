<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
require_once '../funciones.php';

$error=''; $success='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nombre=trim($_POST['nombre']??'');
    $email=strtolower(trim($_POST['email']??''));
    $telefono=normalizarTelefono($_POST['telefono']??'');
    $password=$_POST['password']??'';
    $confirm=$_POST['confirm_password']??'';

    if (strlen($nombre)<3 || strlen($nombre)>100) {
        $error='❌ Ingresa un nombre válido.';
    } elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
        $error='❌ Ingresa un correo electrónico válido.';
    } elseif (!validarTelefonoHN($telefono)) {
        $error='❌ El teléfono debe contener exactamente 8 dígitos.';
    } elseif ($password!==$confirm) {
        $error='❌ Las contraseñas no coinciden.';
    } elseif (!validarPasswordSegura($password)) {
        $error='❌ La contraseña debe tener al menos 8 caracteres e incluir letras y números.';
    } else {
        $stmt=mysqli_prepare($conn,"SELECT id,nombre FROM estudiantes WHERE telefono_padre=? AND activo=1 LIMIT 1");
        mysqli_stmt_bind_param($stmt,'s',$telefono); mysqli_stmt_execute($stmt); $res=mysqli_stmt_get_result($stmt);
        $estudiante=$res?mysqli_fetch_assoc($res):null; mysqli_stmt_close($stmt);
        if (!$estudiante) {
            $error='❌ Este teléfono no está asociado a ningún estudiante activo. Contacta al administrador.';
        } else {
            $stmt=mysqli_prepare($conn,"SELECT id FROM usuarios WHERE email=? OR telefono=? LIMIT 1");
            mysqli_stmt_bind_param($stmt,'ss',$email,$telefono); mysqli_stmt_execute($stmt); $res=mysqli_stmt_get_result($stmt);
            $existe=$res && mysqli_num_rows($res)>0; mysqli_stmt_close($stmt);
            if ($existe) {
                $error='❌ Ya existe una cuenta con ese correo o teléfono.';
            } else {
                $hash=md5($password);
                $stmt=mysqli_prepare($conn,"INSERT INTO usuarios(nombre,email,password,rol,telefono,activo) VALUES(?,?,?,'padre',?,1)");
                mysqli_stmt_bind_param($stmt,'ssss',$nombre,$email,$hash,$telefono);
                if (mysqli_stmt_execute($stmt)) $success='✅ Registro exitoso. Ya puedes iniciar sesión.';
                else $error='❌ No se pudo crear la cuenta.';
                mysqli_stmt_close($stmt);
            }
        }
    }
}
function pval($name){return htmlspecialchars($_POST[$name]??'',ENT_QUOTES,'UTF-8');}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Registro - Padre</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><link rel="stylesheet" href="../css/style.css">
<style>body{background:#0A0A0A;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:24px 16px;font-family:'Segoe UI',system-ui,sans-serif}.register-box{background:linear-gradient(160deg,rgba(18,36,58,.97),rgba(8,20,33,.98));padding:40px;border-radius:24px;border:1px solid rgba(169,215,245,.16);box-shadow:0 24px 70px rgba(0,0,0,.38);max-width:450px;width:100%}.logo-login{width:80px;border-radius:12px}.copyright-footer{margin-top:24px;padding:12px 8px;text-align:center;color:#B0BEC5;font-size:12px;line-height:1.5}.register-box .btn{width:100%}</style></head><body>
<div class="register-box"><div class="text-center mb-4"><img src="../css/logo.png" alt="Logo" class="logo-login mb-3"><h2>👨‍👩‍👦 Registro de Padre</h2><p class="text-secondary">Crea tu cuenta para ver la asistencia de tu hijo</p></div>
<?php if($error):?><div class="alert alert-danger"><?php echo htmlspecialchars($error);?></div><?php endif;?>
<?php if($success):?><div class="alert alert-success"><?php echo htmlspecialchars($success);?></div><a href="../login.php" class="btn btn-success"><i class="fas fa-sign-in-alt"></i> Ir a Iniciar Sesión</a>
<?php else:?><form method="POST"><div class="mb-3"><label>Tu Nombre</label><input type="text" name="nombre" maxlength="100" class="form-control" value="<?php echo pval('nombre');?>" required></div>
<div class="mb-3"><label>Correo Electrónico</label><input type="email" name="email" maxlength="100" class="form-control" value="<?php echo pval('email');?>" required></div>
<div class="mb-3"><label>Número de Teléfono</label><input type="tel" name="telefono" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" class="form-control" value="<?php echo pval('telefono');?>" required><small class="text-secondary">Debe ser el mismo número registrado por el administrador en el estudiante.</small></div>
<div class="mb-3"><label>Contraseña</label><input type="password" name="password" minlength="8" class="form-control" required><small class="text-secondary">Mínimo 8 caracteres, con letras y números.</small></div>
<div class="mb-3"><label>Confirmar Contraseña</label><input type="password" name="confirm_password" minlength="8" class="form-control" required></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Registrarme</button></form><hr class="border-secondary"><div class="text-center"><a href="../login.php" class="text-secondary">Ya tengo cuenta</a></div><?php endif;?></div>
<footer class="copyright-footer"><div><strong>© 2026 Jahir Alexander Zuniga Enamorado</strong></div><div>Duodecimo Grado en Informatica Año 2026</div></footer></body></html>
