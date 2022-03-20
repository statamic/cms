<?php

namespace Tests\StaticCaching;

use Tests\PreventSavingStacheItemsToDisk;

class NoCacheTest extends NoCacheTestCase
{
    use PreventSavingStacheItemsToDisk;

    public function test_nocache_tag()
    {
        $this->flush();

        $template = <<<'EOT'
<h1>{{ title }}</h1>
{{ content }}
{{ no_cache }}{{ content }}{{ /no_cache }}
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

<p>This is the about page.</p>
EOT;

        $this->assertSameNle($expected, $response->content());

        $page->set('content', 'Hello, there!')->saveQuietly();

        $responseTwo = $this->get('/about')
            ->assertStatus(200);

        $expected = <<<'EOT'
<h1>The About Page</h1>
<p>This is the about page.</p>

<p>Hello, there!</p>
EOT;

        $this->assertSameNle($expected, $responseTwo->content());

        $page->set('content', 'Hello, wilderness!')->saveQuietly();

        $responseThree = $this->get('/about')
            ->assertStatus(200);

        $expected = <<<'EOT'
<h1>The About Page</h1>
<p>This is the about page.</p>

<p>Hello, wilderness!</p>
EOT;

        $this->assertSameNle($expected, $responseThree->content());
    }
}