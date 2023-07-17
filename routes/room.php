<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/room', function (Request $req, Response $res) {
    $db = getDB();
    $rooms = $db->getAllRooms();

    $res->getBody()->write(json_encode($rooms));

    return $res
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->get('/room/{id}', function (Request $req, Response $res) {
    $db = getDB();
    $id = $req->getAttribute('id');

    $room = $db->getRoomViaId($id);

    if (!$room) {
        $res->getBody()->write(json_encode([
            'message' => 'Room not found'
        ]));

        return $res
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    $res->getBody()->write(json_encode($room));

    return $res
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->post('/room', function (Request $req, Response $res) {
    $db = getDB();
    $type = $req->getParsedBody()['type'] ?? null;
    $status = $req->getParsedBody()['status'] ?? 1;
    $number = $req->getParsedBody()['number'] ?? 1;


    $db->insertRoom($number, $type, $status);

    $res->getBody()->write(json_encode([
        'message' => 'Room added successfully'
    ]));

    return $res
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});

$app->put('/room/{id}', function (Request $req, Response $res) {
    $id = $req->getAttribute('id');
    $roomNumber = $req->getParsedBody()['number'] ?? null;
    $roomType = $req->getParsedBody()['type'] ?? null;
    $roomStatus = $req->getParsedBody()['status'] ?? null;

    $db = getDB();
    $update = $db->updateRoomViaId($id, $roomNumber, $roomType, $roomStatus);

    if (!$update) {
        $res->getBody()->write(json_encode([
            'message' => 'Room not found'
        ]));

        return $res
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    $res->getBody()->write(json_encode([
        'message' => 'Room updated successfully'
    ]));
});


$app->delete('/room/{id}', function (Request $req, Response $res) {
    $db = getDB();

    $id = $req->getAttribute('id');

    $delete = $db->deleteRoomViaId($id);

    if (!$delete) {
        $res->getBody()->write(json_encode([
            'message' => 'Room not found'
        ]));

        return $res
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    $res->getBody()->write(json_encode([
        'message' => 'Room deleted successfully'
    ]));
});
