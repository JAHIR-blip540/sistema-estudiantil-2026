<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
// funciones.php - Funciones auxiliares y validaciones del sistema

function generarQR($data) {
    return "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($data);
}

function calcularEdad($fechaNacimiento, $referencia = null) {
    if (!$fechaNacimiento) return -1;
    try {
        $nacimiento = new DateTime($fechaNacimiento);
        $hoy = $referencia ? new DateTime($referencia) : new DateTime('today');
        if ($nacimiento > $hoy) return -1;
        return $nacimiento->diff($hoy)->y;
    } catch (Exception $e) {
        return -1;
    }
}

function cursosPermitidos() {
    return [
        '7mo', '8vo', '9no',
        '10° Informática', '11° Informática', '12° Informática',
        '10° Agronomía', '11° Agronomía', '12° Agronomía',
        '10° Electromecánica', '11° Electromecánica', '12° Electromecánica'
    ];
}

function validarNombrePersona($valor) {
    $valor = trim($valor);
    return strlen($valor) >= 2 && strlen($valor) <= 50 && preg_match("/^[\p{L}][\p{L} .'-]*$/u", $valor);
}

function validarTelefonoHN($telefono) {
    $limpio = preg_replace('/\D+/', '', $telefono);
    return strlen($limpio) === 8;
}

function normalizarTelefono($telefono) {
    return preg_replace('/\D+/', '', trim($telefono));
}

function validarDNI($dni) {
    $dni = preg_replace('/[\s-]+/', '', trim($dni));
    return preg_match('/^[A-Za-z0-9]{8,20}$/', $dni) === 1;
}

function validarPasswordSegura($password) {
    return strlen($password) >= 8
        && preg_match('/[A-Za-z]/', $password)
        && preg_match('/\d/', $password);
}

function generarCodigoEstudiante($conn) {
    $year = date('Y');
    $result = mysqli_query($conn, "SELECT codigo_estudiante FROM estudiantes WHERE codigo_estudiante LIKE 'EST-$year-%' ORDER BY id DESC LIMIT 1");
    $numero = 1;
    if ($result && ($row = mysqli_fetch_assoc($result))) {
        if (preg_match('/(\d+)$/', $row['codigo_estudiante'], $m)) $numero = intval($m[1]) + 1;
    }
    return "EST-$year-" . str_pad($numero, 3, '0', STR_PAD_LEFT);
}

function generarCarnetNumber($conn) {
    $result = mysqli_query($conn, "SELECT carnet_number FROM estudiantes ORDER BY id DESC LIMIT 1");
    $numero = 1;
    if ($result && ($row = mysqli_fetch_assoc($result))) {
        if (preg_match('/(\d+)$/', $row['carnet_number'], $m)) $numero = intval($m[1]) + 1;
    }
    return "CAR-" . str_pad($numero, 4, '0', STR_PAD_LEFT);
}

