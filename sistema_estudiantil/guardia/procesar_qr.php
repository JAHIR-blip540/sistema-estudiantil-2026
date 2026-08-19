<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
// Desactivar errores en pantalla para que no rompan el JSON
error_reporting(0);
ini_set('display_errors', 0);

require_once '../db.php';
require_once '../funciones.php';

// Verificar sesión
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'guardia') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

header('Content-Type: application/json');

// Obtener datos
$data = json_decode(file_get_contents('php://input'), true);
$codigo_qr = isset($data['codigo_qr']) ? trim($data['codigo_qr']) : '';

if (empty($codigo_qr)) {
    echo json_encode(['success' => false, 'message' => 'Código QR vacío']);
    exit();
}

// Limpiar el código
$codigo_qr = mysqli_real_escape_string($conn, $codigo_qr);

// Buscar estudiante
$sql = "SELECT * FROM estudiantes WHERE codigo_qr = '$codigo_qr' AND activo = 1";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 1) {
    $estudiante = mysqli_fetch_assoc($result);
    $estudiante['qr_url'] = generarQR($estudiante['codigo_qr']);
    
    echo json_encode([
        'success' => true,
        'estudiante' => $estudiante
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Estudiante no encontrado: ' . $codigo_qr
    ]);
}
exit();
?>