<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response as SlimResponse;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->addErrorMiddleware(true, true, true);

function addBasePath(Request $request, RequestHandler $handler): Response
{
    $uri = $request->getUri();
    $basePath = '/api/v1';

    $uri = $uri->withPath($basePath . $uri->getPath());
    $request = $request->withUri($uri);

    $response = $handler->handle($request);
    return $response;
}

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

$app->addRoutingMiddleware();

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->add('checkToken');
$app->add('addBasePath');

$app->run();
