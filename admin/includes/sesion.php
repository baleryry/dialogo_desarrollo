<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/funciones.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function usuarioAutenticado(): bool
{
    return !empty($_SESSION['usuario']['id']);
}

function usuarioActual(): array
{
    return $_SESSION['usuario'] ?? [];
}

function exigirLogin(): void
{
    if (!usuarioAutenticado()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

function exigirAdmin(): void
{
    exigirLogin();

    $usuario = usuarioActual();

    if (!esAdministrador($usuario['rol'] ?? '')) {
        $_SESSION['alerta_tipo'] = 'danger';
        $_SESSION['alerta_mensaje'] =
            'Solo un Administrador puede acceder a esta sección.';

        header('Location: ' . ADMIN_URL . '/index.php');
        exit;
    }
}
