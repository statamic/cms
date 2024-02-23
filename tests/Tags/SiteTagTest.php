<?php

namespace Tests\Tags;

use Tests\TestCase;
use Statamic\Facades\Site;
use Statamic\Facades\Antlers;

class SiteTagTest extends TestCase
{
    /** @test */
    public function it_gets_site_from_site_context()
    {
        Site::setConfig(['sites' => [
            'english' => ['name' => 'English', 'locale' => 'en_US', 'url' => '/en'],
            'french' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => '/fr'],
        ]]);

        $context = [
            'site' => Site::get('english'),
            'sites' => Site::all()->values(),
        ];

        $this->assertEquals(
            'English',
            Antlers::parse('{{ site:english:name }}', $context)
        );

        $this->assertEquals(
            'fr_FR',
            Antlers::parse('{{ site:french }}{{ locale }}{{ /site:french }}', $context)
        );

        $this->assertEquals(
            '',
            Antlers::parse('{{ site:nonexistend_site:name }}', $context)
        );
    }

    /** @test */
    public function it_wont_conflict_with_site_context()
    {
        Site::setConfig(['sites' => [
            'english' => ['name' => 'English', 'locale' => 'en_US', 'url' => '/en'],
            'french' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => '/fr'],
        ]]);

        $context = [
            'site' => Site::get('french'),
            'sites' => Site::all()->values(),
        ];

        $this->assertEquals(
            'French',
            Antlers::parse('{{ site:name }}', $context)
        );
    }
}
