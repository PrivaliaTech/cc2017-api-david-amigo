<?php

use App\Services\GameConditions;
use App\Services\LocalStorage;
use App\Services\MazePathFinder;
use App\Services\SessionData;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();

$app->match('/name', function () use ($app) {
    return new JsonResponse(array(
        'name' => 'David Amigo',
        'email' => 'david.amigo@privalia.com'
    ));
});

$app->match('/move', function (Request $request) use ($app) {
    // Get the data form the request
    $body = $request->getContent();
    $data = new GameConditions($body);

    // Extract some vars
    $uuid = $data->uuid();
    $pos = $data->position();
    $prev = $data->previous();
    $walls = $data->walls();
    $height = $data->height();
    $width = $data->width();
    $goal = $data->goal();
    $ghosts = $data->ghosts();

    $session = new SessionData(
        LocalStorage::readData($uuid)
    );

    $maze = $session->maze();
    $yPos = $session->yPos();
    $xPos = $session->xPos();
    if (empty($maze) || $yPos != $pos->y || $xPos != $pos->x) {
        $maze = array();
        for ($y = 0; $y < $height; ++$y) {
            $maze[$y] = array();
            for ($x = 0; $x < $width; ++$x) {
                $maze[$y][$x] = 0;
            }
        }
    }

    // Add visible walls to the maze
    foreach ($walls as $wall) {
        $maze[$wall->y][$wall->x] = -1;
    }

    $finder = new MazePathFinder($maze, $height, $width, $goal);
    $move = $finder->nextMove($pos, $prev);
    $pos = $finder->nextPosition($pos, $move);

    $session->init($maze, $pos->y, $pos->x);
    LocalStorage::writeData($uuid, $session->encode());

    return new JsonResponse(array(
        'move' => $move,
        'debug' => $finder->printMaze(),
    ));
});

$app->run();
