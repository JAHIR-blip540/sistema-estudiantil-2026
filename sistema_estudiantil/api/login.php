<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../db.php';
require_once '../funciones.php';

$response = ['success' => false, 'message' => '', 'data' => null];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password = md5($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $response['message'] = 'Email y contraseña son requeridos';
        echo json_encode($response);
        exit();
    }
    
    $sql = "SELECT id, nombre, email, rol, ubicacion FROM usuarios WHERE email='$email' AND password='$password' AND activo=1";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $response['success'] = true;
        $response['message'] = 'Login exitoso';
        $response['data'] = $user;
    } else {
        $response['message'] = 'Credenciales incorrectas';
    }
} else {
    $response['message'] = 'Método no permitido';
}

echo json_encode($response);
?>