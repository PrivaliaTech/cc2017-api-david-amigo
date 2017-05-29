<?php

namespace App\Services;

/**
 * Class GameConditions
 *
 * @package App\Services
 */
class GameConditions
{
    /** @var \stdClass */
    private $data;

    /**
     * GameConditions constructor.
     *
     * @param $body
     * @throws \HttpException
     */
    public function __construct($body)
    {
        $this->data = json_decode($body);
        if (false === $this->data) {
            throw new \HttpException('Invalid request data!', 400);
        }
    }

    /**
     * @return string
     */
    public function uuid()
    {
        return $this->data->player->id;
    }

    /**
     * @return int
     */
    public function height()
    {
        return $this->data->maze->size->height;
    }

    /**
     * @return int
     */
    public function width()
    {
        return $this->data->maze->size->width;
    }

    /**
     * @return array
     */
    public function walls()
    {
        return $this->data->maze->walls;
    }

    /**
     * @return array
     */
    public function ghosts()
    {
        return $this->data->ghosts;
    }

    /**
     * @return \stdClass
     */
    public function goal()
    {
        return $this->data->maze->goal;
    }

    /**
     * @return \stdClass
     */
    public function position()
    {
        return $this->data->player->position;
    }

    /**
     * @return \stdClass
     */
    public function previous()
    {
        return $this->data->player->previous;
    }
}
