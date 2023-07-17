<?php

use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use App\Models\DB;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\SignatureInvalidException;
use Slim\Psr7\Response;

require __DIR__ . '/../vendor/autoload.php';

function getDB()
{
    return new DB(
        'localhost',
        'root',
        'root12345',
        'college'
    );
}

function isValidToken($token)
{
    try {
        $decoded = JWT::decode($token, new Key('SECRET_KEY', 'HS256'));
        return $decoded->exp > time();
    } catch (ExpiredException $e) {
        return false;
    } catch (BeforeValidException $e) {
        return false;
    } catch (SignatureInvalidException $e) {
        return false;
    } catch (\Exception $e) {
        return false;
    }
}


$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($request, $handler) {

    $publicRoute = [
        '/v1/auth/token',
        '/v1/auth/login',
        '/v1/auth/register'
    ];

    if (in_array($request->getUri()->getPath(), $publicRoute)) {
        return $handler->handle($request);
    }

    if (!$request->hasHeader('Authorization')) {
        $res = new Response();
        $res->getBody()->write(json_encode(['error' => 'No token provided']));

        return $res
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }

    $token = $request->getHeaderLine('Authorization');

    if (!isValidToken($token)) {
        $res = new Response();
        $res->getBody()->write(json_encode(['error' => 'Access denied']));

        return $res
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }

    $response = $handler->handle($request);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

require __DIR__ . '/../routes/booking.php';
require __DIR__ . '/../routes/room.php';
require __DIR__ . '/../routes/auth.php';
require __DIR__ . '/../routes/user.php';

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    throw new HttpNotFoundException($request);
});

$app->run();
