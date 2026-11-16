<?php
declare(strict_types=1);

abstract class Modelo
{
    public function __construct(protected PDO $pdo)
    {
    }

    protected function ejecutar(string $sql, array $parametros = []): PDOStatement
    {
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($parametros);
        return $consulta;
    }
}
