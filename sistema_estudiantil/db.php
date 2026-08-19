<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
// db.php - Conexión a base de datos con variables de entorno

// Obtener variables de entorno de Railway
$host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
$db = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'sistema_estudiantil';
$port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '3306';

// Para Railway MySQL
$host = getenv('MYSQLHOST') ?: $host;
$user = getenv('MYSQLUSER') ?: $user;
$pass = getenv('MYSQLPASSWORD') ?: $pass;
$db = getenv('MYSQLDATABASE') ?: $db;
$port = getenv('MYSQLPORT') ?: $port;

// Si hay host con puerto incluido, separarlos
if (strpos($host, ':') !== false) {
    list($host, $port) = explode(':', $host);
}

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Funciones de autenticación
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasRole($rol) {
    return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] == $rol;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

function requireRole($rol) {
    requireLogin();
    if (!hasRole($rol)) {
        header('Location: ../login.php');
        exit();
    }
}

// Configurar zona horaria
date_default_timezone_set('America/Tegucigalpa');
?>