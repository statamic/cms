<?php

namespace Tests\StaticCaching;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\StaticCaching\Replacer;
use Symfony\Component\HttpFoundation\Response;
use Tests\FakesContent;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class HalfMeasureStaticCachingTest extends TestCase
{
    use FakesContent;
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Use the file driver so that serializing happens, to better simulate a real world scenario.
        $app['config']->set('cache.default', 'file');

        $app['config']->set('statamic.static_caching.strategy', 'half');

        $app['config']->set('statamic.static_caching.replacers', array_merge($app['config']->get('statamic.static_caching.replacers'), [
            'test' => TestReplacer::class,
        ]));
    }

    #[Test]
    public function it_statically_caches()
    {
        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '<h1>{{ title }}</h1> {{ content }}');

        $page = $this->createPage('about', [
            'with' => [
                'title' => 'The About Page',
                'content' => 'This is the about page.',
                'headers' => [
                    'foo' => 'bar',
                    'alfa' => ['bravo', 'charlie'],
                ],
            ],
        ]);

        $response = $this
            ->get('/about')
            ->assertOk()
            ->assertSee('<h1>The About Page</h1> <p>This is the about page.</p>', false);
        $this->assertEquals(['bar'], $response->headers->all('foo'));
        $this->assertEquals(['bravo', 'charlie'], $response->headers->all('alfa'));

        $page
            ->set('content', 'Updated content')
            ->set('headers', ['foo' => 'updated', 'alfa' => ['updated1', 'updated2']])
            ->saveQuietly(); // Save quietly to prevent the invalidator from clearing the statically cached page.

        $response = $this
            ->get('/about')
            ->assertOk()
            ->assertSee('<h1>The About Page</h1> <p>This is the about page.</p>', false);
        $this->assertEquals(['bar'], $response->headers->all('foo'));
        $this->assertEquals(['bravo', 'charlie'], $response->headers->all('alfa'));
    }

    #[Test]
    public function it_performs_replacements()
    {
        Carbon::setTestNow(Carbon::parse('2019-01-01'));

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '{{ now format="Y-m-d" }} REPLACEME');

        $this->createPage('about');

        $response = $this->get('/about')->assertOk();
        $this->assertSame('2019-01-01 INITIAL-2019-01-01', $response->getContent());

        Carbon::setTestNow(Carbon::parse('2020-05-23'));
        $response = $this->get('/about')->assertOk();
        $this->assertSame('2019-01-01 SUBSEQUENT-2020-05-23', $response->getContent());
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

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '{{ example_count }} {{ nocache }}{{ example_count }}{{ /nocache }}');

        $this->createPage('about');

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('1 2', false);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('1 3', false);
    }

    #[Test]
    public function it_can_keep_parts_dynamic_using_nocache_tags_in_loops()
    {
        // Use a tag that outputs something dynamic but consistent.
        // It will just increment by one every time it's used.

        app()->instance('example_count', 0);

        (new class extends \Statamic\Tags\Tags
        {
            public static $handle = 'example_count';

            public function wildcard($method)
            {
                $count = app('example_count');
                $count++;
                app()->instance('example_count', $count);

                return $this->context->get($method).$count;
            }
        })::register();

        $this->withStandardFakeViews();

        $template = <<<'EOT'
    {{ array }}
        {{ value }}
        {{ example_count:value }}
        {{ nocache }}
            {{ value }}
            {{ example_count:value }}
        {{ /nocache }}
    {{ /array }}
    EOT;

        $this->viewShouldReturnRaw('default', $template);

        $this->createPage('about', ['with' => [
            'array' => [
                ['value' => 'One'],
                ['value' => 'Two'],
                ['value' => 'Three'],
            ],
        ]]);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSeeInOrder([
                'One', 'One1', 'One', 'One4',
                'Two', 'Two2', 'Two', 'Two5',
                'Three', 'Three3', 'Three', 'Three6',
            ]);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSeeInOrder([
                'One', 'One1', 'One', 'One7',
                'Two', 'Two2', 'Two', 'Two8',
                'Three', 'Three3', 'Three', 'Three9',
            ]);
    }

    #[Test]
    public function it_can_keep_the_cascade_parts_dynamic_using_nocache_tags()
    {
        // The "now" variable is generated in the cascade on every request.

        Carbon::setTestNow(Carbon::parse('2019-01-01'));

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '{{ now format="Y-m-d" }} {{ nocache }}{{ now format="Y-m-d" }}{{ /nocache }}');

        $this->createPage('about');

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('2019-01-01 2019-01-01', false);

        Carbon::setTestNow(Carbon::parse('2020-05-23'));

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('2019-01-01 2020-05-23', false);
    }

    #[Test]
    public function it_can_keep_the_urls_page_parts_dynamic_using_nocache_tags()
    {
        // The "page" variable (i.e. the about entry) is inserted into the cascade on every request.

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '<h1>{{ title }}</h1> {{ text }} {{ nocache }}{{ text }}{{ /nocache }}');

        $page = $this->createPage('about', [
            'with' => [
                'title' => 'The About Page',
                'text' => 'This is the about page.',
            ],
        ]);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('<h1>The About Page</h1> This is the about page. This is the about page.', false);

        $page
            ->set('text', 'Updated text')
            ->saveQuietly(); // Save quietly to prevent the invalidator from clearing the statically cached page.

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('<h1>The About Page</h1> This is the about page. Updated text', false);
    }

    #[Test]
    public function it_can_keep_parts_dynamic_using_nested_nocache_tags()
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

        $template = <<<'EOT'
{{ example_count }}
{{ nocache }}
    {{ example_count }}
    {{ nocache }}
        {{ example_count }}
    {{ /nocache }}
{{ /nocache }}
EOT;

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', $template);

        $this->createPage('about');

        $this
            ->get('/about')
            ->assertOk()
            ->assertSeeInOrder([1, 2, 3]);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSeeInOrder([1, 4, 5]);
    }

    #[Test]
    public function it_can_keep_parts_dynamic_using_nocache_tags_with_view_front_matter()
    {
        $template = <<<'EOT'
---
foo: bar
---
{{ view:foo }} {{ nocache }}{{ view:foo }}{{ /nocache }}
EOT;

        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', $template);

        $this->createPage('about');

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('bar bar');

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('bar bar');
    }

    public function bladeViewPaths($app)
    {
        $app['config']->set('view.paths', [
            __DIR__.'/blade',
            ...$app['config']->get('view.paths'),
        ]);
    }

    #[Test]
    #[DefineEnvironment('bladeViewPaths')]
    public function it_can_keep_parts_dynamic_using_blade()
    {
        // Use a tag that outputs something dynamic.
        // It will just increment by one every time it's used.

        app()->instance('example_count', 0);

        app()->instance('example_count_tag', function () {
            $count = app('example_count');
            $count++;
            app()->instance('example_count', $count);

            return $count;
        });

        $this->createPage('about');

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('1 2', false);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSee('1 3', false);
    }
}

class TestReplacer implements Replacer
{
    public function prepareResponseToCache(Response $response, Response $initial)
    {
        $initial->setContent(
            str_replace('REPLACEME', 'INITIAL-'.Carbon::now()->format('Y-m-d'), $initial->getContent())
        );
    }

    public function replaceInCachedResponse(Response $response)
    {
        $response->setContent(
            str_replace('REPLACEME', 'SUBSEQUENT-'.Carbon::now()->format('Y-m-d'), $response->getContent())
        );
    }
}
