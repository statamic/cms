<?php

namespace Tests\StaticCaching;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\File;
use Statamic\Facades\StaticCache;
use Statamic\StaticCaching\Cacher;
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

        // Initial response should have the placeholder and javascript, NOT the rendered content.
        $this->assertEquals(vsprintf('<html><body>1 <span class="nocache" data-nocache="%s">%s</span>%s</body></html>', [
            $region->key(),
            '<svg>Loading...</svg>',
            '<script>js here</script>',
        ]), $response->getContent());

        // The cached response should be the same as the initial response.
        $this->assertTrue(file_exists($this->dir.'/about_.html'));
        $this->assertEquals(vsprintf('<html><body>1 <span class="nocache" data-nocache="%s">%s</span>%s</body></html>', [
            $region->key(),
            '<svg>Loading...</svg>',
            '<script>js here</script>',
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
    public function it_adds_the_csrf_and_nocache_scripts()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '<html><head></head><body>{{ template_content }}</body></html>');
        $this->viewShouldReturnRaw('default', '{{ csrf_token }}');

        $this->createPage('about');

        $csrfTokenScript = '<script>'.app(Cacher::class)->getCsrfTokenJs().'</script>';
        $nocacheScript = '<script>'.app(Cacher::class)->getNocacheJs().'</script>';

        $this->assertFalse(file_exists($this->dir.'/about_.html'));

        $response = $this
            ->get('/about')
            ->assertOk();

        // Initial response should have the placeholder and the javascript, NOT the real token.
        $this->assertEquals(vsprintf("<html><head>{$csrfTokenScript}</head><body>STATAMIC_CSRF_TOKEN%s</body></html>", [
            $nocacheScript,
        ]), $response->getContent());

        // The cached response should be the same as the initial response.
        $this->assertTrue(file_exists($this->dir.'/about_.html'));
        $this->assertEquals(vsprintf("<html><head>{$csrfTokenScript}</head><body>STATAMIC_CSRF_TOKEN%s</body></html>", [
            $nocacheScript,
        ]), file_get_contents($this->dir.'/about_.html'));
    }

    #[Test]
    public function it_can_override_the_csrf_and_nocache_scripts()
    {
        StaticCache::nocacheJs('nocache');
        StaticCache::csrfTokenJs('csrf');

        $this->assertEquals(app(Cacher::class)->getNocacheJs(), 'nocache');
        $this->assertEquals(app(Cacher::class)->getCsrfTokenJs(), 'csrf');
    }

    #[Test]
    public function excluded_pages_should_have_real_csrf_token()
    {
        config(['statamic.static_caching.exclude' => [
            'urls' => ['/about'],
        ]]);

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '<html><body>{{ template_content }}</body></html>');
        $this->viewShouldReturnRaw('default', '{{ csrf_token }}');

        $this->createPage('about');

        $response = $this
            ->get('/about')
            ->assertOk();

        // The response should have the real CSRF token, not the placeholder.
        $this->assertEquals('<html><body>'.csrf_token().'</body></html>', $response->getContent());

        // The page should not be cached.
        $this->assertFalse(file_exists($this->dir.'/about_.html'));
    }

    #[Test]
    public function excluded_pages_should_have_nocache_regions_replaced()
    {
        config(['statamic.static_caching.exclude' => [
            'urls' => ['/about'],
        ]]);

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

        $response = $this
            ->get('/about')
            ->assertOk();

        // The response should have the nocache regions replaced with rendered content, no placeholders or JS.
        $this->assertEquals('<html><body>1 2</body></html>', $response->getContent());
        $this->assertStringNotContainsString('<script>', $response->getContent());

        // The page should not be cached.
        $this->assertFalse(file_exists($this->dir.'/about_.html'));
    }
}
