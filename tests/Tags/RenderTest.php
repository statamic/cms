<?php

namespace Tests\Tags;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Imaging\Manipulator;
use Statamic\Facades\Antlers;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Image;
use Statamic\Imaging\Manipulators\GlideManipulator;
use Statamic\Imaging\Manipulators\Sources\AssetSource;
use Statamic\Imaging\Manipulators\Sources\PathSource;
use Statamic\Imaging\Manipulators\Sources\UrlSource;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RenderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_outputs_a_url_to_a_manipulated_image()
    {
        // {{ render :src="img" w="100" }}

        $driver = Mockery::mock(Manipulator::class);
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof PathSource && $source->path() === 'test.jpg')->once()->andReturnSelf();
        $driver->shouldReceive('getAvailableParams')->once()->andReturn(['w', 'h']);
        $driver->shouldReceive('addParams')->with(['w' => '100', 'h' => '150'])->once()->andReturnSelf();
        $driver->shouldReceive('getUrl')->once()->andReturn('the-url');
        Image::shouldReceive('driver')->once()->andReturn($driver);

        $output = $this->parse('{{ render :src="img" w="100" h="150" foo="ignore" }}', [
            'img' => 'test.jpg',
        ]);

        $this->assertEquals('the-url', $output);
    }

    #[Test]
    public function it_outputs_a_url_to_a_manipulated_image_using_shorthand()
    {
        // {{ render:img w="100" }}

        $driver = Mockery::mock(Manipulator::class);
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof PathSource && $source->path() === 'test.jpg')->once()->andReturnSelf();
        $driver->shouldReceive('getAvailableParams')->once()->andReturn(['w', 'h']);
        $driver->shouldReceive('addParams')->with(['w' => '100', 'h' => '150'])->once()->andReturnSelf();
        $driver->shouldReceive('getUrl')->once()->andReturn('the-url');
        Image::shouldReceive('driver')->once()->andReturn($driver);

        $output = $this->parse('{{ render:img w="100" h="150" foo="ignore" }}', [
            'img' => 'test.jpg',
        ]);

        $this->assertEquals('the-url', $output);
    }

    #[Test]
    public function it_outputs_a_url_to_a_manipulated_image_in_an_img_tag()
    {
        // {{ render :src="img" w="100" tag="true" alt="foo" }}

        $driver = Mockery::mock(Manipulator::class);
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof PathSource && $source->path() === 'test.jpg')->once()->andReturnSelf();
        $driver->shouldReceive('getAvailableParams')->once()->andReturn(['w', 'h']);
        $driver->shouldReceive('addParams')->with(['w' => '100', 'h' => '150'])->once()->andReturnSelf();
        $driver->shouldReceive('getUrl')->once()->andReturn('the-url');
        Image::shouldReceive('driver')->once()->andReturn($driver);

        $output = $this->parse('{{ render :src="img" w="100" h="150" foo="ignore" tag="true" alt="the alt text" }}', [
            'img' => 'test.jpg',
        ]);

        $this->assertEquals('<img src="the-url" alt="the alt text" />', $output);
    }

    #[Test]
    public function it_outputs_a_url_to_a_manipulated_image_in_an_img_tag_using_shorthand()
    {
        // {{ render:img w="100" tag="true" alt="foo" }}

        $driver = Mockery::mock(Manipulator::class);
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof PathSource && $source->path() === 'test.jpg')->once()->andReturnSelf();
        $driver->shouldReceive('getAvailableParams')->once()->andReturn(['w', 'h']);
        $driver->shouldReceive('addParams')->with(['w' => '100', 'h' => '150'])->once()->andReturnSelf();
        $driver->shouldReceive('getUrl')->once()->andReturn('the-url');
        Image::shouldReceive('driver')->once()->andReturn($driver);

        $output = $this->parse('{{ render:img w="100" h="150" foo="ignore" tag="true" alt="the alt text" }}', [
            'img' => 'test.jpg',
        ]);

        $this->assertEquals('<img src="the-url" alt="the alt text" />', $output);
    }

    #[Test]
    public function when_using_a_tag_pair_it_will_include_variables()
    {
        // {{ render :src="img" w="100" }}
        //     {{ url }}, {{ width }}, etc
        // {{ /render }}

        $driver = Mockery::mock(Manipulator::class);
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof PathSource && $source->path() === 'test.jpg')->once()->andReturnSelf();
        $driver->shouldReceive('getAvailableParams')->once()->andReturn(['w', 'h']);
        $driver->shouldReceive('addParams')->with(['w' => '100', 'h' => '150'])->once()->andReturnSelf();
        $driver->shouldReceive('getUrl')->once()->andReturn('the-url');
        $driver->shouldReceive('getAttributes')->once()->andReturn([
            'width' => 'the-width',
            'height' => 'the-height',
        ]);
        Image::shouldReceive('driver')->once()->andReturn($driver);

        $template = '{{ render :src="img" w="100" h="150" foo="ignore"
            }}{{ url }},{{ width }},{{ height }}{{ /render }}';

        $output = $this->parse($template, ['img' => 'test.jpg']);

        $this->assertEquals('the-url,the-width,the-height', $output);
    }

    #[Test]
    public function when_using_a_tag_pair_it_will_include_variables_via_asset_object()
    {
        // {{ render :src="img" w="100" }}
        //     {{ url }}, {{ width }}, etc
        // {{ /render }}

        Storage::fake('test');
        $file = UploadedFile::fake()->image('foo.jpg', 30, 60); // creates a 723 byte image
        Storage::disk('test')->putFileAs('img', $file, 'foo.jpg');
        tap($container = AssetContainer::make('test')->disk('test')->title('Test'))->save();
        $asset = tap($container->makeAsset('img/foo.jpg')->data(['foo' => 'bar']))->save();

        $driver = Mockery::mock(Manipulator::class);
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof AssetSource && $source->asset() === $asset)->once()->andReturnSelf();
        $driver->shouldReceive('getAvailableParams')->once()->andReturn(['w', 'h']);
        $driver->shouldReceive('addParams')->with(['w' => '100', 'h' => '150'])->once()->andReturnSelf();
        $driver->shouldReceive('getUrl')->once()->andReturn('the-url');
        $driver->shouldReceive('getAttributes')->once()->andReturn([
            'width' => 'the-width',
            'height' => 'the-height',
        ]);
        Image::shouldReceive('driver')->once()->andReturn($driver);

        $template = '{{ render :src="img" w="100" h="150" foo="ignore"
            }}{{ url }},{{ width }},{{ height }},{{ foo }}{{ /render }}';

        $output = $this->parse($template, ['img' => $asset]);

        $this->assertEquals('the-url,the-width,the-height,bar', $output);
    }

    #[Test]
    public function when_using_a_tag_pair_it_will_include_variables_using_shorthand()
    {
        // {{ render :src="img" w="100" }}
        //     {{ url }}, {{ width }}, etc
        // {{ /render }}

        $driver = Mockery::mock(Manipulator::class);
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof PathSource && $source->path() === 'test.jpg')->once()->andReturnSelf();
        $driver->shouldReceive('getAvailableParams')->once()->andReturn(['w', 'h']);
        $driver->shouldReceive('addParams')->with(['w' => '100', 'h' => '150'])->once()->andReturnSelf();
        $driver->shouldReceive('getUrl')->once()->andReturn('the-url');
        $driver->shouldReceive('getAttributes')->once()->andReturn([
            'width' => 'the-width',
            'height' => 'the-height',
        ]);
        Image::shouldReceive('driver')->once()->andReturn($driver);

        $template = '{{ render:img w="100" h="150" foo="ignore"
            }}{{ url }},{{ width }},{{ height }}{{ /render:img }}';

        $output = $this->parse($template, ['img' => 'test.jpg']);

        $this->assertEquals('the-url,the-width,the-height', $output);
    }

    #[Test]
    public function when_using_a_tag_pair_it_can_loop_over_items()
    {
        // {{ render :src="img" w="100" }}
        //     {{ url }}, {{ width }}, etc
        // {{ /render }}

        $driver = Mockery::mock(Manipulator::class);
        $driver->shouldReceive('getAvailableParams')->twice()->andReturn(['w', 'h']);
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof PathSource && $source->path() === 'one.jpg')->once()->andReturnSelf();
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof PathSource && $source->path() === 'two.jpg')->once()->andReturnSelf();
        $driver->shouldReceive('addParams')->with(['w' => '100', 'h' => '150'])->twice()->andReturnSelf();
        $driver->shouldReceive('getUrl')->twice()->andReturn('the-url', 'second-url');
        $driver->shouldReceive('getAttributes')->twice()->andReturn([
            'width' => 'the-width',
            'height' => 'the-height',
        ], [
            'width' => 'second-width',
            'height' => 'second-height',
        ]);
        Image::shouldReceive('driver')->twice()->andReturn($driver);

        $template = <<<'EOF'
{{ render :src="imgs" w="100" h="150" foo="ignore" }}
{{ url }},{{ width }},{{ height }}
{{ /render }}
EOF;

        $expected = <<<'EOF'
