<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
session_start();
session_destroy();
header('Location: login.php');
exit();
?>