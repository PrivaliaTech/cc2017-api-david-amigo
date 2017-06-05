<?php

namespace App\Services;

/**
 * Class MazePathFinder
 *
 * @package App\Services
 */
class PathFinder
{
    /** @var array */
    private $maze;

    /** @var int */
    private $height;

    /** @var int */
    private $width;

    /** @var \stdClass */
    private $goal;

    /** @var int */
    private $turns;

    /** @var int */
    private $iter;

    /**
     * Find the next movement
     *
     * @param array     $maze       Maze array (content)
     * @param int       $height     Height of the maze
     * @param int       $width      Width of the maze
     * @param \stdClass $goal       Goal position
     * @param \stdClass $position   Current position
     * @param int       $direction  Direction to move
     * @param int       $turns      Number of turns done
     * @return int                  Max inters
     */
    public function findPath(
        array $maze,
        $height,
        $width,
        \stdClass $goal,
        \stdClass $position,
        $direction,
        $turns = 4
    ) {
        $this->maze = $maze;
        $this->height = $height;
        $this->width = $width;
        $this->goal = $goal;
        $pos = clone $position;
        $dir = $direction;
        $this->turns = $turns;
        $this->iter = 1;

        while (1) {
            $dir = $this->findNextMove($pos, $dir);
            if ($dir == null) {
                return 0;
            }

            $pos = Direction::nextPosition($pos, $dir);
            if ($pos->y == $this->goal->y && $pos->x == $this->goal->x) {
                return $this->iter;
            }
        }
        return 0;
    }

    /**
     * Returns the maze in an string in print format
     *
     * @return string
     */
    public function printMaze()
    {
        $result = PHP_EOL . PHP_EOL;
        for ($y = 0; $y < $this->height; ++$y) {
            for ($x = 0; $x < $this->width; ++$x) {
                if ($y == $this->goal->y && $x == $this->goal->x) {
                    $result .= ' {}';
                } elseif ($y == 0 || $y == $this->height - 1) {
                    $result .= ' ##';
                } elseif ($x == 0 || $x == $this->width - 1) {
                    $result .= ' ##';
                } elseif ($this->maze[$y][$x] == CellType::TYPE_WALL) {
                    $result .= ' ##';
                } elseif ($this->maze[$y][$x] == CellType::TYPE_VISITED) {
                    $result .= ' **';
                } elseif ($this->maze[$y][$x] == CellType::TYPE_HIDDEN) {
                    $result .= ' ..';
                } elseif ($this->maze[$y][$x] == CellType::TYPE_IN_PATH) {
                    $result .= ' PP';
                } elseif ($this->maze[$y][$x] > 0) {
                    $result .= sprintf('%3d', $this->maze[$y][$x]);
                } else {
                    $result .= '   ';
                }
            }
            $result .= PHP_EOL;
        }
        $result .= PHP_EOL;
        return $result;
    }

