<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;

function generateToken($email, $userID)
{
    $payload = [
        'email' => $email,
        'userID' => $userID,
        'iat' => time(),
        'exp' => time() + 60 * 60 * 24
    ];

    $algo = 'HS256';

    return JWT::encode($payload, 'SECRET_KEY', $algo);
}

$app->post('/auth/login', function (Request $req, Response $res) {
    $body = $req->getParsedBody();
    $email = $body['email'];
    $password = $body['password'];

    $db = getDB();
    $user = $db->getUserViaLogin($email);
    $db->close();

    $status = password_verify($password, $user->passwordHash);

    if(!password_verify($password, $user->passwordHash)) {
        $res->getBody()->write(json_encode([
            'message' => 'Invalid username or password',
            'status' => $status,
            'password' => $password,
            'hash' => $user->passwordHash
        ]));

        return $res
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }

    if (!$user) {
        $res->getBody()->write(json_encode([
            'message' => 'User not found'
        ]));

        return $res
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    $token = generateToken($email, $password, $user->userID);

    $res->getBody()->write(json_encode([
        'message' => 'Login successful',
        'token' => $token,
        'id' => $user->userID,
        'role' => $user->role
    ]));

    // $res->getBody()->write(json_encode($user));

    return $res
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->post('/auth/register', function (Request $req, Response $res) {
    $body = $req->getParsedBody();
    $name = $body['name'];
    $email = $body['email'];
    $password = $body['password'];
    $role = $body['role'] ?? 'user';
    $photo = $body['photo'] ?? null;

    $db = getDB();
    $user = $db->insertUser($name, $email, $password, $role, $photo);
    $db->close();

    $res->getBody()->write(json_encode([
        'message' => 'User creation successful',
        'user' => $user
    ]));

    return $res
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->get('/auth/token', function (Request $req, Response $res) {

    $token = generateToken(
        'test400@test.com',
        1
    );

    $res->getBody()->write(json_encode([
        'token' => $token
    ]));

    return $res
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});
