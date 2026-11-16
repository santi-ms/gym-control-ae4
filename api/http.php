<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

function responder(int $estado, array $body): never
{
    $json = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    http_response_code($estado);
    echo $json;
    exit;
}

function errorApi(int $estado, string $mensaje): never
{
    responder($estado, ['error' => $mensaje]);
}

function ejecutarApi(array $metodos, callable $controlador): never
{
    ini_set('display_errors', '0');
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    $metodo = $_SERVER['REQUEST_METHOD'] ?? '';
    if (!in_array($metodo, $metodos, true)) {
        header('Allow: ' . implode(', ', $metodos));
        errorApi(405, 'Método no permitido.');
    }
    if ($metodo !== 'GET' && ($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '') === 'cross-site') {
        errorApi(403, 'Origen de la petición no permitido.');
    }

    try {
        $controlador($metodo);
    } catch (PDOException $error) {
        error_log('Gym Control | PDO | SQLSTATE ' . $error->getCode());
        errorApi(500, 'Error interno de persistencia. Volvé a intentar.');
    } catch (Throwable $error) {
        error_log('Gym Control | ' . get_class($error) . ' | ' . basename($error->getFile()) . ':' . $error->getLine());
        errorApi(500, 'Ocurrió un error interno. Volvé a intentar.');
    }

    errorApi(500, 'Ocurrió un error interno.');
}

function entradaJson(): array
{
    $tipo = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
    if ($tipo !== 'application/json') {
        errorApi(400, 'Enviá el cuerpo como application/json.');
    }
    $cuerpo = file_get_contents('php://input', false, null, 0, 16385);
    if ($cuerpo === false || strlen($cuerpo) > 16384) {
        errorApi(400, 'El cuerpo supera el tamaño permitido.');
    }
    try {
        $datos = json_decode($cuerpo, false, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        errorApi(400, 'El JSON es inválido.');
    }
    if (!$datos instanceof stdClass) {
        errorApi(400, 'El cuerpo debe ser un objeto JSON.');
    }
    return (array) $datos;
}

function texto(array $datos, string $campo, int $minimo, int $maximo): string
{
    $valor = $datos[$campo] ?? null;
    $valor = is_string($valor) ? trim($valor) : '';
    $longitud = mb_strlen($valor, 'UTF-8');
    if ($longitud < $minimo || $longitud > $maximo || str_contains($valor, "\0")) {
        errorApi(400, "Revisá el campo $campo ($minimo a $maximo caracteres).");
    }
    return $valor;
}

function entero(array $datos, string $campo, int $minimo, int $maximo): int
{
    $entrada = $datos[$campo] ?? null;
    $valor = is_int($entrada) || is_string($entrada) ? filter_var($entrada, FILTER_VALIDATE_INT) : false;
    if ($valor === false || $valor < $minimo || $valor > $maximo) {
        errorApi(400, "Revisá el campo $campo ($minimo a $maximo).");
    }
    return $valor;
}

function email(array $datos): string
{
    $valor = strtolower(texto($datos, 'email', 3, 190));
    if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
        errorApi(400, 'Ingresá un e-mail válido.');
    }
    return $valor;
}

function clave(array $datos): string
{
    $valor = $datos['clave'] ?? null;
    if (!is_string($valor) || mb_strlen($valor, 'UTF-8') < 8 || strlen($valor) > 72 || str_contains($valor, "\0")) {
        errorApi(400, 'La clave debe tener al menos 8 caracteres y como máximo 72 bytes.');
    }
    return $valor;
}

function entradaDuplicada(PDOException $error): bool
{
    return (int) ($error->errorInfo[1] ?? 0) === 1062;
}
