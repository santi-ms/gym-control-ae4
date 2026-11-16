<?php
declare(strict_types=1);

require_once __DIR__ . '/Modelo.php';

final class ConsultaModel extends Modelo
{
    public function crear(int $usuario, int $plan, string $mensaje): int
    {
        $this->ejecutar(
            'INSERT INTO consultas (usuario_id, plan_id, mensaje) VALUES (:usuario, :plan, :mensaje)',
            ['usuario' => $usuario, 'plan' => $plan, 'mensaje' => $mensaje]
        );
        return (int) $this->pdo->lastInsertId();
    }
}
