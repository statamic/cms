<?php

namespace Tests\Tags;

use Statamic\Facades\Parse;
use Statamic\Facades\Site;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SitesTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutEvents();

        Site::setConfig(['sites' => [
            'en' => ['url' => '/en', 'name' => 'English', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr', 'name' => 'French', 'locale' => 'fr_FR'],
            'es' => ['url' => '/es', 'name' => 'Spanish', 'locale' => 'es_ES'],
        ]]);
    }

    private function tag($tag)
    {
        return (string) Parse::template($tag);
    }

    /** @test */
    public function it_renders_sites()
    {
        $this->assertEquals(
            '<en English en_US /en><fr French fr_FR /fr><es Spanish es_ES /es>',
            $this->tag('{{ sites }}<{{ handle }} {{ name }} {{ locale }} {{ url }}>{{ /sites }}')
        );
    }
}
