<?php
declare(strict_types=1);

function abrirSesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('GYM_CONTROL');
    $https = !in_array(strtolower($_SERVER['HTTPS'] ?? ''), ['', 'off', '0'], true);
    session_set_cookie_params([
        'path' => rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/',
        'httponly' => true,
        'secure' => $https,
        'samesite' => 'Lax',
    ]);
    if (!session_start()) {
        throw new RuntimeException('No se pudo abrir la sesión.');
    }
}

function iniciarSesion(array $usuario): void
{
    abrirSesion();
    if (!session_regenerate_id(true)) {
        throw new RuntimeException('No se pudo regenerar la sesión.');
    }
    unset($usuario['clave_hash']);
    $_SESSION = ['usuario' => $usuario, 'csrf' => bin2hex(random_bytes(32)), 'actividad' => time()];
}

function sesionRequerida(?string $rol = null, bool $escritura = false, bool $mantenerAbierta = false): array
{
    abrirSesion();
    $usuario = $_SESSION['usuario'] ?? null;
    if (!$usuario || time() - ($_SESSION['actividad'] ?? 0) > 1800) {
        if ($usuario) {
            cerrarSesion();
        }
        errorApi(401, 'Ingresá para continuar.');
    }
    if ($rol !== null && $usuario['rol'] !== $rol) {
        errorApi(403, 'Tu cuenta no tiene permisos para esta operación.');
    }
    $csrf = $_SESSION['csrf'];
    if ($escritura && !hash_equals($csrf, $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
        errorApi(403, 'La protección de sesión rechazó la petición. Volvé a ingresar.');
    }
    $_SESSION['actividad'] = time();
    if (!$mantenerAbierta) {
        session_write_close();
    }

    return ['usuario' => $usuario, 'csrf' => $csrf];
}

function cerrarSesion(): void
{
    $_SESSION = [];
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => $cookie['path'],
        'secure' => $cookie['secure'],
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_destroy();
}