the-url,the-width,the-height

second-url,second-width,second-height
EOF;

        $output = $this->parse($template, ['imgs' => ['one.jpg', 'two.jpg']]);

        $this->assertEquals(trim($expected), trim($output));
    }

    #[Test]
    public function when_using_a_tag_pair_it_can_loop_over_items_using_shorthand()
    {
        $driver = Mockery::mock(Manipulator::class);
        $driver->shouldReceive('getAvailableParams')->twice()->andReturn(['w', 'h']);
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof PathSource && $source->path() === 'one.jpg')->once()->andReturnSelf();
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof PathSource && $source->path() === 'two.jpg')->once()->andReturnSelf();
        $driver->shouldReceive('addParams')->with(['w' => '100', 'h' => '150'])->twice()->andReturnSelf();
        $driver->shouldReceive('getUrl')->twice()->andReturn('the-url', 'second-url');
        $driver->shouldReceive('getAttributes')->twice()->andReturn([
            'width' => 'the-width',
            'height' => 'the-height',
        ], [
            'width' => 'second-width',
            'height' => 'second-height',
        ]);
        Image::shouldReceive('driver')->twice()->andReturn($driver);

        $template = <<<'EOF'
{{ render:imgs w="100" h="150" foo="ignore" }}
{{ url }},{{ width }},{{ height }}
{{ /render:imgs }}
EOF;

        $expected = <<<'EOF'
