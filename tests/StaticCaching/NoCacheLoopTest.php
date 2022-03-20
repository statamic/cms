<?php

namespace Tests\StaticCaching;

use Illuminate\Support\Carbon;

class NoCacheLoopTest extends NoCacheTestCase
{
    public function test_nocache_in_loops()
    {
        $this->flush();

        Carbon::setTestNow(Carbon::parse('2019-01-01'));
        $template = <<<'EOT'
{{ title }}
{{ now }}
{{ nocache_data }}
{{ value }}
{{ now }}
{{ /nocache_data }}
{{ nocache_data }}
{{ no_cache }}
{{ if first }}First{{ /if }}
{{ if last }}Last{{ /if }}
{{ value }}
{{ now }}
{{ /no_cache }}
{{ /nocache_data }}
EOT;
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('some_template', $template);

        view()->composer('*', function ($v) {
            $v->with([
                'now' => Carbon::now(),
                'nocache_data' => ['One', 'Two', 'Three'],
            ]);
        });

        $page = $this->createPage('loops', [
            'with' => [
                'title' => 'The About Page',
                'template' => 'some_template',
            ],
        ]);

        $response = $this->get('/loops')
            ->assertStatus(200);

        $expected = <<<'EOT'
The About Page
January 1st, 2019

One
January 1st, 2019

Two
January 1st, 2019

Three
January 1st, 2019



First

One
January 1st, 2019





Two
January 1st, 2019




Last
Three
January 1st, 2019
EOT;

        $this->assertSameNle($expected, $response->getContent());

        Carbon::setTestNow(Carbon::parse('2019-02-01'));

        view()->composer('*', function ($v) {
            $v->with([
                'now' => Carbon::now(),
                'nocache_data' => ['One', 'Two', 'Three'],
            ]);
        });

        $page->set('title', 'A different title')->saveQuietly();

        $responseTwo = $this->get('/loops')
            ->assertStatus(200);

        $expected = <<<'EOT'
The About Page
January 1st, 2019

One
January 1st, 2019

Two
January 1st, 2019

Three
January 1st, 2019


First

One
February 1st, 2019



Two
February 1st, 2019


Last
Three
February 1st, 2019
EOT;

        $this->assertSameNle($expected, $responseTwo->getContent());
    }
}