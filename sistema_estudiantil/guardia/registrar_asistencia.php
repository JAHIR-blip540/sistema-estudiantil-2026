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

// Obtener datos del POST
$data = json_decode(file_get_contents('php://input'), true);
$estudiante_id = isset($data['estudiante_id']) ? intval($data['estudiante_id']) : 0;
$tipo = isset($data['tipo']) ? $data['tipo'] : 'entrada';

if ($estudiante_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de estudiante inválido']);
    exit();
}

// Validar tipo
if (!in_array($tipo, ['entrada', 'salida'])) {
    echo json_encode(['success' => false, 'message' => 'Tipo de asistencia inválido']);
    exit();
}

$ubicacion = isset($_SESSION['ubicacion']) ? $_SESSION['ubicacion'] : 'Puerta Principal';
$validado_por = $_SESSION['user_id'];

// Registrar asistencia usando la función
$resultado = registrarAsistencia($conn, $estudiante_id, $tipo, $ubicacion, $validado_por);

if ($resultado['success']) {
    echo json_encode([
        'success' => true,
        'message' => $resultado['message'],
        'hora' => date('H:i:s')
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $resultado['message']
    ]);
}
exit();
?>