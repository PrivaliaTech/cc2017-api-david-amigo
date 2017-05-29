<?php

namespace App\Services;

/**
 * Class MazePathFinder
 *
 * @package App\Services
 */
class MazePathFinder
{
    /** @var array */
    private $maze;

    /** @var int */
    private $height;

    /** @var int */
    private $width;

    /** @var \stdClass */
    private $goal;

    /**
     * MazePathFinder constructor.
     * @param array     $maze
     * @param int       $height
     * @param int       $width
     * @param \stdClass $goal
     */
    public function __construct(
        array $maze,
        $height,
        $width,
        \stdClass $goal
    ) {
        $this->maze = $maze;
        $this->height = $height;
        $this->width = $width;
        $this->goal = $goal;
    }


    /**
     * Find the next movement
     *
     * @param \stdClass $position
     * @param \stdClass $previous
     * @return string Next move: up, down, left, right
     */
    public function nextMove(\stdClass $position, \stdClass $previous)
    {
        $iter = 1;
        $pos = $position;

        $dir = Direction::computeDirection($position, $previous);
        if (!$dir) {
            $dir = Direction::computeDirection($this->goal, $position);
        }

        do {
            if ($this->maze[$pos->y][$pos->x] == 0) {
                $this->maze[$pos->y][$pos->x] = $iter++;
            }

            $dir = $this->findNextMove($pos, $dir);

            $pos = $this->nextPosition($pos, $dir);

            for ($y = 0; $y < $this->height; ++$y) {
                for ($x = 0; $x < $this->width; ++$x) {
                    if ($y == $this->goal->y && $x == $this->goal->x) {
                        echo '{} ';
                    } elseif ($y == 0 || $y == $this->height - 1) {
                        echo '## ';
                    } elseif ($x == 0 || $x == $this->width - 1) {
                        echo '## ';
                    } elseif ($this->maze[$y][$x] == -1) {
                        echo '## ';
                    } elseif ($this->maze[$y][$x] == -2) {
                        echo '** ';
                    } elseif ($this->maze[$y][$x] > 0) {
                        echo sprintf('%02d ', $this->maze[$y][$x]);
                    } else {
                        echo '   ';
                    }
                }
                echo PHP_EOL;
            }
Usleep(250000);echo PHP_EOL;
        } while ($dir && ($pos->y != $this->goal->y || $pos->x != $this->goal->x));

        return Direction::UP;
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
        // Array of movements
        $moves = Direction::getDirectionsArray();

        $forwardDir = $dir;
        $rightDir = $moves[(array_search($dir, $moves) + 1) % 4];
        $leftDir = $moves[(array_search($dir, $moves) + 3) % 4];
        $backDir = $moves[(array_search($dir, $moves) + 2) % 4];

        $forwardPos = $this->nextPosition($pos, $forwardDir);
        $rightPos = $this->nextPosition($pos, $rightDir);
        $leftPos = $this->nextPosition($pos, $leftDir);
        $backPos = $this->nextPosition($pos, $backDir);

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

        // Go forward if possible
        if ($this->isValidPosition($forwardPos, true)) {
            return $forwardDir;
        }

        // Turn right if possible
        if ($this->isValidPosition($rightPos, true)) {
            return $rightDir;
        }

        // Turn left if possible
        if ($this->isValidPosition($leftPos, true)) {
            return $leftDir;
        }

        // Else: go back
        $moves = array();

        $currentContent = $this->maze[$pos->y][$pos->x];
        $this->maze[$pos->y][$pos->x] = -2;

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

        if ($this->maze[$pos->y][$pos->x] < 0) {
            return false;
        }

        if ($onlyEmpty && $this->maze[$pos->y][$pos->x] != 0) {
            return false;
        }

        return true;
    }

    /**
     * Computes the next position
     *
     * @param \stdClass $pos
     * @param string    $dir
     * @return \stdClass
     */
    private function nextPosition(\stdClass $pos, $dir)
    {
        $new = clone $pos;
        switch ($dir) {
            case Direction::UP:
                --$new->y;
                break;

            case Direction::DOWN:
                ++$new->y;
                break;

            case Direction::LEFT:
                --$new->x;
                break;

            case Direction::RIGHT:
                ++$new->x;
                break;
        }
        return $new;
    }
}
