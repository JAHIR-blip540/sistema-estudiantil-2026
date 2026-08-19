<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */

/**
 * Control ligero de presencia para el personal del sistema.
 * No crea tablas nuevas: usa un archivo temporal del servidor.
 * Una sesión se considera activa si tuvo actividad en los últimos 90 segundos.
 */

function presenciaArchivo(): string {
    return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'dhs_personal_activo.json';
}

function presenciaRolesPermitidos(): array {
    return ['admin', 'consejero', 'guardia'];
}

function presenciaLeer(): array {
    $archivo = presenciaArchivo();
    if (!is_file($archivo)) {
        return [];
    }

    $fp = @fopen($archivo, 'r');
    if (!$fp) {
        return [];
    }

    @flock($fp, LOCK_SH);
    $contenido = stream_get_contents($fp);
    @flock($fp, LOCK_UN);
    fclose($fp);

    $datos = json_decode($contenido ?: '[]', true);
    return is_array($datos) ? $datos : [];
}

function presenciaGuardar(array $datos): void {
    $archivo = presenciaArchivo();
    $fp = @fopen($archivo, 'c+');
    if (!$fp) {
        return;
    }

    if (@flock($fp, LOCK_EX)) {
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        fflush($fp);
        @flock($fp, LOCK_UN);
    }
    fclose($fp);
}

function presenciaLimpiar(array $datos, int $vigencia = 90): array {
    $limite = time() - $vigencia;
    foreach ($datos as $id => $item) {
        $ultimo = (int)($item['ultimo'] ?? 0);
        if ($ultimo < $limite) {
            unset($datos[$id]);
        }
    }
    return $datos;
}

function presenciaRegistrarActual(): void {
    if (empty($_SESSION['user_id']) || empty($_SESSION['user_rol'])) {
        return;
    }

    $rol = (string)$_SESSION['user_rol'];
    if (!in_array($rol, presenciaRolesPermitidos(), true)) {
        return;
    }

    $datos = presenciaLimpiar(presenciaLeer());
    $id = (string)(int)$_SESSION['user_id'];
    $datos[$id] = [
        'id' => (int)$_SESSION['user_id'],
        'nombre' => (string)($_SESSION['user_name'] ?? ''),
        'rol' => $rol,
        'ultimo' => time(),
    ];
    presenciaGuardar($datos);
}

function presenciaQuitarUsuario(?int $userId = null): void {
    $userId = $userId ?? (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0);
    if ($userId <= 0) {
        return;
    }

    $datos = presenciaLimpiar(presenciaLeer());
    unset($datos[(string)$userId]);
    presenciaGuardar($datos);
}

function presenciaTotalActivos(): int {
    $datos = presenciaLimpiar(presenciaLeer());
    presenciaGuardar($datos);
    return count($datos);
}
