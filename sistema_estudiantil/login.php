<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
include 'db.php';
require_once __DIR__ . '/presencia.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password = md5($_POST['password'] ?? '');
    $sql = "SELECT * FROM usuarios WHERE email='$email' AND password='$password' AND activo=1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_rol'] = $user['rol'];
        $_SESSION['ubicacion'] = $user['ubicacion'] ?? 'Puerta Principal';
        presenciaRegistrarActual();
        header('Location: index.php');
        exit();
    }
    $error = 'Credenciales incorrectas';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#07111f">
    <title>DHS Access Control - Iniciar sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body.login-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px 16px;
            position: relative;
            isolation: isolate;
        }
        body.login-page::before,
        body.login-page::after {
            content: '';
            position: fixed;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            filter: blur(70px);
            opacity: .16;
            pointer-events: none;
            z-index: -1;
        }
        body.login-page::before { background: #18c7df; top: -100px; left: -70px; }
        body.login-page::after { background: #0877c9; right: -80px; bottom: -120px; }
        .login-shell { width: min(100%, 430px); }
        .login-box {
            background: linear-gradient(160deg, rgba(18,36,58,.96), rgba(8,20,33,.98));
            padding: clamp(24px, 6vw, 38px);
            border-radius: 24px;
            border: 1px solid rgba(169,215,245,.16);
            box-shadow: 0 24px 70px rgba(0,0,0,.38);
            overflow: hidden;
            position: relative;
        }
        .login-box::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #0073cf, #21c7df, #0073cf);
        }
        .login-title { margin: 4px 0 3px; font-size: 1.65rem; }
        .login-subtitle { margin-bottom: 0; }
        .login-box .form-control { width: 100%; }
        .login-box .btn { width: 100%; }
        .login-copyright {
            margin: 15px 0 0;
            padding: 12px 10px 0;
            border-top: 1px solid rgba(255,255,255,.08);
            text-align: center;
            color: #8fa9bd;
            font-size: 10.5px;
            line-height: 1.5;
        }
        .login-copyright strong { color: #dcecf7; font-weight: 700; }
        .register-zone { margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,.08); }
        .security-note { display: flex; gap: 8px; align-items: center; justify-content: center; color: #7f9aad; font-size: .73rem; margin-top: 13px; }
        @media (max-width: 420px) { .login-box { border-radius: 19px; } }
    </style>
</head>
<body class="login-page">
    <div class="login-shell">
        <section class="login-box" aria-label="Inicio de sesión">
            <div class="text-center mb-4">
                <div class="logo-login-container">
                    <img src="css/logo.png" alt="Logo de la institución" class="logo-login">
                </div>
                <h1 class="login-title">DHS Access Control</h1>
                <p class="text-secondary login-subtitle">Sistema de Gestión y Asistencia</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><i class="fas fa-circle-exclamation me-1"></i><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="on">
                <div class="mb-3">
                    <label for="email"><i class="fas fa-envelope me-1"></i> Correo electrónico</label>
                    <input id="email" type="email" name="email" class="form-control" autocomplete="username" required>
                </div>
                <div class="mb-3">
                    <label for="password"><i class="fas fa-lock me-1"></i> Contraseña</label>
                    <input id="password" type="password" name="password" class="form-control" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-right-to-bracket"></i> Iniciar Sesión
                </button>
            </form>

            <div class="login-copyright">
                <strong>© 2026 Jahir Alexander Zuniga Enamorado</strong><br>
                Duodecimo Grado en Informatica Año 2026
            </div>

            <div class="register-zone text-center">
                <a href="padre/registro.php" class="btn btn-success">
                    <i class="fas fa-user-plus"></i> Registrarse como Padre
                </a>
            </div>
            <div class="security-note"><i class="fas fa-shield-halved"></i> Acceso institucional protegido</div>
        </section>
    </div>
</body>
</html>
