<?php

namespace App\Services;

/**
 * Class GhostAvoider
 *
 * @package App\Services
 */
class GhostDetector
{
    /**
     * Alerts if ghost can reach a position
     *
     * @param \stdClass $pos
     * @param array $ghosts
     * @return int 0=normal, 1=warning, 2=alert
     */
    public function ghostAlert(\stdClass $pos, array $ghosts)
    {
        $level = 0;
        $directions = Direction::getDirectionsArray();
        foreach ($ghosts as $ghost) {
            if ($pos->y == $ghost->y && $pos->x == $ghost->x) {
                return 2;
            }
            foreach ($directions as $dir) {
                $newPos = Direction::nextPosition($ghost, $dir);
                if ($pos->y == $newPos->y && $pos->x == $newPos->x) {
                    $level = 1;
                }
            }
        }
        return $level;
    }
}
