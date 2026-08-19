<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once 'funciones.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#07111f">
    <title>DHS Access Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar app-navbar navbar-dark">
        <div class="container d-flex flex-nowrap justify-content-between">
            <a class="navbar-brand" href="../index.php" aria-label="DHS Access Control">
                <span class="brand-mark"><i class="fas fa-shield-halved"></i></span>
                <span class="brand-copy">
                    <span class="brand-title">DHS Access Control</span>
                    <span class="brand-subtitle">Gestión y asistencia estudiantil</span>
                </span>
            </a>
            <div class="nav-user-area">
                <div class="user-chip" title="Usuario activo">
                    <i class="fas fa-circle-user"></i>
                    <span class="user-chip-copy">
                        <span class="user-chip-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
                        <span class="user-chip-role"><?php echo htmlspecialchars($_SESSION['user_rol'] ?? 'invitado'); ?></span>
                    </span>
                </div>
                <a href="../logout.php" class="btn btn-sm btn-danger" title="Cerrar sesión">
                    <i class="fas fa-right-from-bracket"></i><span>Salir</span>
                </a>
            </div>
        </div>
    </nav>
    <main class="container mt-4 page-shell">
