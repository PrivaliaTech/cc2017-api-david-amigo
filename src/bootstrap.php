<?php

use App\Services\CellType;
use App\Services\Direction;
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

    $finder = new MazePathFinder();
    $move = Direction::STOPPED;
    $printedMaze = null;
    $maxIter = -1;

    $directions = Direction::getDirectionsArray();
    foreach ($directions as $dir) {
        $new = Direction::nextPosition($pos, $dir);
        if ($maze[$new->y][$new->x] == CellType::TYPE_EMPTY) {
            $totalIter = $finder->findPath($maze, $height, $width, $goal, $pos, $dir);
            $printedMaze[] = $finder->printMaze();
            if ($totalIter > 0 && $maxIter < 0 || $totalIter < $maxIter) {
                $maxIter = $totalIter;
                $move = $dir;
            }
        }
    }

    if ($maxIter < 0) {
        for ($y = 0; $y < $height; ++$y) {
            for ($x = 0; $x < $width; ++$x) {
                if ($maze[$y][$x] == CellType::TYPE_IN_PATH) {
                    $maze[$y][$x] = CellType::TYPE_EMPTY;
                }
            }
        }

        foreach ($directions as $dir) {
            $new = Direction::nextPosition($pos, $dir);
            if ($maze[$new->y][$new->x] == CellType::TYPE_EMPTY) {
                $totalIter = $finder->findPath($maze, $height, $width, $goal, $pos, $dir);
                $printedMaze[] = $finder->printMaze();
                if ($maxIter < 0 || $totalIter < $maxIter) {
                    $maxIter = $totalIter;
                    $move = $dir;
                }
            }
        }
    }

    $maze[$pos->y][$pos->x] = CellType::TYPE_IN_PATH;
    $pos = Direction::nextPosition($pos, $move);

    $session->init($maze, $pos->y, $pos->x);
    LocalStorage::writeData($uuid, $session->encode());

    $time = microtime(true) - $time;

    return new JsonResponse(array(
        'move' => $move,
        'debug' => $printedMaze,
        'time' => sprintf('%.8f', $time)
    ));
});

$app->run();
