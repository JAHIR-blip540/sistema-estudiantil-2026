<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
// enviar_sms.php - Simula envío de SMS (usa Twilio o API real después)
function enviarCodigoSMS($telefono, $codigo) {
    // 🔴 TEMPORAL: En lugar de enviar SMS, guardamos en sesión
    // Para producción, usa Twilio, Nexmo, o API de SMS local
    
    // Simular envío exitoso
    $_SESSION['sms_codigo'] = $codigo;
    $_SESSION['sms_telefono'] = $telefono;
    $_SESSION['sms_tiempo'] = time();
    
    // En producción, aquí iría el código real de SMS
    // Ejemplo con Twilio:
    // require_once 'vendor/autoload.php';
    // use Twilio\Rest\Client;
    // $client = new Client($sid, $token);
    // $client->messages->create($telefono, ['from' => '+1234567890', 'body' => "Tu código es: $codigo"]);
    
    return true;
}

function verificarCodigoSMS($telefono, $codigo) {
    if (!isset($_SESSION['sms_codigo']) || !isset($_SESSION['sms_telefono']) || !isset($_SESSION['sms_tiempo'])) {
        return false;
    }
    
    // Verificar que no haya expirado (5 minutos)
    if (time() - $_SESSION['sms_tiempo'] > 300) {
        return false;
    }
    
    return ($_SESSION['sms_telefono'] == $telefono && $_SESSION['sms_codigo'] == $codigo);
}
?>