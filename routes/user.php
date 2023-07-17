<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use function App\Models\hashPassword;

$app->get('/user', function (Request $req, Response $res) {
    $db = getDB();
    $users = $db->getAllUser();

    $res->getBody()->write(json_encode($users));

    return $res
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

//get user byId
$app->get('/user/{id}', function (Request $req, Response $res, $args) {
    $id = $args['id'];

    $db = getDB();
    $user = $db->getUserViaId($id);

    if (!$user) {
        $res->getBody()->write(json_encode([
            'message' => 'User not found'
        ]));

        return $res
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    $response = array(
        'id' => $user->userID,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
        'photo' => $user->photo
    );

    $res->getBody()->write(json_encode($response));

    return $res
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->post('/user', function (Request $req, Response $res) {
    $db = getDB();

    $name = $req->getParsedBody()['name'] ?? null;
    $email = $req->getParsedBody()['email'] ?? null;
    $password = $req->getParsedBody()['password'] ?? null;
    $role = $req->getParsedBody()['role'] ?? 'user';
    $photo = $req->getParsedBody()['photo'] ?? null;

    if (!$name || !$email || !$password) {

        $res->getBody()->write(json_encode([
            'message' => 'Name, email, and password are required'
        ]));

        return $res
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    $user = $db->insertUser($name, $email, $password, $role, $photo);

    $res->getBody()->write(json_encode([
        'message' => 'User creation successful',
        'user' => $user
    ]));

    return $res
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});


$app->put('/user/{id}', function (Request $req, Response $res) {
    $id = $req->getAttribute('id');
    $body = $req->getParsedBody();

    if (!$id) {
        $res->getBody()->write(json_encode([
            'message' => 'ID is required'
        ]));

        return $res
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    $db = getDB();
    $user = $db->getUserViaId($id);

    if (!$user) {
        $res->getBody()->write(json_encode([
            'message' => 'User not found'
        ]));

        return $res
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    $name = $body['name'] ?? $user->name;
    $email = $body['email'] ?? $user->email;
    $role = $body['role'] ?? $user->role;
    $photo = $body['photo'] ?? $user->photo;
    $password = $body['password'] ?? $user->passwordHash;

    $user = $db->updateUserViaId($id, $name, $email, $password, $role, $photo);

    $res->getBody()->write(json_encode([
        'message' => 'User update successful',
        'user' => $user
    ]));

    return $res
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});
