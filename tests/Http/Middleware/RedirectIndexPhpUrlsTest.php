<?php

namespace Tests\Http\Middleware;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\FakesContent;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RedirectIndexPhpUrlsTest extends TestCase
{
    use FakesContent;
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->withFakeViews();
        $this->viewShouldReturnRaw('default', '<h1>{{ title }}</h1>');
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');

        $this->createPage('about', ['with' => ['title' => 'About']]);

        $this->withServerVariables(['SCRIPT_NAME' => '/index.php', 'SCRIPT_FILENAME' => 'index.php']);
    }

    #[Test]
    #[DataProvider('indexPhpUrlProvider')]
    public function it_redirects_index_php_urls_to_clean_urls(string $url, string $expected)
    {
        $this->get($url)->assertRedirect($expected)->assertStatus(301);
    }

    public static function indexPhpUrlProvider(): array
    {
        return [
            'bare' => ['/index.php', 'http://localhost/'],
            'bare with trailing slash' => ['/index.php/', 'http://localhost/'],
            'page' => ['/index.php/about', 'http://localhost/about'],
            'page with query string' => ['/index.php/about?b=2&a=1', 'http://localhost/about?b=2&a=1'],
            'nested' => ['/index.php/index.php/about', 'http://localhost/about'],
        ];
    }

    #[Test]
    public function it_redirects_head_requests()
    {
        $this->head('/index.php/about')->assertRedirect('http://localhost/about')->assertStatus(301);
    }

    #[Test]
    public function it_redirects_to_the_clean_url_when_installed_in_a_subdirectory()
    {
        $this
            ->withServerVariables(['SCRIPT_NAME' => '/blog/index.php', 'SCRIPT_FILENAME' => 'index.php'])
            ->get('/blog/index.php/about')
            ->assertRedirect('http://localhost/blog/about')
            ->assertStatus(301);
    }

    #[Test]
    public function it_does_not_redirect_clean_urls()
    {
        $this->get('/about')->assertOk()->assertSee('<h1>About</h1>', false);
    }

    #[Test]
    public function it_does_not_redirect_post_requests()
    {
        $this->assertFalse($this->post('/index.php/about')->isRedirect());
    }
}
