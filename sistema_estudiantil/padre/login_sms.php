<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
include '../db.php';
include '../enviar_sms.php';

$error = '';
$success = '';
$telefono = '';
$step = 'telefono'; // telefono | codigo | dashboard

// Si ya está logueado, redirigir
if (isset($_SESSION['user_id']) && $_SESSION['user_rol'] == 'padre') {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'enviar_codigo') {
        $telefono = mysqli_real_escape_string($conn, $_POST['telefono']);
        
        // Verificar que exista un estudiante con ese teléfono
        $sql = "SELECT * FROM estudiantes WHERE telefono_padre = '$telefono' AND activo = 1";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $estudiantes = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $estudiantes[] = $row;
            }
            
            // Generar código de 6 dígitos
            $codigo = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
            
            // Enviar SMS (simulado)
            if (enviarCodigoSMS($telefono, $codigo)) {
                $_SESSION['sms_telefono_temp'] = $telefono;
                $_SESSION['sms_estudiantes'] = $estudiantes;
                $step = 'codigo';
                $success = "📱 Se ha enviado un código de verificación al número $telefono";
            } else {
                $error = "Error al enviar el código. Intenta de nuevo.";
            }
        } else {
            $error = "❌ Número de teléfono no registrado. Contacta al administrador.";
        }
    }
    
    elseif ($action == 'verificar_codigo') {
        $telefono = $_SESSION['sms_telefono_temp'] ?? '';
        $codigo = $_POST['codigo'] ?? '';
        
        if (verificarCodigoSMS($telefono, $codigo)) {
            // Buscar o crear usuario padre
            $estudiantes = $_SESSION['sms_estudiantes'] ?? [];
            
            if (!empty($estudiantes)) {
                // Verificar si ya tiene usuario
                $sql = "SELECT * FROM usuarios WHERE telefono = '$telefono' AND rol = 'padre'";
                $result = mysqli_query($conn, $sql);
                
                if (mysqli_num_rows($result) == 1) {
                    $user = mysqli_fetch_assoc($result);
                } else {
                    // Crear usuario padre
                    $nombre = "Padre de " . $estudiantes[0]['nombre'] . " " . $estudiantes[0]['apellido'];
                    $email = "padre_" . $telefono . "@dhs.com";
                    
                    $sql_insert = "INSERT INTO usuarios (nombre, email, rol, telefono, activo) 
                                  VALUES ('$nombre', '$email', 'padre', '$telefono', 1)";
                    
                    if (mysqli_query($conn, $sql_insert)) {
                        $user_id = mysqli_insert_id($conn);
                        $sql = "SELECT * FROM usuarios WHERE id = $user_id";
                        $result = mysqli_query($conn, $sql);
                        $user = mysqli_fetch_assoc($result);
                    } else {
                        $error = "Error al crear usuario";
                    }
                }
                
                if (isset($user) && $user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nombre'];
                    $_SESSION['user_rol'] = 'padre';
                    $_SESSION['user_phone'] = $telefono;
                    
                    // Limpiar sesión SMS
                    unset($_SESSION['sms_codigo']);
                    unset($_SESSION['sms_telefono']);
                    unset($_SESSION['sms_tiempo']);
                    unset($_SESSION['sms_telefono_temp']);
                    unset($_SESSION['sms_estudiantes']);
                    
                    header('Location: dashboard.php');
                    exit();
                }
            }
        } else {
            $error = "❌ Código incorrecto o expirado. Solicita uno nuevo.";
            $step = 'codigo';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Padre - DHS Access Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background: #0A0A0A;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 24px 16px;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .login-box {
            background: linear-gradient(160deg, rgba(18,36,58,.97), rgba(8,20,33,.98));
            padding: 40px;
            border-radius: 24px;
            border: 1px solid rgba(169,215,245,.16);
            box-shadow: 0 24px 70px rgba(0,0,0,.38);
            max-width: 400px;
            width: 100%;
        }
        .form-control {
            background: #0A0A0A;
            border: 1px solid rgba(169,215,245,.16);
            color: white;
            border-radius: 12px;
            padding: 12px 16px;
        }
        .form-control:focus {
            border-color: #00BCD4;
            box-shadow: 0 0 0 3px rgba(0,188,212,0.2);
            background: #0A0A0A;
            color: white;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0D47A1, #00BCD4);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            color: white;
            width: 100%;
        }
        .btn-primary:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(0,188,212,0.3);
        }
        .text-secondary { color: #B0BEC5 !important; }
        .alert-danger { background: rgba(255,23,68,0.1); border: 1px solid #FF1744; color: #FF1744; }
        .alert-success { background: rgba(0,230,118,0.1); border: 1px solid #00E676; color: #00E676; }
        h2 { color: white; }
        label { color: white; }
        .logo-login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1rem;
            padding: 5px 0;
        }
        .logo-login {
            width: 80px;
            height: auto;
            max-width: 100%;
            border-radius: 12px;
        }
        .codigo-input {
            font-size: 24px;
            letter-spacing: 8px;
            text-align: center;
        }
    </style>

    <style>
        .copyright-footer { margin-top: 24px; padding: 12px 8px; text-align: center; color: #B0BEC5; font-size: 12px; line-height: 1.5; border-top: 1px solid rgba(128,128,128,.25); }
        .copyright-footer div:first-child { font-weight: 600; }
        @media print { .copyright-footer { color: #333; border-top: 1px solid #bbb; font-size: 10px; } }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="text-center mb-4">
            <div class="logo-login-container">
                <img src="../css/logo.png" alt="Logo Institución" class="logo-login" />
            </div>
            <h2>👨‍👩‍👦 Acceso Padre</h2>
            <p class="text-secondary">Inicia sesión con tu número de teléfono</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($step == 'telefono' || empty($step)): ?>
            <form method="POST">
                <input type="hidden" name="action" value="enviar_codigo">
                <div class="mb-3">
                    <label>📱 Número de Teléfono</label>
                    <input type="text" name="telefono" class="form-control" placeholder="Ej: 98765432" required>
                    <small class="text-secondary">Ingresa el número registrado en el sistema</small>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-sms"></i> Enviar Código de Verificación
                </button>
            </form>
        <?php endif; ?>
        
        <?php if ($step == 'codigo'): ?>
            <form method="POST">
                <input type="hidden" name="action" value="verificar_codigo">
                <div class="mb-3">
                    <label>🔑 Código de Verificación</label>
                    <input type="text" name="codigo" class="form-control codigo-input" placeholder="123456" maxlength="6" required>
                    <small class="text-secondary">Ingresa el código de 6 dígitos recibido por SMS</small>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check"></i> Verificar e Iniciar Sesión
                </button>
            </form>
            <div class="text-center mt-3">
                <a href="login_sms.php" class="text-secondary">← Volver a enviar código</a>
            </div>
        <?php endif; ?>
        
        <hr class="border-secondary">
        <div class="text-center">
            <small class="text-secondary">¿Eres administrador o personal?</small><br>
            <a href="../login.php" class="text-secondary">Iniciar Sesión con Email</a>
        </div>
    </div>

    <footer class="copyright-footer">
        <div>© 2026 Jahir Alexander Zuniga Enamorado</div>
        <div>Duodecimo Grado en Informatica Año 2026</div>
    </footer>
</body>
</html>