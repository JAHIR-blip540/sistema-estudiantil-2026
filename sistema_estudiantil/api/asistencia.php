<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
require_once '../db.php';
require_once '../funciones.php';

$response=['success'=>false,'message'=>'','data'=>null];
if ($_SERVER['REQUEST_METHOD']==='OPTIONS') { http_response_code(204); exit(); }

$user_id = intval($_POST['user_id'] ?? $_GET['user_id'] ?? 0);
if (!usuarioPuedeValidarAsistencia($conn,$user_id)) {
    http_response_code(403);
    $response['message']='Usuario no autorizado para validar asistencia';
    echo json_encode($response); exit();
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $estudiante_id=intval($_POST['estudiante_id']??0);
    $tipo=trim($_POST['tipo']??'entrada');
    $ubicacion=trim($_POST['ubicacion']??'Puerta Principal');
    if ($estudiante_id<=0) { $response['message']='ID de estudiante inválido'; echo json_encode($response); exit(); }
    $resultado=registrarAsistencia($conn,$estudiante_id,$tipo,$ubicacion,$user_id);
    $response['success']=$resultado['success'];
    $response['message']=$resultado['message'];
    if ($resultado['success']) $response['data']=['hora'=>$resultado['hora'],'tipo'=>$resultado['tipo']];
} elseif ($_SERVER['REQUEST_METHOD']==='GET') {
    $stmt=mysqli_prepare($conn,"SELECT a.*,e.nombre,e.apellido,e.codigo_estudiante FROM asistencia a JOIN estudiantes e ON a.estudiante_id=e.id WHERE a.fecha=CURDATE() ORDER BY a.fecha_hora DESC");
    mysqli_stmt_execute($stmt); $res=mysqli_stmt_get_result($stmt); $data=[];
    while($res && ($r=mysqli_fetch_assoc($res))) $data[]=$r;
    mysqli_stmt_close($stmt);
    $response=['success'=>true,'message'=>'','data'=>$data];
} else {
    http_response_code(405); $response['message']='Método no permitido';
}
echo json_encode($response);
?>
