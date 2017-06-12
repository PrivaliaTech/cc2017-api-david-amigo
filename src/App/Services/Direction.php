<?php

namespace App\Services;

/**
 * Class Direction
 *
 * @package App\Services
 */
class Direction
{
    const UP = 'up';
    const DOWN = 'down';
    const LEFT = 'left';
    const RIGHT = 'right';
    const STOPPED = null;

    /**
     * Get directions array
     *
     * @return array
     */
    public static function getDirectionsArray()
    {
        return array(
            Direction::UP,
            Direction::RIGHT,
            Direction::DOWN,
            Direction::LEFT
        );
    }

    /**
     * Computes a direction using two positions.
     *
     * @param \stdClass $pos
     * @param \stdClass $prev
     * @return string
     */
    public static function computeDirection(\stdClass $pos, \stdClass $prev)
    {
        $dir = null;
        if ($pos->y < $prev->y) {
            $dir = Direction::UP;
        } elseif ($pos->y > $prev->y) {
            $dir = Direction::DOWN;
        } elseif ($pos->x < $prev->x) {
            $dir = Direction::LEFT;
        } elseif ($pos->x > $prev->x) {
            $dir = Direction::RIGHT;
        } else {
            $dir = Direction::STOPPED;
        }
        return $dir;
    }

    /**
     * Computes the next position
     *
     * @param \stdClass $pos
     * @param string    $dir
     * @return \stdClass
     */
    public static function nextPosition(\stdClass $pos, $dir)
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
