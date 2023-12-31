<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/booking', function (Request $req, Response $res) {
    $db = getDB();

    $bookings = $db->getAllBookings();

    $res->getBody()->write(json_encode($bookings));

    return $res
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});


$app->get('/booking/{id}', function (Request $req, Response $res) {
    $db = getDB();
    $id = $req->getAttribute('id');

    $booking = $db->getBookingViaId($id);

    if (!$booking) {
        $res->getBody()->write(json_encode([
            'message' => 'Booking not found'
        ]));

        return $res
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    $res->getBody()->write(json_encode($booking));

    return $res
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->post('/booking', function (Request $req, Response $res) {
    $db =  getDB();
    $roomID = $req->getParsedBody()['roomId'] ?? null;
    $userID = $req->getParsedBody()['userId'] ?? null;

    $db->insertBooking($roomID, $userID, 0);

    $res->getBody()->write(json_encode([
        'message' => 'Booking added successfully'
    ]));

    return $res
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});

$app->put('/booking/{id}', function (Request $req, Response $res) {
    $db =  getDB();

    $id = $req->getAttribute('id');
    $roomID = $req->getParsedBody()['roomId'] ?? null;
    $userID = $req->getParsedBody()['userId'] ?? null;
    $status = $req->getParsedBody()['status'] ?? null;

    $db->updateBookingViaId($id, $roomID, $userID, $status);

    $res->getBody()->write(json_encode([
        'message' => 'Booking updated successfully'
    ]));

    return $res
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});


$app->delete('/booking/{id}', function (Request $req, Response $res) {
    $db =  getDB();

    $id = $req->getAttribute('id');

    $db->deleteBookingViaId($id);

    $res->getBody()->write(json_encode([
        'message' => 'Booking updated successfully'
    ]));

    return $res
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});
