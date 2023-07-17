<?php

namespace App\Utils;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Exception;
use Firebase\JWT\JWT;

class JWTMiddleware
{
    private $secretKey;

    public function __construct($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function createToken(array $data, $expiration = '+1 hour'): string
    {
        $issuedAt = time();
        $expireAt = strtotime($expiration, $issuedAt);

        $payload = array(
            'iat' => $issuedAt,
            'exp' => $expireAt,
            'data' => $data
        );

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function validateToken(Request $request, RequestHandler $handler): Response
    {
        $token = $request->getHeaderLine('Authorization');

        if (!$token) {
            $response = new Response();
            $response->getBody()->write('Unauthorized');
            return $response->withStatus(401);
        }

        try {
            $decoded = JWT::decode($token, $this->secretKey);

            $request = $request->withAttribute('jwtPayload', $decoded);

            $response = $handler->handle($request);
            return $response;
        } catch (Exception $e) {
            $response = new Response();
            $response->getBody()->write('Unauthorized');
            return $response->withStatus(401);
        }
    }

}
