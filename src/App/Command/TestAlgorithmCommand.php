<?php

namespace App\Command;

use App\Services\Direction;
use App\Services\GameConditions;
use App\Services\MazePathFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TestAlgorithmCommand
 */
class TestAlgorithmCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('test-algorithm')
            ->setDescription('Test Algorithm');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input An InputInterface instance
     * @param \Symfony\Component\Console\Output\OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $body = '{"game":{"id":"test1"},"player":{"id":"test1","name":"Test Game","position":{"y":7,"x":18},'
            . '"previous":{"y":7,"x":18},"area":{"y1":2,"x1":14,"y2":12,"x2":19}},"maze":{"size":{"height":15,'
            . '"width":20},"goal":{"y":7,"x":0},"walls":[{"y":2,"x":14},{"y":2,"x":15},{"y":2,"x":17},'
            . '{"y":2,"x":18},{"y":2,"x":19},{"y":3,"x":14},{"y":3,"x":19},{"y":4,"x":19},{"y":5,"x":14},'
            . '{"y":5,"x":19},{"y":6,"x":14},{"y":6,"x":19},{"y":7,"x":14},{"y":7,"x":19},{"y":8,"x":14},'
            . '{"y":8,"x":19},{"y":9,"x":14},{"y":9,"x":19},{"y":10,"x":14},{"y":10,"x":15},{"y":10,"x":16},'
            . '{"y":10,"x":18},{"y":10,"x":19},{"y":11,"x":14},{"y":11,"x":19},{"y":12,"x":14},{"y":12,"x":19}]},'
            . '"ghosts":[]}';

        $data = new GameConditions($body);

        // Extract some vars
        $uuid = $data->uuid();
        $pos = $data->position();
        $prev = $data->previous();
        $walls = $data->walls();
        $height = $data->height();
        $width = $data->width();
        $goal = $data->goal();
        $ghosts = $data->ghosts();

        $maze = array();
        for ($y = 0; $y < $height; ++$y) {
            $maze[$y] = array();
            for ($x = 0; $x < $width; ++$x) {
                $maze[$y][$x] = 0;
            }
        }

        foreach ($walls as $wall) {
            $maze[$wall->y][$wall->x] = -1;
        }

        $finder = new MazePathFinder($maze, $height, $width, $goal);
        $move = $finder->nextMove($pos, $prev);

        echo 'Next move: ' . $move . PHP_EOL . PHP_EOL;

        return 0;
    }
}
