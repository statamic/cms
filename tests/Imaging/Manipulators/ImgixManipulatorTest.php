<?php

namespace Tests\Imaging\Manipulators;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Imaging\Manipulators\ImgixManipulator;
use Tests\TestCase;

class ImgixManipulatorTest extends TestCase
{
    private ImgixManipulator $manipulator;

    public function setUp(): void
    {
        parent::setUp();

        $this->manipulator = new ImgixManipulator;
    }

    #[Test]
    public function it_sets_focal_point_parameters()
    {
        $this->manipulator
            ->addFocalPointParams(10, 20, 2)
            ->addParams(['w' => 100, 'h' => 200]);

        $this->assertEquals([
            'w' => 100,
            'h' => 200,
            'fit' => 'crop',
            'crop' => 'focalpoint',
            'fp-x' => 0.1,
            'fp-y' => 0.2,
            'fp-z' => 2,
        ], $this->manipulator->getParams());
    }
}
