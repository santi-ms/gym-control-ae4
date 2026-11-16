<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

if ($argc !== 4 || !in_array($argv[1], ['admin', 'socio'], true) || !filter_var($argv[3], FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Uso: php scripts/crear-usuario.php admin|socio \"Nombre completo\" email\n");
    exit(1);
}
[, $rol, $nombre, $email] = $argv;
fwrite(STDOUT, "Ingresá la clave (8 caracteres como mínimo, 72 bytes como máximo) y pulsá Enter:\n");
$clave = rtrim((string) fgets(STDIN), "\r\n");
$nombre = trim($nombre);

if (mb_strlen($nombre, 'UTF-8') < 2 || mb_strlen($nombre, 'UTF-8') > 100 || strlen($email) > 190
    || mb_strlen($clave, 'UTF-8') < 8 || strlen($clave) > 72 || str_contains($clave, "\0")) {
    fwrite(STDERR, "Revisá nombre, e-mail y clave.\n");
    exit(1);
}

try {
    $modelo = new UsuarioModel(Conexion::obtener());
    $modelo->crear($nombre, strtolower($email), password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]), $rol);
    fwrite(STDOUT, "Cuenta creada.\n");
} catch (PDOException $error) {
    fwrite(STDERR, (int) ($error->errorInfo[1] ?? 0) === 1062
        ? "Ya existe una cuenta con ese e-mail.\n"
        : "No se pudo crear la cuenta. Revisá la configuración de la base.\n");
    exit(1);
}
