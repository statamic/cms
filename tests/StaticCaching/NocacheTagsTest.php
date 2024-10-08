<?php

namespace Tests\StaticCaching;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\StaticCaching\NoCache\Session;
use Statamic\StaticCaching\NoCache\StringRegion;
use Tests\FakesContent;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class NocacheTagsTest extends TestCase
{
    use FakesContent;
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.static_caching.strategy', null);
    }

    #[Test]
    public function it_can_nest_nocache_tags()
    {
        $this->withStandardFakeViews();

        $template = <<<'EOT'
{{ title }}
{{ nocache }}
    {{ title }}
    {{ nocache }}
        {{ title }}
        {{ nocache }}{{ title }}{{ /nocache }}
    {{ /nocache }}
{{ /nocache }}
EOT;

        $this->viewShouldReturnRaw('default', $template);

        $page = $this->createPage('about', [
            'with' => [
                'title' => 'Existing',
            ],
        ]);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSeeInOrder(['Existing', 'Existing', 'Existing', 'Existing']);

        $page
            ->set('title', 'Updated')
            ->saveQuietly(); // Save quietly to prevent the invalidator from clearing the statically cached page.

        $this->app->make(Session::class)->reset();

        $this
            ->get('/about')
            ->assertOk()
            ->assertSeeInOrder(['Updated', 'Updated', 'Updated', 'Updated']);
    }

    #[Test]
    public function it_can_keep_nocache_tags_dynamic_inside_cache_tags()
    {
        $this->withStandardFakeViews();

        $template = <<<'EOT'
{{ title }}
{{ cache }}
    {{ title }}
    {{ nocache }}{{ title }}{{ /nocache }}
{{ /cache }}
EOT;

        $this->viewShouldReturnRaw('default', $template);

        $page = $this->createPage('about', [
            'with' => [
                'title' => 'Existing',
            ],
        ]);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSeeInOrder(['Existing', 'Existing', 'Existing']);

        $page
            ->set('title', 'Updated')
            ->saveQuietly(); // Save quietly to prevent the invalidator from clearing the statically cached page.

        $this->app->make(Session::class)->reset();

        $this
            ->get('/about')
            ->assertOk()
            ->assertSeeInOrder(['Updated', 'Existing', 'Updated']);
    }

    #[Test]
    public function it_can_keep_nested_nocache_tags_dynamic_inside_cache_tags()
    {
        $this->withStandardFakeViews();

        $template = <<<'EOT'
{{ title }}
{{ nocache }}
    {{ title }}
    {{ cache }}
        {{ title }}
        {{ nocache }}{{ title }}{{ /nocache }}
    {{ /cache }}
{{ /nocache }}
EOT;

        $this->viewShouldReturnRaw('default', $template);

        $page = $this->createPage('about', [
            'with' => [
                'title' => 'Existing',
            ],
        ]);

        $this
            ->get('/about')
            ->assertOk()
            ->assertSeeInOrder(['Existing', 'Existing', 'Existing', 'Existing']);

        $page
            ->set('title', 'Updated')
            ->saveQuietly(); // Save quietly to prevent the invalidator from clearing the statically cached page.

        $this->app->make(Session::class)->reset();

        $this
            ->get('/about')
            ->assertOk()
            ->assertSeeInOrder(['Updated', 'Updated', 'Existing', 'Updated']);
    }

    #[Test]
    public function it_only_adds_appropriate_fields_of_context_to_session()
    {
        // The tag won't do anything if it's not being used on a request with the cache middleware.
        $this->get('/');

        $expectedFields = [
            'foo', // By adding @auto it will be picked up from the template.
            'baz', // Explicitly selected
            // 'bar' // Not explicitly selected
            // 'nope' // Not in the context
        ];
        $template = '{{ nocache select="@auto|baz" }}{{ foo }}{{ nope }}{{ /nocache }}';
        $context = [
            'foo' => 'alfa',
            'bar' => 'bravo',
            'baz' => 'charlie',
        ];

        $region = Mockery::mock(StringRegion::class)->shouldReceive('placeholder')->andReturn('the placeholder')->getMock();

        $this->mock(Session::class, fn ($mock) => $mock
            ->shouldReceive('pushRegion')
            ->withArgs(fn ($arg1, $arg2, $arg3) => array_keys($arg2) === $expectedFields)
            ->once()->andReturn($region));

        $this->assertEquals('the placeholder', $this->tag($template, $context));
    }

    #[Test]
    public function it_only_adds_explicitly_defined_fields_of_context_to_session()
    {
        // The tag won't do anything if it's not being used on a request with the cache middleware.
        $this->get('/');

        // We will not add `bar` to the session because it is not explicitly defined.
        // We will not add `nope` to the session because it is not in the context.
        $expectedFields = ['foo', 'baz'];
        $template = '{{ nocache select="foo|baz|nope" }}{{ foo }}{{ bar }}{{ nope }}{{ /nocache }}';
        $context = [
            'foo' => 'alfa',
            'bar' => 'bravo',
            'baz' => 'charlie',
        ];

        $region = Mockery::mock(StringRegion::class)->shouldReceive('placeholder')->andReturn('the placeholder')->getMock();

        $this->mock(Session::class, fn ($mock) => $mock
            ->shouldReceive('pushRegion')
            ->withArgs(fn ($arg1, $arg2, $arg3) => array_keys($arg2) === $expectedFields)
            ->once()->andReturn($region));

        $this->assertEquals('the placeholder', $this->tag($template, $context));
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }
}
