<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response as SlimResponse;
use App\Utils\JWTMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();


$app->addBodyParsingMiddleware();

$app->addErrorMiddleware(true, true, true);

$jwtMiddleware = new JWTMiddleware('YOUR_SECRET_KEY');

$app->add(function (Request $request, RequestHandler $handler) use ($jwtMiddleware) {
    return $jwtMiddleware->validateToken($request, $handler);
});

$app->get('/protected', function (Request $request, Response $response) {
    $payload = $request->getAttribute('jwtPayload');
    $userId = $payload->data->userId;

    $response->getBody()->write("Authenticated user with ID: $userId");
    return $response;
});


function checkToken(Request $request, RequestHandler $handler): Response
{
    $token = $request->getHeaderLine('Authorization');
    $validToken = 'YOUR_VALID_TOKEN';

    if ($token === $validToken) {
        $response = $handler->handle($request);
        return $response;
    } else {
        $response = new SlimResponse();
        $response->getBody()->write('Unauthorized');
        return $response->withStatus(401);
    }
}

$app->get('/test', function (Request $request, Response $response, $args) {


    $response->getBody()->write(json_encode([
        'name' => 'Slim 4 Skeleton',
        'version' => '1.0.0',
        'status' => 'OK',
        'message' => 'API is running'
    ]));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

require __DIR__ . '/../routes/booking.php';
require __DIR__ . '/../routes/room.php';
require __DIR__ . '/../routes/auth.php';
require __DIR__ . '/../routes/user.php';

$app->add('checkToken');

$app->run();
