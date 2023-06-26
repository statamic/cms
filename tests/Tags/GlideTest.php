<?php

namespace Tests\Tags;

use Illuminate\Http\UploadedFile;
use Statamic\Facades\File;
use Statamic\Facades\Parse;
use Tests\TestCase;

class GlideTest extends TestCase
{
    /**
     * @test
     *
     * @define-env relativeRouteUrl
     */
    public function it_outputs_a_relative_url_by_default_when_the_glide_route_is_relative()
    {
        $this->createImageInPublicDirectory();

        $this->assertEquals('/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2.jpg', $this->absoluteTestTag());
        $this->assertEquals('/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2.jpg', $this->absoluteTestTag(false));
        $this->assertEquals('http://localhost/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2.jpg', $this->absoluteTestTag(true));
    }

    /**
     * @test
     *
     * @define-env absoluteHttpRouteUrl
     */
    public function it_outputs_an_absolute_url_by_default_when_the_glide_route_is_absolute_http()
    {
        $this->createImageInPublicDirectory();

        $this->assertEquals('http://localhost/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2.jpg', $this->absoluteTestTag());
        $this->assertEquals('/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2.jpg', $this->absoluteTestTag(false));
        $this->assertEquals('http://localhost/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2.jpg', $this->absoluteTestTag(true));
    }

    /**
     * @test
     *
     * @define-env absoluteHttpsRouteUrl
     */
    public function it_outputs_an_absolute_url_by_default_when_the_glide_route_is_absolute_https()
    {
        $this->createImageInPublicDirectory();

        $this->assertEquals('https://localhost/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2.jpg', $this->absoluteTestTag());
        $this->assertEquals('/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2.jpg', $this->absoluteTestTag(false));
        $this->assertEquals('https://localhost/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2.jpg', $this->absoluteTestTag(true));
    }

    /**
     * @test
     */
    public function it_outputs_a_data_url()
    {
        $this->createImageInPublicDirectory();

        $tag = <<<'EOT'
{{ glide:data_url :src="foo" }}
EOT;

        $this->assertStringStartsWith('data:image/jpeg;base64', (string) Parse::template($tag, ['foo' => 'bar.jpg']));
    }

    public function relativeRouteUrl($app)
    {
        $this->configureGlideCacheDiskWithUrl($app, '/glide');
    }

    public function absoluteHttpRouteUrl($app)
    {
        $this->configureGlideCacheDiskWithUrl($app, 'http://localhost/glide');
    }

    public function absoluteHttpsRouteUrl($app)
    {
        $this->configureGlideCacheDiskWithUrl($app, 'https://localhost/glide');
    }

    private function configureGlideCacheDiskWithUrl($app, $url)
    {
        $app['config']->set('filesystems.disks.glide', [
            'driver' => 'local',
            'root' => public_path('glide'),
            'url' => $url,
            'visibility' => 'public',
        ]);
        $app['config']->set('statamic.assets.image_manipulation.cache', 'glide');
    }

    private function createImageInPublicDirectory()
    {
        $file = UploadedFile::fake()->image('bar.jpg');
        File::put(public_path('bar.jpg'), File::get($file->getPathname()));
    }

    private function absoluteTestTag($absolute = null)
    {
        $absoluteParam = '';

        if ($absolute) {
            $absoluteParam = 'absolute="true"';
        } elseif ($absolute === false) {
            $absoluteParam = 'absolute="false"';
        }

        $tag = <<<EOT
{{ glide:foo width="100" $absoluteParam }}
EOT;

        return (string) Parse::template($tag, ['foo' => 'bar.jpg']);
    }
}
