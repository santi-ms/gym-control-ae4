<?php
declare(strict_types=1);

require_once __DIR__ . '/Modelo.php';

final class MembresiaModel extends Modelo
{
    public function listar(int $usuario): array
    {
        return $this->ejecutar(
            'SELECT id, plan_id, plan_nombre, cantidad, precio_unitario, descuento, total, medio_pago, inicio, fin
               FROM membresias WHERE usuario_id = :usuario ORDER BY inicio DESC, id DESC',
            ['usuario' => $usuario]
        )->fetchAll();
    }

    public function asignar(int $usuario, array $datos, string $hoy): ?array
    {
        $this->pdo->beginTransaction();
        try {
            // Serializa renovaciones del mismo socio, incluso desde sesiones distintas.
            $this->ejecutar('SELECT id FROM usuarios WHERE id = :id FOR UPDATE', ['id' => $usuario])->fetch();
            $previa = $this->porSolicitud($usuario, $datos['solicitud']);
            if ($previa !== null) {
                $this->pdo->commit();
                return ['creada' => false, 'membresia' => $previa];
            }

            $plan = $this->ejecutar(
                'SELECT nombre, duracion_meses, precio FROM planes WHERE id = :id LOCK IN SHARE MODE',
                ['id' => $datos['plan_id']]
            )->fetch();
            if (!$plan) {
                $this->pdo->rollBack();
                return null;
            }

            $ultimoFin = $this->ejecutar(
                'SELECT MAX(fin) FROM membresias WHERE usuario_id = :usuario',
                ['usuario' => $usuario]
            )->fetchColumn();
            $inicio = $ultimoFin !== null && $ultimoFin > $hoy ? $ultimoFin : $hoy;
            $meses = $plan['duracion_meses'] * $datos['cantidad'];
            $fin = $this->ejecutar(
                'SELECT TIMESTAMPADD(MONTH, :meses, CAST(:inicio AS DATE))',
                ['meses' => $meses, 'inicio' => $inicio]
            )->fetchColumn();

            $subtotal = $plan['precio'] * $datos['cantidad'];
            $descuento = $datos['cupon'] === 'UCP10' ? intdiv($subtotal, 10) : 0;
            unset($datos['cupon']);
            $this->ejecutar(
                'INSERT INTO membresias (usuario_id, plan_id, plan_nombre, precio_unitario, cantidad, descuento,
                                        total, nombre, email, direccion, telefono, medio_pago, inicio, fin, solicitud)
                 VALUES (:usuario, :plan_id, :plan_nombre, :precio, :cantidad, :descuento,
                         :total, :nombre, :email, :direccion, :telefono, :medio_pago, :inicio, :fin, :solicitud)',
                $datos + [
                    'usuario' => $usuario, 'plan_nombre' => $plan['nombre'], 'precio' => $plan['precio'],
                    'descuento' => $descuento, 'total' => $subtotal - $descuento, 'inicio' => $inicio, 'fin' => $fin,
                ]
            );
            $membresia = $this->porSolicitud($usuario, $datos['solicitud']);
            $this->pdo->commit();
            return ['creada' => true, 'membresia' => $membresia];
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }

    private function porSolicitud(int $usuario, string $solicitud): ?array
    {
        return $this->ejecutar(
            'SELECT id, plan_id, plan_nombre, cantidad, precio_unitario, descuento, total, medio_pago, inicio, fin
               FROM membresias WHERE usuario_id = :usuario AND solicitud = :solicitud',
            ['usuario' => $usuario, 'solicitud' => $solicitud]
        )->fetch() ?: null;
    }
}
