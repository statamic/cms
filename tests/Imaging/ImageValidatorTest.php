<?php

namespace Tests\Imaging;

use Facades\Statamic\Imaging\ImageValidator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImageValidatorTest extends TestCase
{
    #[Test]
    public function it_checks_if_image_has_valid_extension_and_mimetype()
    {
        config(['statamic.assets.image_manipulation.driver' => 'imagick']);

        config(['statamic.assets.image_manipulation.additional_extensions' => [
            'svg',
            'pdf',
            'eps',
        ]]);

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
    public function it_checks_if_image_extension_is_allowed_for_manipulation_with_gd_driver()
    {
        config(['statamic.assets.image_manipulation.driver' => 'gd']);

        $this->assertTrue(ImageValidator::isValidExtension('jpeg'));
        $this->assertTrue(ImageValidator::isValidExtension('jpg'));
        $this->assertTrue(ImageValidator::isValidExtension('png'));
        $this->assertTrue(ImageValidator::isValidExtension('gif'));
        $this->assertTrue(ImageValidator::isValidExtension('webp'));

        // Supported by imagick only...
        $this->assertFalse(ImageValidator::isValidExtension('tif'));
        $this->assertFalse(ImageValidator::isValidExtension('bmp'));
        $this->assertFalse(ImageValidator::isValidExtension('psd'));

        // Supported by imagick only, but requires `additional_extensions` configuration...
        $this->assertFalse(ImageValidator::isValidExtension('svg'));
        $this->assertFalse(ImageValidator::isValidExtension('pdf'));
        $this->assertFalse(ImageValidator::isValidExtension('eps'));
    }

    #[Test]
    public function it_checks_if_image_extension_is_allowed_for_manipulation_with_imagick_driver()
    {
        config(['statamic.assets.image_manipulation.driver' => 'imagick']);

        $this->assertTrue(ImageValidator::isValidExtension('jpeg'));
        $this->assertTrue(ImageValidator::isValidExtension('jpg'));
        $this->assertTrue(ImageValidator::isValidExtension('png'));
        $this->assertTrue(ImageValidator::isValidExtension('gif'));
        $this->assertTrue(ImageValidator::isValidExtension('webp'));
        $this->assertTrue(ImageValidator::isValidExtension('tif'));
        $this->assertTrue(ImageValidator::isValidExtension('bmp'));
        $this->assertTrue(ImageValidator::isValidExtension('psd'));

        // Supported by imagick, but requires `additional_extensions` configuration...
        $this->assertFalse(ImageValidator::isValidExtension('svg'));
        $this->assertFalse(ImageValidator::isValidExtension('pdf'));
        $this->assertFalse(ImageValidator::isValidExtension('eps'));
        $this->assertFalse(ImageValidator::isValidExtension('avif'));
    }

    #[Test]
    public function it_checks_if_image_extension_is_allowed_for_manipulation_with_libvips_driver()
    {
        config(['statamic.assets.image_manipulation.driver' => 'libvips']);

        $this->assertTrue(ImageValidator::isValidExtension('jpeg'));
        $this->assertTrue(ImageValidator::isValidExtension('jpg'));
        $this->assertTrue(ImageValidator::isValidExtension('png'));
        $this->assertTrue(ImageValidator::isValidExtension('gif'));
        $this->assertTrue(ImageValidator::isValidExtension('webp'));
        $this->assertTrue(ImageValidator::isValidExtension('tif'));

        // Not supported by libvips...
        $this->assertFalse(ImageValidator::isValidExtension('bmp'));
        $this->assertFalse(ImageValidator::isValidExtension('psd'));
        $this->assertFalse(ImageValidator::isValidExtension('eps'));

        // Supported by libvips, but requires `additional_extensions` configuration...
        $this->assertFalse(ImageValidator::isValidExtension('svg'));
        $this->assertFalse(ImageValidator::isValidExtension('pdf'));
        $this->assertFalse(ImageValidator::isValidExtension('avif'));
    }

    #[Test]
    public function it_checks_if_custom_image_extension_is_allowed_for_manipulation_with_proper_config()
    {
        config(['statamic.assets.image_manipulation.driver' => 'imagick']);

        config(['statamic.assets.image_manipulation.additional_extensions' => [
            'svg',
            'pdf',
            'eps',
            'avif',
        ]]);

        $this->assertTrue(ImageValidator::isValidExtension('jpeg'));
        $this->assertTrue(ImageValidator::isValidExtension('jpg'));
        $this->assertTrue(ImageValidator::isValidExtension('png'));
        $this->assertTrue(ImageValidator::isValidExtension('gif'));
        $this->assertTrue(ImageValidator::isValidExtension('webp'));
        $this->assertTrue(ImageValidator::isValidExtension('tif'));
        $this->assertTrue(ImageValidator::isValidExtension('bmp'));
        $this->assertTrue(ImageValidator::isValidExtension('psd'));

        // Should now be supported due to `additional_extensions` config...
        $this->assertTrue(ImageValidator::isValidExtension('svg'));
        $this->assertTrue(ImageValidator::isValidExtension('pdf'));
        $this->assertTrue(ImageValidator::isValidExtension('eps'));
        $this->assertTrue(ImageValidator::isValidExtension('avif'));

        // Not configured, should still be false...
        $this->assertFalse(ImageValidator::isValidExtension('exe'));
    }
}
