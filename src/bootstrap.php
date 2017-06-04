<?php

use App\Services\CellType;
use App\Services\Direction;
use App\Services\GameConditions;
use App\Services\GhostDetector;
use App\Services\LocalStorage;
use App\Services\MovementDecider;
use App\Services\PathFinder;
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
    $time = microtime(true);

    // Get the data form the request
    $body = $request->getContent();
    $data = new GameConditions($body);

    // Extract some vars
    $uuid = $data->uuid();
    $pos = $data->position();
    $prev = $data->previous();
    $walls = $data->walls();
    $area = $data->area();
    $height = $data->height();
    $width = $data->width();
    $goal = $data->goal();
    $ghosts = $data->ghosts();

    // Read data from session
    $session = new SessionData(
        LocalStorage::readData($uuid)
    );

    $maze = $session->maze();
    $yPos = $session->yPos();
    $xPos = $session->xPos();

    // Create the maze
    if (empty($maze) || $yPos != $pos->y || $xPos != $pos->x) {
        $maze = array();
        for ($y = 0; $y < $height; ++$y) {
            $maze[$y] = array();
            for ($x = 0; $x < $width; ++$x) {
                $maze[$y][$x] = CellType::TYPE_HIDDEN;
            }
        }
    }

    // Discover the maze (visible area)
    for ($y = $area->y1; $y <= $area->y2; $y++) {
        for ($x = $area->x1; $x <= $area->x2; $x++) {
            if ($maze[$y][$x] == CellType::TYPE_HIDDEN) {
                $maze[$y][$x] = CellType::TYPE_EMPTY;
            }
        }
    }

    // Add visible walls to the maze
    foreach ($walls as $wall) {
        $maze[$wall->y][$wall->x] = CellType::TYPE_WALL;
    }

    $pathFinder = new PathFinder();
    $ghostDetector = new GhostDetector();
    $movementDecider = new MovementDecider(
        $pathFinder,
        $ghostDetector
    );

    // Locate valid movements
    $moves = $movementDecider->getMovements(
        $maze,
        $ghosts,
        $height,
        $width,
        $goal,
        $pos
    );

    $maze = $movementDecider->getMaze();

    // Find more optimized movement (without ghosts)
    $minIter = PHP_INT_MAX;
    $move = Direction::STOPPED;
    foreach ($moves as $dir => $data) {
        if ($data['alert'] == 0 && $data['iter'] > 0 && $data['iter'] < $minIter) {
            $minIter = $data['iter'];
            $move = $dir;
        }
    }

    // Escape from the ghosts
    if ($move == Direction::STOPPED){
        foreach ($moves as $dir => $data) {
            if ($data['alert'] == 0 || ($data['alert'] == 1 && $move == Direction::STOPPED)) {
                $move = $dir;
            }
        }
    }

    // Mark position as visited as checked
    $maze[$pos->y][$pos->x] = CellType::TYPE_IN_PATH;

    // Save session data
    $newPos = $moves[$move]['pos'];
    $session->init($maze, $newPos->y, $newPos->x);
    LocalStorage::writeData($uuid, $session->encode());

    $time = microtime(true) - $time;

    return new JsonResponse(array(
        'move' => $move,
        'maze' => $moves,
        'time' => sprintf('%.8f', $time)
    ));
});

$app->run();