function generarCodigoQR($conn) {
    do {
        $codigo = 'QR-' . strtoupper(bin2hex(random_bytes(5)));
        $stmt = mysqli_prepare($conn, 'SELECT id FROM estudiantes WHERE codigo_qr = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $codigo);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $existe = $res && mysqli_num_rows($res) > 0;
        mysqli_stmt_close($stmt);
    } while ($existe);
    return $codigo;
}

function usuarioPuedeValidarAsistencia($conn, $userId) {
    $userId = intval($userId);
    if ($userId <= 0) return false;
    $stmt = mysqli_prepare($conn, "SELECT id FROM usuarios WHERE id = ? AND activo = 1 AND rol IN ('guardia','admin') LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $ok = $res && mysqli_num_rows($res) === 1;
    mysqli_stmt_close($stmt);
    return $ok;
}

/**
 * Reglas de asistencia:
 * - Estudiante activo.
 * - Una entrada y una salida por día.
 * - No se permite salida sin entrada previa.
 * - La salida debe ser posterior a la entrada.
 * - Usuario validador activo (guardia o admin).
 */
function registrarAsistencia($conn, $estudiante_id, $tipo, $ubicacion, $validado_por) {
    $estudiante_id = intval($estudiante_id);
    $validado_por = intval($validado_por);
    $tipo = trim((string)$tipo);
    $ubicacion = trim((string)$ubicacion);

    if ($estudiante_id <= 0) return ['success' => false, 'message' => 'ID de estudiante inválido'];
    if (!in_array($tipo, ['entrada', 'salida'], true)) return ['success' => false, 'message' => 'Tipo de asistencia inválido'];
    if (!usuarioPuedeValidarAsistencia($conn, $validado_por)) return ['success' => false, 'message' => 'Usuario no autorizado para validar asistencia'];
    if ($ubicacion === '' || strlen($ubicacion) > 100) return ['success' => false, 'message' => 'Ubicación inválida'];

    $stmt = mysqli_prepare($conn, "SELECT id, nombre, apellido, codigo_estudiante, telefono_padre FROM estudiantes WHERE id = ? AND activo = 1 LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $estudiante_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if (!$res || mysqli_num_rows($res) !== 1) {
        mysqli_stmt_close($stmt);
        return ['success' => false, 'message' => 'Estudiante no encontrado o inactivo'];
    }
    $estudiante = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    $fecha = date('Y-m-d');
    $nombreCompleto = trim($estudiante['nombre'] . ' ' . $estudiante['apellido']);

    $stmt = mysqli_prepare($conn, "SELECT tipo, fecha_hora FROM asistencia WHERE estudiante_id = ? AND fecha = ? ORDER BY fecha_hora ASC");
    mysqli_stmt_bind_param($stmt, 'is', $estudiante_id, $fecha);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $tieneEntrada = false;
    $tieneSalida = false;
    while ($r = mysqli_fetch_assoc($res)) {
        if ($r['tipo'] === 'entrada') $tieneEntrada = true;
        if ($r['tipo'] === 'salida') $tieneSalida = true;
    }
    mysqli_stmt_close($stmt);

    if ($tipo === 'entrada' && $tieneEntrada) {
        return ['success' => false, 'message' => "⚠️ $nombreCompleto ya tiene su entrada registrada hoy"];
    }
    if ($tipo === 'entrada' && $tieneSalida) {
        return ['success' => false, 'message' => "⚠️ $nombreCompleto ya completó su entrada y salida de hoy"];
    }
    if ($tipo === 'salida' && !$tieneEntrada) {
        return ['success' => false, 'message' => "⚠️ No se puede registrar la salida de $nombreCompleto sin una entrada previa hoy"];
    }
    if ($tipo === 'salida' && $tieneSalida) {
        return ['success' => false, 'message' => "⚠️ $nombreCompleto ya tiene su salida registrada hoy"];
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO asistencia (estudiante_id, estudiante_nombre, estudiante_codigo, tipo, ubicacion, validado_por, fecha, fecha_hora) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, 'issssis', $estudiante_id, $nombreCompleto, $estudiante['codigo_estudiante'], $tipo, $ubicacion, $validado_por, $fecha);
    $ok = mysqli_stmt_execute($stmt);
    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        if (stripos($error, 'Duplicate') !== false) return ['success' => false, 'message' => 'Ese movimiento ya fue registrado hoy'];
        return ['success' => false, 'message' => 'Error al registrar asistencia'];
    }

    $mensaje = $nombreCompleto . ' ha registrado ' . ($tipo === 'entrada' ? 'entrada' : 'salida') . ' a las ' . date('H:i');
    $telefono = $estudiante['telefono_padre'];
    $stmt = mysqli_prepare($conn, "INSERT INTO alertas (estudiante_id, estudiante_nombre, telefono_padre, mensaje) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isss', $estudiante_id, $nombreCompleto, $telefono, $mensaje);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return [
        'success' => true,
        'message' => ($tipo === 'entrada' ? '✅ Entrada autorizada - ' : '✅ Salida autorizada - ') . $nombreCompleto,
        'tipo' => $tipo,
        'hora' => date('H:i:s')
    ];
}

function getEstadisticasAsistencia($conn, $fecha = null) {
    $fecha = $fecha ?: date('Y-m-d');
    $result = mysqli_query($conn, "SELECT COUNT(*) total FROM estudiantes WHERE activo = 1");
    $total = intval(mysqli_fetch_assoc($result)['total'] ?? 0);

    $stmt = mysqli_prepare($conn, "SELECT tipo, COUNT(*) total FROM asistencia WHERE fecha = ? GROUP BY tipo");
    mysqli_stmt_bind_param($stmt, 's', $fecha);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $entradas = 0; $salidas = 0;
    while ($r = mysqli_fetch_assoc($res)) {
        if ($r['tipo'] === 'entrada') $entradas = intval($r['total']);
        if ($r['tipo'] === 'salida') $salidas = intval($r['total']);
    }
    mysqli_stmt_close($stmt);
    return ['total_estudiantes'=>$total,'entradas'=>$entradas,'salidas'=>$salidas,'ausentes'=>max(0,$total-$entradas)];
}

function getAlertasFaltas($conn) {
    $sql = "SELECT e.id,e.nombre,e.apellido,e.curso,e.seccion,e.telefono_padre,
            (DAY(CURDATE()) - COUNT(DISTINCT a.fecha)) AS faltas,
            MAX(a.fecha) AS ultima_asistencia
            FROM estudiantes e
            LEFT JOIN asistencia a ON e.id=a.estudiante_id AND a.tipo='entrada' AND a.fecha>=DATE_FORMAT(CURDATE(), '%Y-%m-01')
            WHERE e.activo=1
            GROUP BY e.id
            HAVING faltas > 3
            ORDER BY faltas DESC";
    $result = mysqli_query($conn, $sql);
    $alertas = [];
    if ($result) while ($row = mysqli_fetch_assoc($result)) $alertas[] = $row;
    return $alertas;
}

function formatearFecha($fecha) { return date('d/m/Y H:i', strtotime($fecha)); }
function formatearFechaDia($fecha) { return date('d/m/Y', strtotime($fecha)); }
function getNombreRol($rol) { return ['admin'=>'Administrador','guardia'=>'Guardia','consejero'=>'Consejero','padre'=>'Padre'][$rol] ?? $rol; }

function telefonoPadreRegistrado($conn, $telefono) {
    $telefono = normalizarTelefono($telefono);
    $stmt = mysqli_prepare($conn, "SELECT id FROM usuarios WHERE telefono = ? AND rol='padre' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $telefono);
    mysqli_stmt_execute($stmt); $res = mysqli_stmt_get_result($stmt);
    $ok = $res && mysqli_num_rows($res) > 0; mysqli_stmt_close($stmt); return $ok;
}

function getEstudiantesByTelefono($conn, $telefono) {
    $telefono = normalizarTelefono($telefono);
    $stmt = mysqli_prepare($conn, "SELECT * FROM estudiantes WHERE telefono_padre = ? AND activo = 1 ORDER BY nombre, apellido");
    mysqli_stmt_bind_param($stmt, 's', $telefono);
    mysqli_stmt_execute($stmt); $res = mysqli_stmt_get_result($stmt); $out=[];
    while ($res && ($row=mysqli_fetch_assoc($res))) $out[]=$row;
    mysqli_stmt_close($stmt); return $out;
}

function generarCarnetHTML($estudiante) {
    $qr_url = generarQR($estudiante['codigo_qr']);
    return '<div class="carnet"><div class="carnet-header"><h2>DHS Access Control</h2><span class="logo"><i class="fas fa-shield-alt"></i></span></div><div class="carnet-body"><div class="carnet-foto"><i class="fas fa-user-graduate"></i></div><div class="carnet-info"><div><div class="label">Nombre</div><div class="value">'.htmlspecialchars($estudiante['nombre'].' '.$estudiante['apellido']).'</div></div><div><div class="label">Código</div><div class="value">'.htmlspecialchars($estudiante['codigo_estudiante']).'</div></div><div><div class="label">Curso / Sección</div><div class="value">'.htmlspecialchars($estudiante['curso'].' - '.$estudiante['seccion']).'</div></div><div><div class="label">Jornada</div><div class="value">'.htmlspecialchars($estudiante['jornada']).'</div></div></div></div><div class="carnet-footer"><div class="qr"><img src="'.htmlspecialchars($qr_url).'" alt="QR"></div><div class="codigo">Carnet<br><strong>'.htmlspecialchars($estudiante['carnet_number']).'</strong></div></div></div>';
}
?>
