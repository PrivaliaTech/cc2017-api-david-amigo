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
}
