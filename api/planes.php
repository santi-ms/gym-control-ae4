<?php
declare(strict_types=1);

require_once __DIR__ . '/http.php';
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../models/PlanModel.php';

ejecutarApi(['GET', 'POST', 'PUT', 'DELETE'], function (string $metodo): never {
    if ($metodo !== 'GET') {
        sesionRequerida('admin', escritura: true);
    }

    $id = null;
    if (isset($_GET['id']) || in_array($metodo, ['PUT', 'DELETE'], true)) {
        $id = entero($_GET, 'id', 1, 4294967295);
    }
    $planes = new PlanModel(Conexion::obtener());

    if ($metodo === 'GET') {
        if ($id === null) {
            responder(200, ['planes' => $planes->listar()]);
        }
        $plan = $planes->buscar($id) ?? errorApi(404, 'Ese plan ya no está disponible.');
        responder(200, ['plan' => $plan]);
    }

    if ($metodo === 'DELETE') {
        try {
            $eliminado = $planes->eliminar($id);
        } catch (PDOException $error) {
            if ((int) ($error->errorInfo[1] ?? 0) === 1451) {
                errorApi(409, 'El plan tiene membresías o consultas asociadas.');
            }
            throw $error;
        }
        if (!$eliminado) {
            errorApi(404, 'Ese plan ya no está disponible.');
        }
        responder(200, ['mensaje' => 'Plan eliminado.']);
    }

    $datos = entradaJson();
    $plan = [
        'nombre' => texto($datos, 'nombre', 2, 100),
        'duracion_meses' => entero($datos, 'duracion_meses', 1, 36),
        'precio' => entero($datos, 'precio', 1, 10000000),
        'categoria' => texto($datos, 'categoria', 2, 60),
        'descripcion' => texto($datos, 'descripcion', 5, 600),
    ];

    if ($metodo === 'POST') {
        $nuevo = $planes->crear($plan);
        header('Location: planes.php?id=' . $nuevo['id']);
        responder(201, ['plan' => $nuevo]);
    }

    $actualizado = $planes->actualizar($id, $plan) ?? errorApi(404, 'Ese plan ya no está disponible.');
    responder(200, ['plan' => $actualizado]);
});