the-url,the-width,the-height

second-url,second-width,second-height
EOF;

        $output = $this->parse($template, ['imgs' => ['one.jpg', 'two.jpg']]);

        $this->assertEquals(trim($expected), trim($output));
    }

    #[Test]
    public function it_converts_to_manipulated_urls_in_batches()
    {
        $driver = Mockery::mock(Manipulator::class);
        $driver->shouldReceive('getAvailableParams')->times(3)->andReturn(['w', 'h']);
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof PathSource && $source->path() === 'foo.jpg')->once()->andReturnSelf();
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof PathSource && $source->path() === 'foo/bar.png')->once()->andReturnSelf();
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof UrlSource && $source->path() === 'http://absolute.com/some.gif')->once()->andReturnSelf();
        $driver->shouldReceive('addParams')->with(['w' => '100', 'h' => '150'])->times(3)->andReturnSelf();
        $driver->shouldReceive('getUrl')->times(3)->andReturn('url-one', 'url-two', 'url-three');
        Image::shouldReceive('driver')->times(3)->andReturn($driver);

        $template = <<<'EOF'
{{ render:batch w="100" h="150" foo="ignore" }}
  <img src="foo.jpg" keep="me" />
  <img src="foo/bar.png" alt="test" />
  <img src="http://absolute.com/some.gif" />
  {{ hello }}
{{ /render:batch }}
EOF;

        $expected = <<<'EOF'
  <img src="url-one" keep="me" />
  <img src="url-two" alt="test" />
  <img src="url-three" />
  world
