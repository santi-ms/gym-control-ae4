<?php
declare(strict_types=1);

require_once __DIR__ . '/http.php';
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../models/MembresiaModel.php';

ejecutarApi(['GET', 'POST'], function (string $metodo): never {
    $sesion = sesionRequerida('socio', escritura: $metodo === 'POST');
    $usuario = $sesion['usuario']['id'];
    $hoy = (new DateTimeImmutable('now', new DateTimeZone('America/Argentina/Buenos_Aires')))->format('Y-m-d');
    $membresias = new MembresiaModel(Conexion::obtener());

    if ($metodo === 'GET') {
        responder(200, ['membresias' => $membresias->listar($usuario), 'hoy' => $hoy]);
    }

    $datos = entradaJson();
    $solicitud = texto($datos, 'solicitud', 32, 32);
    $medio = $datos['medio_pago'] ?? null;
    $cupon = strtoupper(texto($datos + ['cupon' => ''], 'cupon', 0, 20));
    if (!preg_match('/^[a-f0-9]{32}$/D', $solicitud)) {
        errorApi(400, 'Identificador de solicitud inválido.');
    }
    if (!in_array($medio, ['tarjeta', 'efectivo', 'transferencia'], true)) {
        errorApi(400, 'Elegí un medio de pago válido.');
    }
    if ($cupon !== '' && $cupon !== 'UCP10') {
        errorApi(400, 'El cupón ingresado no está vigente.');
    }

    $resultado = $membresias->asignar($usuario, [
        'plan_id' => entero($datos, 'plan_id', 1, 4294967295),
        'cantidad' => entero($datos, 'cantidad', 1, 20),
        'nombre' => texto($datos, 'nombre', 2, 100),
        'email' => email($datos),
        'direccion' => texto($datos, 'direccion', 3, 160),
        'telefono' => texto($datos, 'telefono', 6, 35),
        'medio_pago' => $medio, 'solicitud' => $solicitud, 'cupon' => $cupon,
    ], $hoy);
    if ($resultado === null) {
        errorApi(404, 'Ese plan ya no está disponible.');
    }
    responder($resultado['creada'] ? 201 : 200, ['membresia' => $resultado['membresia']]);
});
