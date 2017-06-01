<?php

use App\Services\CellType;
use App\Services\Direction;
use App\Services\GameConditions;
use App\Services\GhostDetector;
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
    $detector = new GhostDetector();

    // Locate valid movements
    $moves = array();
    $directions = Direction::getDirectionsArray();
    foreach ($directions as $dir) {
        $newPos = Direction::nextPosition($pos, $dir);
        $cell = $maze[$newPos->y][$newPos->x];
        $alert = $detector->ghostAlert($newPos, $ghosts);
        if (CellType::isEmpty($cell, true)) {
            $moves[$dir] = array(
                'pos'   => $newPos,
                'cell'  => $cell,
                'alert' => $alert,
                'iter'  => -1,
                'maze'  => null
            );
        }
    }

    // Try to move (only to unvisited cells)
    $count = 0;
    foreach ($moves as $dir => $data) {
        if (CellType::isEmpty($data['cell'])) {
            $iter = $finder->findPath($maze, $height, $width, $goal, $pos, $dir);
            $moves[$dir]['iter']= $iter;
            if ($iter) {
                $moves[$dir]['maze'] = $finder->printMaze();
                ++$count;
            }
        }
    }

    if (!$count) {
        // Clear saved path to re-start
        for ($y = 0; $y < $height; ++$y) {
            for ($x = 0; $x < $width; ++$x) {
                if ($maze[$y][$x] == CellType::TYPE_IN_PATH) {
                    $maze[$y][$x] = CellType::TYPE_EMPTY;
                }
            }
        }

        // Try to move (all cells are valid)
        foreach ($moves as $dir => $data) {
            $iter = $finder->findPath($maze, $height, $width, $goal, $pos, $dir);
            $moves[$dir]['iter']= $iter;
            if ($iter) {
                $moves[$dir]['maze'] = null;
            } else {
                $moves[$dir]['maze'] = $finder->printMaze();
                ++$count;
            }
        }
    }

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
    $pos = $moves[$move]['pos'];
    $session->init($maze, $pos->y, $pos->x);
    LocalStorage::writeData($uuid, $session->encode());

    $time = microtime(true) - $time;

    return new JsonResponse(array(
        'move' => $move,
        'maze' => $moves,
        'time' => sprintf('%.8f', $time)
    ));
});

$app->run();
