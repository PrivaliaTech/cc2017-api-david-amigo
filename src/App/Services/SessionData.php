<?php

namespace App\Services;

/**
 * Class SessionData
 *
 * @package App\Services
 */
class SessionData
{
    /** @var array */
    private $maze = array();

    /** @var int */
    private $yPos = -1;

    /** @var int */
    private $xPos = -1;

    /**
     * SessionData constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->decode($data);
    }

    /**
     * @return array
     */
    public function maze()
    {
        return $this->maze;
    }

    /**
     * @return int
     */
    public function yPos()
    {
        return $this->yPos;
    }

    /**
     * @return int
     */
    public function xPos()
    {
        return $this->xPos;
    }

    /**
     * @param array $maze
     * @param int   $yPos
     * @param int   $xPos
     * @return $this
     */
    public function init(array $maze = array(), $yPos = -1, $xPos = -1)
    {
        $this->maze = $maze;
        $this->yPos = $yPos;
        $this->xPos = $xPos;
        return $this;
    }

    /**
     * Decode data
     *
     * @param string $data
     * @return bool
     */
    public function decode($data)
    {
        $this->init();

        $obj = json_decode($data, false);
        if (!$obj) {
            return false;
        }

        if (!isset($obj->maze) || !isset($obj->yPos) || !isset($obj->xPos)) {
            return false;
        }

        $this->init($obj->maze, $obj->yPos, $obj->xPos);

        return true;
    }

    /**
     * Encode data
     *
     * @return string
     */
    public function encode()
    {
        return json_encode(array(
            'maze' => $this->maze,
            'yPos' => $this->yPos,
            'xPos' => $this->xPos
        ));
    }
}
