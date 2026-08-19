<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../presencia.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'total' => 0]);
    exit();
}

presenciaRegistrarActual();

echo json_encode([
    'success' => true,
    'total' => presenciaTotalActivos(),
    'rol' => $_SESSION['user_rol'] ?? null,
]);
