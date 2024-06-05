<?php

namespace Tests\Imaging\Manipulators;

use Statamic\Imaging\Manipulators\GlideManipulator;
use Statamic\Imaging\Manipulators\ImgixManipulator;
use Statamic\Imaging\Manipulators\Manager;
use Tests\TestCase;

class ManagerTest extends TestCase
{
    private $manager;

    public function setUp(): void
    {
        parent::setUp();
        $this->manager = new Manager($this->app);
    }

    /** @test */
    public function it_gets_the_default_manipulator()
    {
        config(['statamic.image_manipulation.default' => 'glide']);

        $driver = $this->manager->manipulator();

        $this->assertInstanceOf(GlideManipulator::class, $driver);
    }

    /** @test */
    public function it_gets_an_explicit_manipulator()
    {
        $driver = $this->manager->manipulator('imgix');

        $this->assertInstanceOf(ImgixManipulator::class, $driver);
    }

    /** @test */
    public function custom_drivers_can_added()
    {
        $this->manager->extend('foo', fn () => new CustomManipulator);

        $caught = false;
        try {
            $this->manager->manipulator('test');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Image manipulator [test] is not defined.', $e->getMessage());
            $caught = true;
        }

        if (! $caught) {
            $this->fail('Expected an exception to be thrown.');
        }

        config(['statamic.image_manipulation.manipulators.test' => ['driver' => 'foo']]);

        $this->assertInstanceOf(CustomManipulator::class, $this->manager->manipulator('test'));
    }
}

class CustomManipulator extends GlideManipulator
{
    //
}
