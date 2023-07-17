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
    $db = getDB();
    try {
        $decoded = JWT::decode($token, new Key('SECRET_KEY', 'HS256'));

        $user = $db->getUserViaLogin($decoded->email);

        if (!$user) {
            return false;
        }

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

$app->setBasePath('/api/v1');

$app->getContainer();


$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($request, $handler) {
    return $handler->handle($request);
})->add(function ($request, $handler) {
    $disableAuthHeader = true;

    $publicRoutes = [
        '/api/v1/auth/token',
        '/api/v1/auth/login',
        '/api/v1/auth/register'
    ];

    $path = $request->getUri()->getPath();

    if (in_array($path, $publicRoutes) || $disableAuthHeader) {
        return $handler->handle($request);
    }

    if (!$request->hasHeader('Authorization')) {
        $response = new Response();
        $response->getBody()->write(json_encode(['error' => 'No token provided']));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }

    $token = $request->getHeaderLine('Authorization');

    if (!isValidToken($token)) {
        $response = new Response();
        $response->getBody()->write(json_encode(['error' => 'Access denied']));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }

    $response = $handler->handle($request);

    return $response;
})->add(function ($request, $handler) {
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
