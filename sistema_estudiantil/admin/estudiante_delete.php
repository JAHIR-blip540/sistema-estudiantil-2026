<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
requireRole('admin');

$id = isset($_GET['id']) ? $_GET['id'] : 0;

if ($id > 0) {
    $sql = "DELETE FROM estudiantes WHERE id=$id";
    mysqli_query($conn, $sql);
}

header('Location: estudiantes.php');
exit();
?>