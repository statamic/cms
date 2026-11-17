<?php

namespace Tests\Imaging;

use Facades\Statamic\Imaging\ImageValidator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImageValidatorTest extends TestCase
{
    public function tearDown(): void
    {
        (function () {
            static::$extensionSupport = [];
        })->call(new \Statamic\Imaging\ImageValidator(\Mockery::mock(\Intervention\Image\Interfaces\DriverInterface::class)));

        parent::tearDown();
    }

    #[Test]
    public function it_checks_if_image_has_valid_extension_and_mimetype()
    {
        config(['statamic.assets.image_manipulation.driver' => 'gd']);

        // We'll test `isValidExtension()` functionality separately below, and just mock here...
        ImageValidator::shouldReceive('isValidExtension')->andReturnTrue()->times(24);
        ImageValidator::makePartial();

        $this->assertTrue(ImageValidator::isValidImage('jpg', 'image/jpeg'));
        $this->assertTrue(ImageValidator::isValidImage('jpg', 'image/pjpeg'));
        $this->assertTrue(ImageValidator::isValidImage('jpeg', 'image/jpeg'));
        $this->assertTrue(ImageValidator::isValidImage('jpeg', 'image/pjpeg'));
        $this->assertTrue(ImageValidator::isValidImage('png', 'image/png'));
        $this->assertTrue(ImageValidator::isValidImage('gif', 'image/gif'));
        $this->assertTrue(ImageValidator::isValidImage('webp', 'image/webp'));
        $this->assertTrue(ImageValidator::isValidImage('avif', 'image/avif'));
        $this->assertTrue(ImageValidator::isValidImage('tif', 'image/tiff'));
        $this->assertTrue(ImageValidator::isValidImage('bmp', 'image/bmp'));
        $this->assertTrue(ImageValidator::isValidImage('bmp', 'image/x-bmp'));
        $this->assertTrue(ImageValidator::isValidImage('bmp', 'image/x-ms-bmp'));
        $this->assertTrue(ImageValidator::isValidImage('svg', 'image/svg'));
        $this->assertTrue(ImageValidator::isValidImage('svg', 'image/svg+xml'));
        $this->assertTrue(ImageValidator::isValidImage('pdf', 'application/pdf'));
        $this->assertTrue(ImageValidator::isValidImage('pdf', 'application/acrobat'));
        $this->assertTrue(ImageValidator::isValidImage('pdf', 'image/pdf'));
        $this->assertTrue(ImageValidator::isValidImage('eps', 'application/postscript'));
        $this->assertTrue(ImageValidator::isValidImage('eps', 'image/x-eps'));

        // Show that mimetype validation catches improper mime types, for the purpose of catching malicious files...
        $this->assertFalse(ImageValidator::isValidImage('jpg', ''));
        $this->assertFalse(ImageValidator::isValidImage('jpg', null));
        $this->assertFalse(ImageValidator::isValidImage('jpg', 'application/octet-stream')); // exe file
        $this->assertFalse(ImageValidator::isValidImage('jpg', 'application/x-msdownload')); // exe file
        $this->assertFalse(ImageValidator::isValidImage('jpg', 'application/vnd.microsoft.portable-executable')); // exe file
    }

    #[Test]
    public function it_checks_if_image_extension_is_allowed_for_manipulation()
    {
        config(['statamic.assets.image_manipulation.driver' => 'gd']);

        $mock = \Mockery::mock(\Intervention\Image\Interfaces\DriverInterface::class);
        $mock->shouldReceive('supports')->with('one')->andReturnTrue();
        $mock->shouldReceive('supports')->with('two')->andReturnFalse();

        $imageValidator = new \Statamic\Imaging\ImageValidator($mock);

        $this->assertTrue($imageValidator->isValidExtension('one'));
        $this->assertFalse($imageValidator->isValidExtension('two'));
    }

    #[Test]
    public function it_does_not_ask_the_driver_about_universally_supported_formats()
    {
        $driver = \Mockery::mock(\Intervention\Image\Interfaces\DriverInterface::class);
        $driver->shouldNotReceive('supports');

        $validator = new \Statamic\Imaging\ImageValidator($driver);

        $this->assertTrue($validator->isValidExtension('jpg'));
        $this->assertTrue($validator->isValidExtension('JPG'));
        $this->assertTrue($validator->isValidExtension('jpeg'));
        $this->assertTrue($validator->isValidExtension('png'));
        $this->assertTrue($validator->isValidExtension('gif'));
    }

    #[Test]
    public function it_rejects_an_empty_extension_without_asking_the_driver()
    {
        $driver = \Mockery::mock(\Intervention\Image\Interfaces\DriverInterface::class);
        $driver->shouldNotReceive('supports');

        $validator = new \Statamic\Imaging\ImageValidator($driver);

        $this->assertFalse($validator->isValidExtension(''));
        $this->assertFalse($validator->isValidExtension(null));
    }

    #[Test]
    public function it_still_checks_the_mimetype_of_universally_supported_formats()
    {
        $driver = \Mockery::mock(\Intervention\Image\Interfaces\DriverInterface::class);
        $driver->shouldNotReceive('supports');

        $validator = new \Statamic\Imaging\ImageValidator($driver);

        $this->assertTrue($validator->isValidImage('jpg', 'image/jpeg'));
        $this->assertFalse($validator->isValidImage('jpg', 'application/x-msdownload'));
        $this->assertFalse($validator->isValidImage('png', 'image/jpeg'));
    }

    #[Test]
    public function it_only_asks_the_driver_about_an_extension_once()
    {
        $driver = \Mockery::mock(\Intervention\Image\Interfaces\DriverInterface::class);
        $driver->shouldReceive('supports')->with('avif')->once()->andReturnTrue();

        $validator = new \Statamic\Imaging\ImageValidator($driver);

        $this->assertTrue($validator->isValidExtension('avif'));
        $this->assertTrue($validator->isValidExtension('avif'));
        $this->assertTrue($validator->isValidExtension('AVIF'));
        $this->assertTrue((new \Statamic\Imaging\ImageValidator($driver))->isValidExtension('avif'));
    }
}
