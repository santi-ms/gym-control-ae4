<?php
declare(strict_types=1);

require_once __DIR__ . '/http.php';
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

ejecutarApi(['GET', 'POST', 'DELETE'], function (string $metodo): never {
    if ($metodo === 'GET') {
        responder(200, sesionRequerida());
    }
    if ($metodo === 'DELETE') {
        sesionRequerida(escritura: true, mantenerAbierta: true);
        cerrarSesion();
        responder(200, ['mensaje' => 'Sesión cerrada.']);
    }

    $datos = entradaJson();
    $email = email($datos);
    $clave = clave($datos);
    $usuarios = new UsuarioModel(Conexion::obtener());
    $usuario = $usuarios->porEmail($email);
    // Ejecuta BCRYPT también cuando el e-mail no existe.
    $hashFijo = '$2y$12$gI6FXubeKh8NxgzUpsPA5Onkgfm1IrQ6wTyxpyHW6AOlPf6b608kG';
    if (!password_verify($clave, $usuario['clave_hash'] ?? $hashFijo) || !$usuario) {
        errorApi(401, 'E-mail o clave incorrectos.');
    }
    iniciarSesion($usuario);
    responder(200, sesionRequerida());
});