    /**
     * Computes the next movement
     *
     * @param \stdClass $pos
     * @param string    $dir
     * @return string   Next move: up, down, left, right
     */
    private function findNextMove(\stdClass $pos, $dir)
    {
        if (CellType::isEmpty($this->maze[$pos->y][$pos->x])) {
            $this->maze[$pos->y][$pos->x] = $this->iter++;
        }

        // Array of movements
        $moves = Direction::getDirectionsArray();

        $forwardDir = $dir;
        $rightDir = $moves[(array_search($dir, $moves) + 1) % 4];
        $leftDir = $moves[(array_search($dir, $moves) + 3) % 4];
        $backDir = $moves[(array_search($dir, $moves) + 2) % 4];

        $forwardPos = Direction::nextPosition($pos, $forwardDir);
        $rightPos = Direction::nextPosition($pos, $rightDir);
        $leftPos = Direction::nextPosition($pos, $leftDir);
        $backPos = Direction::nextPosition($pos, $backDir);

        // If the goal is at a side, move to it
        if ($forwardPos->y == $this->goal->y && $forwardPos->x == $this->goal->x) {
            return $forwardDir;
        }

        if ($rightPos->y == $this->goal->y && $rightPos->x == $this->goal->x) {
            return $rightDir;
        }

        if ($leftPos->y == $this->goal->y && $leftPos->x == $this->goal->x) {
            return $leftDir;
        }

        if ($backPos->y == $this->goal->y && $backPos->x == $this->goal->x) {
            return $backDir;
        }

        // Now we can turn right or left or go forward depending on the score
        $forwardValid = $this->isValidPosition($forwardPos, true);
        $rightValid = $this->isValidPosition($rightPos, true);
        $leftValid = $this->isValidPosition($leftPos, true);

        if ($rightValid && $leftValid && $this->turns > 0) {
            $finder = new PathFinder();

            $rightIters = $finder->findPath(
                $this->maze,
                $this->height,
                $this->width,
                $this->goal,
                $pos,
                $rightDir,
                $this->turns / 2
            );

            $leftIters = $finder->findPath(
                $this->maze,
                $this->height,
                $this->width,
                $this->goal,
                $pos,
                $leftDir,
                $this->turns / 2
            );

            $this->turns--;

            if ($rightIters > 0 && $rightIters <= $leftIters) {
                return $rightDir;
            } elseif ($leftIters > 0) {
                return $leftDir;
            }
        }

        // Go forward if possible
        if ($forwardValid) {
            return $forwardDir;
        }

        // Turn right if possible
        if ($rightValid) {
            return $rightDir;
        }

        // Turn left if possible
        if ($leftValid) {
            return $leftDir;
        }

        // Else: go back
        $moves = array();

        $currentContent = $this->maze[$pos->y][$pos->x];
        $this->iter = $currentContent + 1;
        $this->maze[$pos->y][$pos->x] = CellType::TYPE_VISITED;

        if ($this->isValidPosition($forwardPos)) {
            $forwardContent = $this->maze[$forwardPos->y][$forwardPos->x];
            if ($forwardContent > 0 && $forwardContent < $currentContent) {
                $moves[$forwardContent] = $forwardDir;
            }
        }

        if ($this->isValidPosition($rightPos)) {
            $rightContent = $this->maze[$rightPos->y][$rightPos->x];
            if ($rightContent > 0 && $rightContent < $currentContent) {
                $moves[$rightContent] = $rightDir;
            }
        }

        if ($this->isValidPosition($leftPos)) {
            $leftContent = $this->maze[$leftPos->y][$leftPos->x];
            if ($leftContent > 0 && $leftContent < $currentContent) {
                $moves[$leftContent] = $leftDir;
            }
        }

        if ($this->isValidPosition($backPos)) {
            $backContent = $this->maze[$backPos->y][$backPos->x];
            if ($backContent > 0 && $backContent < $currentContent) {
                $moves[$backContent] = $backDir;
            }
        }

        if (!empty($moves)) {
            ksort($moves, SORT_NUMERIC);
            $moves = array_reverse($moves);
            return reset($moves);
        }

        return Direction::STOPPED;
    }

    /**
     * Checks if a position is valid
     *
     * @param \stdClass $pos
     * @param bool      $onlyEmpty
     * @return bool
     */
    private function isValidPosition(\stdClass $pos, $onlyEmpty = false)
    {
        if ($pos->y == $this->goal->y && $pos->x == $this->goal->x) {
            return true;
        }

        if ($pos->y < 1 || $pos->y > $this->height - 2) {
            return false;
        }

        if ($pos->x < 1 || $pos->x > $this->width - 2) {
            return false;
        }

        if ($this->maze[$pos->y][$pos->x] == CellType::TYPE_VISITED
            || $this->maze[$pos->y][$pos->x] == CellType::TYPE_WALL) {
            return false;
        }

        if ($onlyEmpty && $this->maze[$pos->y][$pos->x] > 0) {
            return false;
        }

        return true;
    }
}
