<?php

namespace Tests\StaticCaching;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\File;
use Statamic\Facades\StaticCache;
use Statamic\StaticCaching\NoCache\Session;
use Tests\FakesContent;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FullMeasureStaticCachingTest extends TestCase
{
    use FakesContent;
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    private $dir;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.static_caching.strategy', 'full');
        $app['config']->set('statamic.static_caching.strategies.full.path', $this->dir = __DIR__.'/static');

        File::delete($this->dir);
    }

    public function tearDown(): void
    {
        File::delete($this->dir);
        parent::tearDown();
    }

    #[Test]
    public function it_can_keep_parts_dynamic_using_nocache_tags()
    {
        // Use a tag that outputs something dynamic.
        // It will just increment by one every time it's used.

        app()->instance('example_count', 0);

        (new class extends \Statamic\Tags\Tags
        {
            public static $handle = 'example_count';

            public function index()
            {
                $count = app('example_count');
                $count++;
                app()->instance('example_count', $count);

                return $count;
            }
        })::register();

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '<html><body>{{ template_content }}</body></html>');
        $this->viewShouldReturnRaw('default', '{{ example_count }} {{ nocache }}{{ example_count }}{{ /nocache }}');

        $this->createPage('about');

        StaticCache::nocacheJs('js here');
        StaticCache::nocachePlaceholder('<svg>Loading...</svg>');

        $this->assertFalse(file_exists($this->dir.'/about_.html'));

        $response = $this
            ->get('/about')
            ->assertOk();

        $region = app(Session::class)->regions()->first();

        // Initial response should be dynamic and not contain javascript.
        $this->assertEquals('<html><body>1 2</body></html>', $response->getContent());

        // The cached response should have the nocache placeholder, and the javascript.
        $this->assertTrue(file_exists($this->dir.'/about_.html'));
        $this->assertEquals(vsprintf('<html><body>1 <span class="nocache" data-nocache="%s">%s</span>%s</body></html>', [
            $region->key(),
            '<svg>Loading...</svg>',
            '<script type="text/javascript">js here</script>',
        ]), file_get_contents($this->dir.'/about_.html'));
    }

    #[Test]
    public function javascript_doesnt_get_output_if_there_are_no_nocache_tags()
    {
        // Use a tag that outputs something dynamic.
        // It will just increment by one every time it's used.

        app()->instance('example_count', 0);

        (new class extends \Statamic\Tags\Tags
        {
            public static $handle = 'example_count';

            public function index()
            {
                $count = app('example_count');
                $count++;
                app()->instance('example_count', $count);

                return $count;
            }
        })::register();

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '<html><body>{{ template_content }}</body></html>');
        $this->viewShouldReturnRaw('default', '{{ example_count }}');

        $this->createPage('about');

        StaticCache::nocacheJs('js here');
        StaticCache::nocachePlaceholder('<svg>Loading...</svg>');

        $this->assertFalse(file_exists($this->dir.'/about_.html'));

        $response = $this
            ->get('/about')
            ->assertOk();

        // Initial response should be dynamic and not contain javascript.
        $this->assertEquals('<html><body>1</body></html>', $response->getContent());

        // The cached response should be the same, with no javascript.
        $this->assertTrue(file_exists($this->dir.'/about_.html'));
        $this->assertEquals('<html><body>1</body></html>', file_get_contents($this->dir.'/about_.html'));
    }

    #[Test]
    public function it_should_add_the_javascript_if_there_is_a_csrf_token()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '<html><body>{{ template_content }}</body></html>');
        $this->viewShouldReturnRaw('default', '{{ csrf_token }}');

        $this->createPage('about');

        StaticCache::nocacheJs('js here');

        $this->assertFalse(file_exists($this->dir.'/about_.html'));

        $response = $this
            ->get('/about')
            ->assertOk();

        // Initial response should be dynamic and not contain javascript.
        $this->assertEquals('<html><body>'.csrf_token().'</body></html>', $response->getContent());

        // The cached response should have the token placeholder, and the javascript.
        $this->assertTrue(file_exists($this->dir.'/about_.html'));
        $this->assertEquals(vsprintf('<html><body>STATAMIC_CSRF_TOKEN%s</body></html>', [
            '<script type="text/javascript">js here</script>',
        ]), file_get_contents($this->dir.'/about_.html'));
    }
}
