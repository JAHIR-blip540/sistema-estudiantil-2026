<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
require_once '../funciones.php';
requireRole('admin');
include '../header.php';
?>

<h1><i class="fas fa-users-cog"></i> Gestión de Personal</h1>
<a href="usuario_form.php" class="btn btn-primary mb-3">
    <i class="fas fa-plus"></i> Nuevo Usuario
</a>

<div class="table-responsive">
    <table class="table table-dark">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Ubicación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM usuarios WHERE rol != 'padre' ORDER BY creado DESC";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $rol_label = '';
                switch($row['rol']) {
                    case 'admin': 
                        $rol_label = '<span class="badge bg-danger">Admin</span>'; 
                        break;
                    case 'guardia': 
                        $rol_label = '<span class="badge bg-primary">Guardia</span>'; 
                        break;
                    case 'consejero': 
                        $rol_label = '<span class="badge bg-warning">Consejero</span>'; 
                        break;
                }
                
                $ubicacion = isset($row['ubicacion']) ? $row['ubicacion'] : '-';
                
                echo "<tr>
                    <td>{$row['nombre']}</td>
                    <td>{$row['email']}</td>
                    <td>$rol_label</td>
                    <td>{$ubicacion}</td>
                    <td>";
                if ($row['rol'] != 'admin') {
                    echo "<div class='action-buttons'><a href='usuario_form.php?id={$row['id']}' class='btn btn-sm btn-warning' title='Editar'>
                            <i class='fas fa-edit'></i>
                          </a>
                          <a href='cambiar_password.php?id={$row['id']}' class='btn btn-sm btn-info' title='Cambiar Contraseña'>
                            <i class='fas fa-key'></i>
                          </a>
                          <a href='usuario_delete.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"¿Eliminar este usuario?\")' title='Eliminar'>
                            <i class='fas fa-trash'></i>
                          </a></div>";
                } else {
                    echo "<span class='text-secondary'>Sin acciones</span>";
                }
                echo "</td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include '../footer.php'; ?>