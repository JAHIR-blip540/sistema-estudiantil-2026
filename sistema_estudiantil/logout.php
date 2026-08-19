<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
session_start();
require_once __DIR__ . '/presencia.php';
presenciaQuitarUsuario();
session_unset();
session_destroy();
header('Location: login.php');
exit();
?>