EOF;

        $output = $this->parse($template, ['hello' => 'world']);

        $this->assertEquals(trim($expected), trim($output));
    }

    /**
     * @test
     *
     * @dataProvider dataUrlProvider
     */
    public function it_outputs_a_manipulated_image_as_a_data_url($tag)
    {
        // {{ render:data_url :src="img" w="100" }}

        $driver = Mockery::mock(Manipulator::class);
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof PathSource && $source->path() === 'test.jpg')->once()->andReturnSelf();
        $driver->shouldReceive('getAvailableParams')->once()->andReturn(['w', 'h']);
        $driver->shouldReceive('addParams')->with(['w' => '100', 'h' => '150'])->once()->andReturnSelf();
        $driver->shouldReceive('getDataUrl')->once()->andReturn('the-url');
        Image::shouldReceive('driver')->once()->andReturn($driver);

        $output = $this->parse('{{ render:'.$tag.' :src="img" w="100" h="150" foo="ignore" }}', [
            'img' => 'test.jpg',
        ]);

        $this->assertEquals('the-url', $output);
    }

    public static function dataUrlProvider()
    {
        return [
            'uri' => ['data_uri'],
            'url' => ['data_url'], // alias
        ];
    }

    #[Test]
    public function it_can_use_a_specific_manipulator()
    {
        $driver = Mockery::mock(Manipulator::class);
        $driver->shouldReceive('setSource')->withArgs(fn ($source) => $source instanceof PathSource && $source->path() === 'test.jpg')->once()->andReturnSelf();
        $driver->shouldReceive('getAvailableParams')->once()->andReturn(['w', 'h']);
        $driver->shouldReceive('addParams')->with(['w' => '100'])->once()->andReturnSelf();
        $driver->shouldReceive('getUrl')->andReturn('the-url');
        Image::shouldReceive('driver')->with('imgix')->once()->andReturn($driver);

        $this->assertEquals('the-url', $this->parse('{{ render :src="img" w="100" using="imgix" }}', ['img' => 'test.jpg']));
    }

    #[Test, DataProvider('focalPointProvider')]
    public function fit_crop_focal_param_will_automatically_append_focal_point(
        $src,
        $defineFocalPoint,
        $expectedX,
        $expectedY,
        $expectedZ,
    ) {
        Storage::fake('test', ['url' => '/assets']);
        $file = UploadedFile::fake()->image('foo.jpg', 30, 60); // creates a 723 byte image
        Storage::disk('test')->putFileAs('', $file, 'foo.jpg');
        tap($container = AssetContainer::make('test')->disk('test')->title('Test'))->save();
        $asset = tap($container->makeAsset('foo.jpg')->data(['focus' => $defineFocalPoint ? '75-10-2' : null]))->save();

        if ($src instanceof \Closure) {
            $src = $src($asset);
        }

        $driver = Mockery::mock(GlideManipulator::class)->makePartial();
        $driver->shouldReceive('addParams')->with(['w' => 100])->once()->andReturnSelf();
        $driver->shouldReceive('addFocalPointParams')->with($expectedX, $expectedY, $expectedZ)->once()->andReturnSelf();
        $driver->shouldReceive('getUrl')->andReturn('the-url');
        Image::shouldReceive('driver')->once()->andReturn($driver);

        $this->assertEquals('the-url', $this->parse('{{ render:img fit="crop_focal" w="100" }}', ['img' => $src]));
    }

    public static function focalPointProvider()
    {
        return [
            'path to non-asset' => ['something.jpg', false, 50, 50, 1],
            'path to asset with focal point' => ['assets/foo.jpg', true, 75, 10, 2],
            'path to asset without focal point' => ['foo.jpg', false, 50, 50, 1],
            'asset with focal point' => [fn ($asset) => $asset, true, 75, 10, 2],
            'asset without focal point' => [fn ($asset) => $asset, false, 50, 50, 1],
            'asset id with focal point' => [fn ($asset) => $asset->id(), true, 75, 10, 2],
            'asset id without focal point' => [fn ($asset) => $asset->id(), false, 50, 50, 1],
            'external url' => ['http://external.com/something.jpg', false, 50, 50, 1],
        ];
    }

    private function parse($string, $data = [])
    {
        return (string) Antlers::parse($string, $data);
    }
}
