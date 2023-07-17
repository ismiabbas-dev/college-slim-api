<?php

use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use App\Models\DB;

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

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($request, $handler) {
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
