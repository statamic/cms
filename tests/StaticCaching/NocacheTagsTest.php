<?php

namespace Tests\StaticCaching;

use Statamic\StaticCaching\NoCache\Session;
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

    /** @test */
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

    /** @test */
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
}
