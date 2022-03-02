<?php

namespace Tests\StaticCaching;

use Statamic\StaticCaching\Replacer;
use Symfony\Component\HttpFoundation\Response;

class ReplacersTest extends NoCacheTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.static_caching.replacers', [
            SimpleReplacer::class,
        ]);
    }

    public function test_static_cache_replacers()
    {
        SimpleReplacer::$value = 'initial value';

        $this->flush();

        $template = <<<'EOT'
<h1>{{ title }}</h1>
{{ content }}

initial value
EOT;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('some_template', $template);

        $page = $this->createPage('about', [
            'with' => [
                'title' => 'The About Page',
                'content' => 'This is the about page.',
                'template' => 'some_template',
            ],
        ]);

        $response = $this->get('/about')
            ->assertStatus(200);

        $expected = <<<'EOT'
<h1>The About Page</h1>
<p>This is the about page.</p>


initial value
EOT;

        $this->assertSameNle($expected, $response->getContent());

        $page->set('content', 'Hello, wilderness!')->saveQuietly();

        SimpleReplacer::$value = 'another value';

        $responseTwo = $this->get('/about')
            ->assertStatus(200);

        $expected = <<<'EOT'
<h1>The About Page</h1>
<p>This is the about page.</p>


another value
EOT;

        $this->assertSameNle($expected, $responseTwo->getContent());
    }
}

class SimpleReplacer implements Replacer
{
    public static $value = '';

    public function prepareForCache(Response $response)
    {
        if (! $response->getContent()) {
            return;
        }

        $response->setContent(str_replace(
            self::$value,
            '<statamic:test_replacer>',
            $response->getContent()
        ));
    }

    public function replaceInResponse(Response $response)
    {
        if (! $response->getContent()) {
            return;
        }

        $response->setContent(str_replace(
            '<statamic:test_replacer>',
            self::$value,
            $response->getContent()
        ));
    }
}
