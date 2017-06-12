<?php

namespace App\Services;

/**
 * Class CellType
 *
 * @package App\Services
 */
class CellType
{
    const TYPE_EMPTY = 0;
    const TYPE_WALL = -1;
    const TYPE_VISITED = -2;
    const TYPE_HIDDEN = -999;
    const TYPE_IN_PATH = PHP_INT_MAX;

    /**
     * Returns if a cell is empty
     *
     * @param int $cell
     * @param bool $alsoVisited
     * @return bool
     */
    public static function isEmpty($cell, $alsoVisited = false)
    {
        if ($cell == static::TYPE_EMPTY || $cell == static::TYPE_HIDDEN) {
            return true;
        }

        if ($alsoVisited && ($cell == static::TYPE_VISITED || $cell > 0)) {
            return true;
        }

        return false;
    }
}
