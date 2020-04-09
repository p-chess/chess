<?php

declare(strict_types=1);

namespace PChess\Chess\Benchmark;

use PChess\Chess\Chess;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;

/**
 * @BeforeMethods({"init"})
 */
final class MoveBench
{
    /**
     * @var Chess
     */
    private $chess;

    public function init(): void
    {
        $this->chess = new Chess();
    }

    /**
     * @Revs(1000)
     * @Iterations(5)
     */
    public function benchMove(): void
    {
        $this->chess->move('e4');
    }

    /**
     * @Revs(1000)
     * @Iterations(5)
     */
    public function benchMoves(): void
    {
        $this->chess->moves();
    }
}
