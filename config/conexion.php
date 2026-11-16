<?php
declare(strict_types=1);

final class Conexion
{
    public static function obtener(): PDO
    {
        static $pdo = null;

        if ($pdo === null) {
            $archivo = __DIR__ . '/local.php';
            $local = is_file($archivo) ? require $archivo : [];
            $host = $local['host'] ?? '127.0.0.1';
            $puerto = $local['puerto'] ?? 3306;
            $base = $local['base'] ?? 'gym_control';
            $usuario = $local['usuario'] ?? 'root';
            $clave = $local['clave'] ?? '';

            $pdo = new PDO("mysql:host=$host;port=$puerto;dbname=$base;charset=utf8mb4", $usuario, $clave, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]);
        }

        return $pdo;
    }
}
