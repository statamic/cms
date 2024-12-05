<?php

namespace Tests\Imaging;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Imaging\ImageManipulator;
use Statamic\Imaging\Manager;
use Tests\TestCase;

class ManagerTest extends TestCase
{
    private $manager;

    public function setUp(): void
    {
        parent::setUp();
        $this->manager = new Manager;
    }

    #[Test]
    public function manipulator_is_returned()
    {
        $this->assertInstanceOf(
            ImageManipulator::class,
            $this->manager->manipulator()
        );
    }

    #[Test]
    public function manipulator_is_returned_when_no_item_is_passed()
    {
        $this->assertInstanceOf(
            ImageManipulator::class,
            $this->manager->manipulate()
        );
    }

    #[Test]
    public function manipulator_is_returned_when_no_params_are_passed()
    {
        $this->assertInstanceOf(
            ImageManipulator::class,
            $this->manager->manipulate('foo.jpg')
        );
    }

    #[Test]
    public function url_is_returned_when_params_are_specified()
    {
        $this->assertTrue(
            is_string($this->manager->manipulate('foo.jpg', ['w' => 100]))
        );
    }

    #[Test]
    public function it_gets_manipulation_presets()
    {
        config(['statamic.assets.image_manipulation.presets' => [
            'alfa' => ['w' => 100, 'h' => 200, 'q' => 50, 'fit' => 'crop_focal'], // fit of crop_focal gets removed
            'bravo' => ['width' => 200, 'height' => 100, 'quality' => 20], // aliases get resolved
        ]]);

        $this->assertEquals([
            'alfa' => ['w' => 100, 'h' => 200, 'q' => 50],
            'bravo' => ['w' => 200, 'h' => 100, 'q' => 20],
        ], $this->manager->userManipulationPresets());

        $this->assertEquals([
            'cp_thumbnail_small_landscape' => ['w' => '400', 'h' => '400', 'fit' => 'contain'],
            'cp_thumbnail_small_portrait' => ['h' => '400', 'fit' => 'contain'],
            'cp_thumbnail_small_square' => ['w' => '400', 'h' => '400'],
        ], $this->manager->cpManipulationPresets());

        $this->assertEquals([
            'alfa' => ['w' => 100, 'h' => 200, 'q' => 50],
            'bravo' => ['w' => 200, 'h' => 100, 'q' => 20],
            'cp_thumbnail_small_landscape' => ['w' => '400', 'h' => '400', 'fit' => 'contain'],
            'cp_thumbnail_small_portrait' => ['h' => '400', 'fit' => 'contain'],
            'cp_thumbnail_small_square' => ['w' => '400', 'h' => '400'],
        ], $this->manager->manipulationPresets());
    }
}
