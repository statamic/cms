<?php

namespace Tests\Sites;

use Illuminate\Support\Collection;
use Statamic\Sites\Site;
use Statamic\Sites\Sites;
use Tests\TestCase;

class SitesTest extends TestCase
{
    private $sites;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.url', 'http://absolute-url-resolved-from-request.com');
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->sites = new Sites([
            'default' => 'en',
            'sites' => [
                'en' => ['url' => 'http://test.com/'],
                'fr' => ['url' => 'http://fr.test.com/'],
                'de' => ['url' => 'http://test.com/de/'],
            ],
        ]);
    }

    /** @test */
    public function gets_all_sites()
    {
        tap($this->sites->all(), function ($sites) {
            $this->assertInstanceOf(Collection::class, $sites);
            $this->assertEquals(3, $sites->count());
            $this->assertInstanceOf(Site::class, $sites->first());
            $this->assertEquals('en', $sites->values()->get(0)->handle());
            $this->assertEquals('fr', $sites->values()->get(1)->handle());
            $this->assertEquals('de', $sites->values()->get(2)->handle());
        });
    }

    /** @test */
    public function can_reinitialize_sites_by_reproviding_the_config()
    {
        $this->sites->setConfig([
            'default' => 'foo',
            'sites' => [
                'foo' => [],
                'bar' => [],
            ],
        ]);

        $this->assertEquals('foo', $this->sites->get('foo')->handle());
        $this->assertEquals('bar', $this->sites->get('bar')->handle());
        $this->assertArrayNotHasKey('en', $this->sites->all());
        $this->assertArrayNotHasKey('fr', $this->sites->all());
        $this->assertArrayNotHasKey('de', $this->sites->all());
    }

    /** @test */
    public function can_change_specific_config_items()
    {
        $this->sites->setConfig('sites.en.url', 'http://foobar.com/');

        $this->assertEquals('http://foobar.com', $this->sites->get('en')->url());
    }

    /** @test */
    public function checks_whether_there_are_multiple_sites()
    {
        $this->sites->setConfig([
            'default' => 'foo',
            'sites' => [
                'foo' => [],
                'bar' => [],
            ],
        ]);

        $this->assertTrue($this->sites->hasMultiple());

        $this->sites->setConfig([
            'default' => 'foo',
            'sites' => [
                'foo' => [],
            ],
        ]);

        $this->assertFalse($this->sites->hasMultiple());
    }

    /** @test */
    public function gets_site_by_handle()
    {
        tap($this->sites->get('en'), function ($site) {
            $this->assertInstanceOf(Site::class, $site);
            $this->assertEquals('en', $site->handle());
        });
    }

    /** @test */
    public function it_gets_the_default_site()
    {
        tap($this->sites->default(), function ($site) {
            $this->assertInstanceOf(Site::class, $site);
            $this->assertEquals('en', $site->handle());
        });
    }

    /** @test */
    public function gets_site_from_url()
    {
        $this->assertEquals('en', $this->sites->findByUrl('http://test.com/something')->handle());
        $this->assertEquals('de', $this->sites->findByUrl('http://test.com/de/something')->handle());
        $this->assertEquals('fr', $this->sites->findByUrl('http://fr.test.com/something')->handle());
        $this->assertNull($this->sites->findByUrl('http://unknownsite.com'));

        // Make sure that urls that begin with one of the subdirectories (eg. /de) don't get misinterpreted.
        // https://github.com/statamic/cms/issues/1874
        $this->assertEquals('en', $this->sites->findByUrl('http://test.com/delightful')->handle());
        $this->assertEquals('de', $this->sites->findByUrl('http://test.com/de')->handle());

        // Make sure that urls that have a query string don't get misinterpreted.
        // https://github.com/statamic/cms/issues/2207
        $this->assertEquals('en', $this->sites->findByUrl('http://test.com?foo=bar')->handle());
        $this->assertEquals('de', $this->sites->findByUrl('http://test.com/de?foo=bar')->handle());
    }

    /** @test */
    public function current_site_can_be_explicitly_set()
    {
        $this->assertEquals('en', $this->sites->current()->handle());

        $this->sites->setCurrent('fr');

        $this->assertEquals('fr', $this->sites->current()->handle());
    }

    /** @test */
    public function gets_site_from_url_when_using_relative_urls()
    {
        $sites = new Sites([
            'default' => 'en',
            'sites' => [
                'en' => ['url' => '/'],
                'fr' => ['url' => '/fr/'],
            ],
        ]);

        $this->assertEquals('en', $sites->findByUrl('http://absolute-url-resolved-from-request.com/something')->handle());
        $this->assertEquals('fr', $sites->findByUrl('http://absolute-url-resolved-from-request.com/fr/something')->handle());
        $this->assertNull($sites->findByUrl('http://unknownsite.com'));
    }

    /** @test */
    public function it_gets_the_selected_site_from_session()
    {
        session()->put('statamic.cp.selected-site', 'fr');
        $this->assertEquals('fr', $this->sites->selected()->handle());
    }

    /** @test */
    public function the_selected_site_is_the_default_if_not_set()
    {
        session()->put('statamic.cp.selected-site', null);
        $this->assertEquals('en', $this->sites->selected()->handle());
    }

    /** @test */
    public function the_selected_site_is_the_default_if_invalid()
    {
        session()->put('statamic.cp.selected-site', 'invalid');
        $this->assertEquals('en', $this->sites->selected()->handle());
    }
}
