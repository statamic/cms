<?php

namespace Tests\Imaging\Manipulators;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Imaging\Manipulators\CloudflareManipulator;
use Tests\TestCase;

class CloudflareManipulatorTest extends TestCase
{
    private CloudflareManipulator $manipulator;

    public function setUp(): void
    {
        parent::setUp();

        $this->manipulator = new CloudflareManipulator;
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
            'gravity' => '0.1x0.2', // z not supported
        ], $this->manipulator->getParams());
    }
}
