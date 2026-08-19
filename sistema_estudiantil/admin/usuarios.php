<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
require_once '../funciones.php';
requireRole('admin');
include '../header.php';

function rolBadge($rol) {
    switch ($rol) {
        case 'admin': return '<span class="badge bg-danger">Admin</span>';
        case 'guardia': return '<span class="badge bg-primary">Guardia</span>';
        case 'consejero': return '<span class="badge bg-warning text-dark">Consejero</span>';
        case 'padre': return '<span class="badge bg-success">Padre</span>';
        default: return '<span class="badge bg-secondary">'.htmlspecialchars($rol).'</span>';
    }
}
function accionesUsuario($row) {
    if ($row['rol'] === 'admin') return '<span class="text-secondary">Sin acciones</span>';
    $id=(int)$row['id'];
    $activo=(int)$row['activo']===1;
    $estadoIcon=$activo?'fa-user-slash':'fa-user-check';
    $estadoClass=$activo?'btn-outline-danger':'btn-outline-success';
    $estadoTitle=$activo?'Desactivar':'Activar';
    return "<div class='action-buttons'>
        <a href='usuario_form.php?id={$id}' class='btn btn-sm btn-warning' title='Editar'><i class='fas fa-edit'></i></a>
        <a href='cambiar_password.php?id={$id}' class='btn btn-sm btn-info' title='Cambiar contraseña'><i class='fas fa-key'></i></a>
        <a href='usuario_estado.php?id={$id}' class='btn btn-sm {$estadoClass}' title='{$estadoTitle}'><i class='fas {$estadoIcon}'></i></a>
        <a href='usuario_delete.php?id={$id}' class='btn btn-sm btn-danger' onclick='return confirm(\"¿Eliminar este usuario?\")' title='Eliminar'><i class='fas fa-trash'></i></a>
    </div>";
}
?>
<div class="page-heading-actions">
    <div><h1><i class="fas fa-users-cog"></i> Gestión de Usuarios</h1><p class="text-secondary mb-0">Administra personal y cuentas de padres desde un solo lugar.</p></div>
    <div class="toolbar-actions"><a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a><a href="usuario_form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Usuario</a></div>
</div>

<h3 class="user-section-title"><i class="fas fa-user-shield"></i> Personal del sistema</h3>
<div class="table-responsive"><table class="table table-dark"><thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Ubicación</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
<?php
$result=mysqli_query($conn,"SELECT * FROM usuarios WHERE rol != 'padre' ORDER BY FIELD(rol,'admin','consejero','guardia'), nombre");
while($row=mysqli_fetch_assoc($result)){
 $ubicacion=$row['ubicacion']?:'-'; $estado=((int)$row['activo']===1)?'<span class="badge bg-success">Activo</span>':'<span class="badge bg-secondary">Inactivo</span>';
 echo '<tr><td>'.htmlspecialchars($row['nombre']).'</td><td>'.htmlspecialchars($row['email']).'</td><td>'.rolBadge($row['rol']).'</td><td>'.htmlspecialchars($ubicacion).'</td><td>'.$estado.'</td><td>'.accionesUsuario($row).'</td></tr>';
}
?></tbody></table></div>

<div class="parent-users-header"><div><h3 class="user-section-title mb-1"><i class="fas fa-people-roof"></i> Usuarios Padres</h3><p class="text-secondary mb-0">Estas cuentas usan la misma tabla de usuarios y se vinculan con sus estudiantes mediante el teléfono registrado.</p></div><a href="usuario_form.php?tipo=padre" class="btn btn-success"><i class="fas fa-user-plus"></i> Nuevo Padre</a></div>
<div class="table-responsive"><table class="table table-dark"><thead><tr><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Estudiantes asociados</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
<?php
$sql="SELECT u.*, COUNT(e.id) AS hijos FROM usuarios u LEFT JOIN estudiantes e ON e.telefono_padre=u.telefono AND e.activo=1 WHERE u.rol='padre' GROUP BY u.id ORDER BY u.creado DESC";
$result=mysqli_query($conn,$sql);
if($result && mysqli_num_rows($result)>0){ while($row=mysqli_fetch_assoc($result)){
 $estado=((int)$row['activo']===1)?'<span class="badge bg-success">Activo</span>':'<span class="badge bg-secondary">Inactivo</span>';
 echo '<tr><td>'.htmlspecialchars($row['nombre']).'</td><td>'.htmlspecialchars($row['email']).'</td><td>'.htmlspecialchars($row['telefono']?:'-').'</td><td><span class="badge bg-info text-dark">'.(int)$row['hijos'].'</span></td><td>'.$estado.'</td><td>'.accionesUsuario($row).'</td></tr>';
}} else { echo '<tr><td colspan="6" class="text-center text-secondary">Todavía no hay cuentas de padres registradas.</td></tr>'; }
?></tbody></table></div>
<?php include '../footer.php'; ?>
