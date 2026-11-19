<?php

namespace Tests\StaticCaching;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\File;
use Statamic\StaticCaching\Cacher;
use Tests\FakesContent;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ExternalScriptDeliveryTest extends TestCase
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
        $app['config']->set('statamic.static_caching.script_delivery', 'external');

        File::delete($this->dir);
    }

    public function tearDown(): void
    {
        File::delete($this->dir);
        parent::tearDown();
    }

    #[Test]
    public function it_references_the_csrf_and_nocache_scripts_instead_of_inlining_them()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '<html><head></head><body>{{ template_content }}</body></html>');
        $this->viewShouldReturnRaw('default', '{{ csrf_token }}');

        $this->createPage('about');

        $expected = '<html><head><script src="/!/csrf.js"></script></head><body>STATAMIC_CSRF_TOKEN<script src="/!/nocache.js"></script></body></html>';

        $response = $this->get('/about')->assertOk();

        $this->assertEquals($expected, $response->getContent());
        $this->assertStringNotContainsString('(function()', $response->getContent());
        $this->assertEquals($expected, file_get_contents($this->dir.'/about_.html'));
    }

    #[Test]
    public function the_scripts_are_served_from_routes()
    {
        $nocache = $this->get('/!/nocache.js')->assertOk();
        $this->assertStringContainsString('application/javascript', $nocache->headers->get('content-type'));
        $this->assertEquals(app(Cacher::class)->getNocacheJs(), $nocache->getContent());
        $this->assertStringContainsString("fetch('/!/nocache'", $nocache->getContent());

        $csrf = $this->get('/!/csrf.js')->assertOk();
        $this->assertStringContainsString('application/javascript', $csrf->headers->get('content-type'));
        $this->assertEquals(app(Cacher::class)->getCsrfTokenJs(), $csrf->getContent());
    }

    #[Test]
    public function the_routes_are_registered_only_in_external_mode()
    {
        $this->assertTrue(Route::has('statamic.nocache.js'));
        $this->assertTrue(Route::has('statamic.csrf.js'));
    }
}
