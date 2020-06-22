<?php

namespace Tests\Sites;

use Statamic\Data\AugmentedCollection;
use Statamic\Facades\Antlers;
use Statamic\Sites\Site;
use Tests\TestCase;

class SiteTest extends TestCase
{
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.url', 'http://absolute-url-resolved-from-request.com');
    }

    /** @test */
    public function gets_handle()
    {
        $site = new Site('en', []);

        $this->assertEquals('en', $site->handle());
    }

    /** @test */
    public function gets_name()
    {
        $site = new Site('en', ['name' => 'English']);

        $this->assertEquals('English', $site->name());
    }

    /** @test */
    public function gets_locale()
    {
        $site = new Site('en', ['locale' => 'en_US']);

        $this->assertEquals('en_US', $site->locale());
    }

    /** @test */
    public function gets_short_locale()
    {
        $this->assertEquals('en', (new Site('en', ['locale' => 'en']))->shortLocale());
        $this->assertEquals('en', (new Site('en', ['locale' => 'en_US']))->shortLocale());
        $this->assertEquals('en', (new Site('en', ['locale' => 'en-US']))->shortLocale());
    }

    /** @test */
    public function gets_url_when_given_a_trailing_slash()
    {
        $site = new Site('en', ['url' => 'http://test.com/']);

        $this->assertEquals('http://test.com', $site->url());
    }

    /** @test */
    public function gets_url_when_not_given_a_trailing_slash()
    {
        $site = new Site('en', ['url' => 'http://test.com']);

        $this->assertEquals('http://test.com', $site->url());
    }

    /** @test */
    public function gets_url_given_a_relative_url()
    {
        $site = new Site('en', ['url' => '/']);

        $this->assertEquals('/', $site->url());
    }

    /** @test */
    public function gets_url_given_a_relative_url_and_subdirectory()
    {
        $site = new Site('en', ['url' => '/sub']);

        $this->assertEquals('/sub', $site->url());
    }

    /** @test */
    public function gets_url_given_a_relative_url_and_subdirectory_with_trailing_slash()
    {
        $site = new Site('en', ['url' => '/sub/']);

        $this->assertEquals('/sub', $site->url());
    }

    /** @test */
    public function gets_absolute_url()
    {
        $this->assertEquals(
            'http://a-defined-absolute-url.com',
            (new Site('en', ['url' => 'http://a-defined-absolute-url.com/']))->absoluteUrl()
        );

        $this->assertEquals(
            'http://a-defined-absolute-url.com',
            (new Site('en', ['url' => 'http://a-defined-absolute-url.com']))->absoluteUrl()
        );

        $this->assertEquals(
            'http://absolute-url-resolved-from-request.com',
            (new Site('en', ['url' => '/']))->absoluteUrl()
        );

        $this->assertEquals(
            'http://absolute-url-resolved-from-request.com/fr',
            (new Site('en', ['url' => '/fr/']))->absoluteUrl()
        );

        $this->assertEquals(
            'http://absolute-url-resolved-from-request.com/fr',
            (new Site('en', ['url' => '/fr']))->absoluteUrl()
        );

        $this->get('/something');

        $this->assertEquals(
            'http://absolute-url-resolved-from-request.com',
            (new Site('en', ['url' => '/']))->absoluteUrl()
        );

        $this->assertEquals(
            'http://absolute-url-resolved-from-request.com/fr',
            (new Site('en', ['url' => '/fr/']))->absoluteUrl()
        );

        $this->assertEquals(
            'http://absolute-url-resolved-from-request.com/fr',
            (new Site('en', ['url' => '/fr']))->absoluteUrl()
        );
    }

    /** @test */
    public function gets_path()
    {
        tap(new Site('en', ['url' => 'http://test.com/']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://test.com'));
            $this->assertEquals('/', $site->relativePath('http://test.com/'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/foo/bar/'));
        });

        tap(new Site('en', ['url' => 'http://test.com']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://test.com'));
            $this->assertEquals('/', $site->relativePath('http://test.com/'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/foo/bar/'));
        });

        tap(new Site('fr', ['url' => 'http://test.com/fr/']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://test.com/fr'));
            $this->assertEquals('/', $site->relativePath('http://test.com/fr/'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/fr/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/fr/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/fr/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/fr/foo/bar/'));
        });

        tap(new Site('fr', ['url' => 'http://test.com/fr']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://test.com/fr'));
            $this->assertEquals('/', $site->relativePath('http://test.com/fr/'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/fr/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/fr/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/fr/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/fr/foo/bar/'));
        });

        tap(new Site('sub', ['url' => 'http://subdomain.test.com/']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://subdomain.test.com'));
            $this->assertEquals('/', $site->relativePath('http://subdomain.test.com/'));
            $this->assertEquals('/foo', $site->relativePath('http://subdomain.test.com/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://subdomain.test.com/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://subdomain.test.com/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://subdomain.test.com/foo/bar/'));
        });

        tap(new Site('sub', ['url' => 'http://subdomain.test.com']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://subdomain.test.com'));
            $this->assertEquals('/', $site->relativePath('http://subdomain.test.com/'));
            $this->assertEquals('/foo', $site->relativePath('http://subdomain.test.com/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://subdomain.test.com/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://subdomain.test.com/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://subdomain.test.com/foo/bar/'));
        });

        tap(new Site('en', ['url' => '/']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://absolute-url-resolved-from-request.com'));
            $this->assertEquals('/', $site->relativePath('http://absolute-url-resolved-from-request.com/'));
            $this->assertEquals('/foo', $site->relativePath('http://absolute-url-resolved-from-request.com/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://absolute-url-resolved-from-request.com/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://absolute-url-resolved-from-request.com/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://absolute-url-resolved-from-request.com/foo/bar/'));
        });

        tap(new Site('fr', ['url' => '/fr/']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://absolute-url-resolved-from-request.com/fr'));
            $this->assertEquals('/', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/'));
            $this->assertEquals('/foo', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo/bar/'));
        });

        tap(new Site('fr', ['url' => '/fr']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://absolute-url-resolved-from-request.com/fr'));
            $this->assertEquals('/', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/'));
            $this->assertEquals('/foo', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo/bar/'));
        });
    }

    /** @test */
    public function it_is_augmentable()
    {
        $site = new Site('test', [
            'name' => 'Test',
            'url' => 'http://test.com',
            'locale' => 'en_US',
        ]);

        $values = $site->augmented()->all();
        $this->assertInstanceOf(AugmentedCollection::class, $values);
        $this->assertEquals([
            'handle' => 'test',
            'name' => 'Test',
            'locale' => 'en_US',
            'short_locale' => 'en',
            'url' => 'http://test.com',
        ], $values->all());

        $this->assertEquals(
            'test Test en_US en http://test.com',
            Antlers::parse('{{ site }}{{ handle }} {{ name }} {{ locale }} {{ short_locale }} {{ url }}{{ /site }}', ['site' => $site])
        );
        $this->assertEquals(
            'test Test en_US en http://test.com',
            Antlers::parse('{{ site:handle }} {{ site:name }} {{ site:locale }} {{ site:short_locale }} {{ site:url }}', ['site' => $site])
        );
    }

    /** @test */
    public function it_casts_the_handle_to_a_string()
    {
        $site = new Site('test', []);

        $this->assertSame('test', (string) $site);
        $this->assertEquals('test', Antlers::parse('{{ site }}', ['site' => $site]));
    }
}
