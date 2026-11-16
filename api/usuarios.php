<?php
declare(strict_types=1);

require_once __DIR__ . '/http.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

ejecutarApi(['POST'], function (): never {
    $datos = entradaJson();
    $nombre = texto($datos, 'nombre', 2, 100);
    $email = email($datos);
    $clave = clave($datos);
    $usuarios = new UsuarioModel(Conexion::obtener());

    try {
        $usuario = $usuarios->crear($nombre, $email, password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]));
    } catch (PDOException $error) {
        if (entradaDuplicada($error)) {
            errorApi(409, 'Ya existe una cuenta con ese e-mail.');
        }
        throw $error;
    }
    responder(201, ['usuario' => $usuario]);
});
