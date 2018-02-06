<?php

namespace Tests\Sites;

use Tests\TestCase;
use Statamic\Sites\Site;
use Statamic\Sites\Sites;
use Illuminate\Support\Collection;

class SitesTest extends TestCase
{
    public function setUp()
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
    function gets_all_sites()
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
    function gets_site_by_handle()
    {
        tap($this->sites->get('en'), function ($site) {
            $this->assertInstanceOf(Site::class, $site);
            $this->assertEquals('en', $site->handle());
        });
    }

    /** @test */
    function gets_site_from_url()
    {
        $this->assertEquals('en', $this->sites->findByUrl('http://test.com/something')->handle());
        $this->assertEquals('de', $this->sites->findByUrl('http://test.com/de/something')->handle());
        $this->assertEquals('fr', $this->sites->findByUrl('http://fr.test.com/something')->handle());
        $this->assertNull($this->sites->findByUrl('http://unknownsite.com'));
    }
}