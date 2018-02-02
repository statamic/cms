<?php

namespace Tests\Sites;

use Tests\TestCase;
use Statamic\Sites\Sites;

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
    function gets_site_from_url()
    {
        $this->assertEquals('en', $this->sites->findByUrl('http://test.com/something')->handle());
        $this->assertEquals('de', $this->sites->findByUrl('http://test.com/de/something')->handle());
        $this->assertEquals('fr', $this->sites->findByUrl('http://fr.test.com/something')->handle());
        $this->assertNull($this->sites->findByUrl('http://unknownsite.com'));
    }
}