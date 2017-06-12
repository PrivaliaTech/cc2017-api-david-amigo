<?php

namespace App\Services;

/**
 * Class MovementDecider
 *
 * @package App\Services
 */
class MovementDecider
{
    /** @var PathFinder */
    private $pathFider;

    /** @var GhostDetector */
    private $ghostDetector;

    /** @var array */
    private $maze;

    /**
     * MazePathFinder constructor.
     *
     * @param PathFinder    $pathFider
     * @param GhostDetector $ghostDetector
     */
    public function __construct(PathFinder $pathFider, GhostDetector $ghostDetector)
    {
        $this->pathFider = $pathFider;
        $this->ghostDetector = $ghostDetector;
    }

    /**
     * @param array     $maze
     * @param array     $ghosts
     * @param int       $height
     * @param int       $width
     * @param \stdClass $goal
     * @param \stdClass $pos
     * @return array ["dir": {"pos":object, "cell": int, "alert": int, "iter": int, "maze"  => string|null]
     */
    public function getMovements(
        array $maze,
        array $ghosts,
        $height,
        $width,
        \stdClass $goal,
        \stdClass $pos
    ) {
        $this->maze = $maze;

        // Locate valid movements
        $moves = array();
        $directions = Direction::getDirectionsArray();
        foreach ($directions as $dir) {
            $newPos = Direction::nextPosition($pos, $dir);
            $cell = $this->maze[$newPos->y][$newPos->x];
            $alert = $this->ghostDetector->ghostAlert($newPos, $ghosts);
            if (CellType::isEmpty($cell, true)
                || ($newPos->y == $goal->y && $newPos->x == $goal->x)) {
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
            if ($data['pos']->y == $goal->y && $data['pos']->x == $goal->x) {
                $moves[$dir]['iter'] = 1;
                ++$count;
            } elseif (CellType::isEmpty($data['cell'])) {
                $iter = $this->pathFider->findPath($this->maze, $height, $width, $goal, $pos, $dir);
                $moves[$dir]['iter'] = $iter;
                if ($iter > 0) {
                    $moves[$dir]['maze'] = $this->pathFider->printMaze();
                    ++$count;
                }
            }
        }

        if (!$count) {
            // Clear saved path to re-start
            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if ($this->maze[$y][$x] == CellType::TYPE_IN_PATH) {
                        $this->maze[$y][$x] = CellType::TYPE_EMPTY;
                    }
                }
            }

            // Try to move (all cells are valid)
            foreach ($moves as $dir => $data) {
                $iter = $this->pathFider->findPath($this->maze, $height, $width, $goal, $pos, $dir);
                $moves[$dir]['cell'] = CellType::TYPE_EMPTY;
                $moves[$dir]['iter'] = $iter;
                if ($iter > 0) {
                    $moves[$dir]['maze'] = $this->pathFider->printMaze();
                    ++$count;
                } else {
                    $moves[$dir]['maze'] = null;
                }
            }
        }

        return $moves;
    }

    /**
     * Get modified maze
     *
     * @return array
     */
    public function getMaze()
    {
        return $this->maze;
    }
}
