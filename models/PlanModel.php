<?php
declare(strict_types=1);

require_once __DIR__ . '/Modelo.php';

final class PlanModel extends Modelo
{
    public function listar(): array
    {
        return $this->ejecutar(
            'SELECT id, nombre, duracion_meses, precio, categoria, descripcion
               FROM planes ORDER BY duracion_meses, id'
        )->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        return $this->ejecutar(
            'SELECT id, nombre, duracion_meses, precio, categoria, descripcion FROM planes WHERE id = :id',
            ['id' => $id]
        )->fetch() ?: null;
    }

    public function crear(array $plan): array
    {
        $this->ejecutar(
            'INSERT INTO planes (nombre, duracion_meses, precio, categoria, descripcion)
              VALUES (:nombre, :duracion_meses, :precio, :categoria, :descripcion)',
            $plan
        );
        return ['id' => (int) $this->pdo->lastInsertId()] + $plan;
    }

    public function actualizar(int $id, array $plan): ?array
    {
        $this->ejecutar(
            'UPDATE planes SET nombre = :nombre, duracion_meses = :duracion_meses, precio = :precio,
                    categoria = :categoria, descripcion = :descripcion WHERE id = :id',
            $plan + ['id' => $id]
        );
        return $this->buscar($id);
    }

    public function eliminar(int $id): bool
    {
        return $this->ejecutar('DELETE FROM planes WHERE id = :id', ['id' => $id])->rowCount() === 1;
    }
}
