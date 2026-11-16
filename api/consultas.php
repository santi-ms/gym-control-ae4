<?php
declare(strict_types=1);

require_once __DIR__ . '/http.php';
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../models/ConsultaModel.php';

ejecutarApi(['POST'], function (): never {
    $sesion = sesionRequerida('socio', escritura: true);
    $datos = entradaJson();
    $modelo = new ConsultaModel(Conexion::obtener());
    try {
        $id = $modelo->crear(
            $sesion['usuario']['id'],
            entero($datos, 'plan_id', 1, 4294967295),
            texto($datos, 'mensaje', 10, 500)
        );
    } catch (PDOException $error) {
        if ((int) ($error->errorInfo[1] ?? 0) === 1452) {
            errorApi(404, 'Ese plan ya no está disponible.');
        }
        throw $error;
    }
    responder(201, ['id' => $id, 'mensaje' => 'Tu consulta quedó registrada.']);
});
