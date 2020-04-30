<?php

namespace Tests\Facades;

use Statamic\Contracts\Imaging\ImageManipulator;
use Statamic\Facades\Image;
use Tests\TestCase;

class ImageTest extends TestCase
{
    public function testManipulatorIsReturned()
    {
        $this->assertInstanceOf(
            ImageManipulator::class,
            Image::manipulator()
        );
    }

    public function testManipulatorIsReturnedWhenNoItemIsPassed()
    {
        $this->assertInstanceOf(
            ImageManipulator::class,
            Image::manipulate()
        );
    }

    public function testManipulatorIsReturnedWhenNoParamsArePassed()
    {
        $this->assertInstanceOf(
            ImageManipulator::class,
            Image::manipulate('foo.jpg')
        );
    }

    public function testUrlIsReturnedWhenParamsAreSpecified()
    {
        $this->assertTrue(
            is_string(Image::manipulate('foo.jpg', ['w' => 100]))
        );
    }
}
