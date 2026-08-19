<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$rol = $_SESSION['user_rol'];

switch($rol) {
    case 'admin':
        header('Location: admin/dashboard.php');
        break;
    case 'guardia':
        header('Location: guardia/dashboard.php');
        break;
    case 'consejero':
        header('Location: consejero/dashboard.php');
        break;
    case 'padre':
        header('Location: padre/dashboard.php');
        break;
    default:
        header('Location: login.php');
}
?>