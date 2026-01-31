<?php
// auth.php - Verificador de sesiones
session_start();

// Configuración de seguridad
$session_timeout = 3600; // 1 hora en segundos

function verificarAutenticacion() {
    global $session_timeout;
    
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['login_time'])) {
        header("Location: login.php?session=expired");
        exit();
    }
    
    // Verificar timeout de sesión
    if (time() - $_SESSION['login_time'] > $session_timeout) {
        session_destroy();
        header("Location: login.php?session=expired");
        exit();
    }
    
    // Renovar el tiempo de sesión
    $_SESSION['login_time'] = time();
}

function verificarRol($rol_requerido = 'admin') {
    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== $rol_requerido) {
        header("Location: admin.php?error=permisos");
        exit();
    }
}

function obtenerUsuario() {
    return [
        'id' => $_SESSION['usuario_id'] ?? null,
        'nombre' => $_SESSION['usuario_nombre'] ?? null,
        'correo' => $_SESSION['usuario_correo'] ?? null,
        'rol' => $_SESSION['usuario_rol'] ?? null
    ];
}

function esAdministrador() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}

function cerrarSesion() {
    session_destroy();
    header("Location: login.php?logout=1");
    exit();
}
?>