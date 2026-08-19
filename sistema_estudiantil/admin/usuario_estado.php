<?php
require_once '../db.php'; require_once '../funciones.php'; requireRole('admin');
$id=(int)($_GET['id']??0);
if($id>0){$stmt=mysqli_prepare($conn,"UPDATE usuarios SET activo=IF(activo=1,0,1) WHERE id=? AND rol!='admin'");mysqli_stmt_bind_param($stmt,'i',$id);mysqli_stmt_execute($stmt);mysqli_stmt_close($stmt);}
header('Location: usuarios.php'); exit();
?>
