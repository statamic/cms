<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Statamic\Facades\Site;
use Tests\TestCase;

class GetSiteTagTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Site::setSites([
            'english' => ['name' => 'English', 'locale' => 'en_US', 'url' => '/en'],
            'french' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => '/fr'],
        ]);
    }

    #[Test]
    public function it_gets_site_by_handle()
    {
        $this->assertEquals(
            'English',
            Antlers::parse('{{ get_site handle="english" }}{{ name }}{{ /get_site }}')
        );

        $this->assertEquals(
            'French',
            Antlers::parse('{{ get_site:french }}{{ name }}{{ /get_site:french }}')
        );
    }

    #[Test]
    public function it_can_be_used_as_single_tag()
    {
        $this->assertEquals(
            'en_US',
            Antlers::parse('{{ get_site:english:locale }}')
        );
    }

    #[Test]
    public function it_throws_exception_if_handle_is_missing()
    {
        $this->expectExceptionMessage('A site handle is required.');

        Antlers::parse('{{ get_site }}{{ name }}{{ /get_site }}');
    }

    #[Test]
    public function it_throws_exception_if_site_doesnt_exist()
    {
        $this->expectExceptionMessage('Site [nonexistent] does not exist.');

        Antlers::parse('{{ get_site handle="nonexistent" }}{{ name }}{{ /get_site }}');
    }
}
