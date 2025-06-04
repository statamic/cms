<?php

namespace Tests\Tags;

use Illuminate\Http\UploadedFile;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\File;
use Statamic\Facades\Parse;
use Tests\TestCase;

class GlideTest extends TestCase
{
    #[Test]
    #[DefineEnvironment('relativeRouteUrl')]
    public function it_outputs_a_relative_url_by_default_when_the_glide_route_is_relative()
    {
        $this->createImageInPublicDirectory();

        $this->assertEquals('/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2/bar.jpg', $this->absoluteTestTag());
        $this->assertEquals('/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2/bar.jpg', $this->absoluteTestTag(false));
        $this->assertEquals('http://localhost/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2/bar.jpg', $this->absoluteTestTag(true));
    }

    #[Test]
    #[DefineEnvironment('absoluteHttpRouteUrl')]
    public function it_outputs_an_absolute_url_by_default_when_the_glide_route_is_absolute_http()
    {
        $this->createImageInPublicDirectory();

        $this->assertEquals('http://localhost/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2/bar.jpg', $this->absoluteTestTag());
        $this->assertEquals('/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2/bar.jpg', $this->absoluteTestTag(false));
        $this->assertEquals('http://localhost/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2/bar.jpg', $this->absoluteTestTag(true));
    }

    #[Test]
    #[DefineEnvironment('absoluteHttpsRouteUrl')]
    public function it_outputs_an_absolute_url_by_default_when_the_glide_route_is_absolute_https()
    {
        $this->createImageInPublicDirectory();

        $this->assertEquals('https://localhost/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2/bar.jpg', $this->absoluteTestTag());
        $this->assertEquals('/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2/bar.jpg', $this->absoluteTestTag(false));
        $this->assertEquals('https://localhost/glide/paths/bar.jpg/689e9cd88cc1d852c9a4d3a1e27d68c2/bar.jpg', $this->absoluteTestTag(true));
    }

    #[Test]
    /**
     * https://github.com/statamic/cms/pull/9031
     */
    public function it_outputs_an_absolute_url_when_the_url_does_not_have_a_valid_extension()
    {
        $parse = (string) Parse::template('{{ glide src="https://statamic.com/foo" }}');

        $this->assertSame('https://statamic.com/foo', $parse);
    }

    #[Test]
    public function it_outputs_a_data_url()
    {
        $this->createImageInPublicDirectory();

        $tag = <<<'EOT'
{{ glide:data_url :src="foo" }}
EOT;

        $this->assertStringStartsWith('data:image/jpeg;base64', (string) Parse::template($tag, ['foo' => 'bar.jpg']));
    }

    #[Test]
    #[DefineEnvironment('absoluteHttpRouteUrlWithoutCache')]
    /**
     * https://github.com/statamic/cms/pull/11839
     */
    public function it_treats_assets_urls_starting_with_the_app_url_as_internal_assets()
    {
        $this->createImageInPublicDirectory();

        $result = (string) Parse::template('{{ glide:foo width="100" }}', ['foo' => 'http://localhost/glide/bar.jpg']);

        $this->assertStringStartsWith('/img/glide/bar.jpg', $result);
    }

    public function relativeRouteUrl($app)
    {
        $this->configureGlideCacheDiskWithUrl($app, '/glide');
    }

    public function absoluteHttpRouteUrl($app)
    {
        $this->configureGlideCacheDiskWithUrl($app, 'http://localhost/glide');
    }

    public function absoluteHttpRouteUrlWithoutCache($app)
    {
        $this->configureGlideCacheDiskWithUrl($app, 'http://localhost/glide', false);
    }

    public function absoluteHttpsRouteUrl($app)
    {
        $this->configureGlideCacheDiskWithUrl($app, 'https://localhost/glide');
    }

    private function configureGlideCacheDiskWithUrl($app, $url, $cache = 'glide')
    {
        $app['config']->set('filesystems.disks.glide', [
            'driver' => 'local',
            'root' => public_path('glide'),
            'url' => $url,
            'visibility' => 'public',
        ]);
        $app['config']->set('statamic.assets.image_manipulation.cache', $cache);
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
