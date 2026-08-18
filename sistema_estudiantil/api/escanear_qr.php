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
    $codigo_qr = mysqli_real_escape_string($conn, $_POST['codigo_qr'] ?? '');
    
    if (empty($codigo_qr)) {
        $response['message'] = 'Código QR vacío';
        echo json_encode($response);
        exit();
    }
    
    // Buscar estudiante por código QR
    $sql = "SELECT id, nombre, apellido, codigo_estudiante, curso, seccion, jornada, carnet_number 
            FROM estudiantes 
            WHERE codigo_qr = '$codigo_qr' AND activo = 1";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $estudiante = mysqli_fetch_assoc($result);
        $response['success'] = true;
        $response['message'] = 'Estudiante encontrado';
        $response['data'] = $estudiante;
    } else {
        $response['message'] = 'Estudiante no encontrado o inactivo';
    }
} else {
    $response['message'] = 'Método no permitido';
}

echo json_encode($response);
?>