<?php
declare(strict_types=1);

require_once __DIR__ . '/Modelo.php';

final class UsuarioModel extends Modelo
{
    public function porEmail(string $email): ?array
    {
        return $this->ejecutar(
            'SELECT id, nombre, email, rol, clave_hash FROM usuarios WHERE email = :email',
            ['email' => $email]
        )->fetch() ?: null;
    }

    public function crear(string $nombre, string $email, string $hash, string $rol = 'socio'): array
    {
        $this->ejecutar(
            'INSERT INTO usuarios (nombre, email, clave_hash, rol) VALUES (:nombre, :email, :hash, :rol)',
            ['nombre' => $nombre, 'email' => $email, 'hash' => $hash, 'rol' => $rol]
        );
        return ['id' => (int) $this->pdo->lastInsertId(), 'nombre' => $nombre, 'email' => $email, 'rol' => $rol];
    }
}
