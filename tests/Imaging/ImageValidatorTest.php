<?php

namespace Tests\Imaging;

use Facades\Statamic\Imaging\ImageValidator;
use Tests\TestCase;

class ImageValidatorTest extends TestCase
{
    /** @test */
    public function it_checks_if_image_has_valid_extension_and_mimetype()
    {
        config(['statamic.assets.image_manipulation.driver' => 'imagick']);

        config(['statamic.assets.image_manipulation.additional_extensions' => [
            'svg',
            'pdf',
            'eps',
        ]]);

        // We'll test `isAllowedExtension()` functionality separately below, and just mock here...
        ImageValidator::shouldReceive('isAllowedExtension')->andReturnTrue()->times(23);
        ImageValidator::makePartial();

        $this->assertTrue(ImageValidator::isValidImage('jpg', 'image/jpeg'));
        $this->assertTrue(ImageValidator::isValidImage('jpg', 'image/pjpeg'));
        $this->assertTrue(ImageValidator::isValidImage('jpeg', 'image/jpeg'));
        $this->assertTrue(ImageValidator::isValidImage('jpeg', 'image/pjpeg'));
        $this->assertTrue(ImageValidator::isValidImage('png', 'image/png'));
        $this->assertTrue(ImageValidator::isValidImage('gif', 'image/gif'));
        $this->assertTrue(ImageValidator::isValidImage('webp', 'image/webp'));
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

    /** @test */
    public function it_checks_if_image_extension_is_allowed_for_manipulation_with_gd_driver()
    {
        config(['statamic.assets.image_manipulation.driver' => 'gd']);

        $this->assertTrue(ImageValidator::isAllowedExtension('jpeg'));
        $this->assertTrue(ImageValidator::isAllowedExtension('jpg'));
        $this->assertTrue(ImageValidator::isAllowedExtension('png'));
        $this->assertTrue(ImageValidator::isAllowedExtension('gif'));
        $this->assertTrue(ImageValidator::isAllowedExtension('webp'));

        // Supported by imagick only...
        $this->assertFalse(ImageValidator::isAllowedExtension('tif'));
        $this->assertFalse(ImageValidator::isAllowedExtension('bmp'));
        $this->assertFalse(ImageValidator::isAllowedExtension('psd'));

        // Supported by imagick only, but requires `additional_extensions` configuration...
        $this->assertFalse(ImageValidator::isAllowedExtension('svg'));
        $this->assertFalse(ImageValidator::isAllowedExtension('pdf'));
        $this->assertFalse(ImageValidator::isAllowedExtension('eps'));
    }

    /** @test */
    public function it_checks_if_image_extension_is_allowed_for_manipulation_with_imagick_driver()
    {
        config(['statamic.assets.image_manipulation.driver' => 'imagick']);

        $this->assertTrue(ImageValidator::isAllowedExtension('jpeg'));
        $this->assertTrue(ImageValidator::isAllowedExtension('jpg'));
        $this->assertTrue(ImageValidator::isAllowedExtension('png'));
        $this->assertTrue(ImageValidator::isAllowedExtension('gif'));
        $this->assertTrue(ImageValidator::isAllowedExtension('webp'));
        $this->assertTrue(ImageValidator::isAllowedExtension('tif'));
        $this->assertTrue(ImageValidator::isAllowedExtension('bmp'));
        $this->assertTrue(ImageValidator::isAllowedExtension('psd'));

        // Supported by imagick, but requires `additional_extensions` configuration...
        $this->assertFalse(ImageValidator::isAllowedExtension('svg'));
        $this->assertFalse(ImageValidator::isAllowedExtension('pdf'));
        $this->assertFalse(ImageValidator::isAllowedExtension('eps'));
    }

    /** @test */
    public function it_checks_if_custom_image_extension_is_allowed_for_manipulation_with_proper_config()
    {
        config(['statamic.assets.image_manipulation.driver' => 'imagick']);

        config(['statamic.assets.image_manipulation.additional_extensions' => [
            'svg',
            'pdf',
            'eps',
        ]]);

        $this->assertTrue(ImageValidator::isAllowedExtension('jpeg'));
        $this->assertTrue(ImageValidator::isAllowedExtension('jpg'));
        $this->assertTrue(ImageValidator::isAllowedExtension('png'));
        $this->assertTrue(ImageValidator::isAllowedExtension('gif'));
        $this->assertTrue(ImageValidator::isAllowedExtension('webp'));
        $this->assertTrue(ImageValidator::isAllowedExtension('tif'));
        $this->assertTrue(ImageValidator::isAllowedExtension('bmp'));
        $this->assertTrue(ImageValidator::isAllowedExtension('psd'));

        // Should now be supported due to `additional_extensions` config...
        $this->assertTrue(ImageValidator::isAllowedExtension('svg'));
        $this->assertTrue(ImageValidator::isAllowedExtension('pdf'));
        $this->assertTrue(ImageValidator::isAllowedExtension('eps'));

        // Not configured, should still be false...
        $this->assertFalse(ImageValidator::isAllowedExtension('exe'));
    }
}
