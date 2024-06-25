<?php

namespace Tests\Antlers\Runtime;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;
use Tests\FakesContent;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;

class OnceTest extends ParserTestCase
{
    use FakesContent,
        FakesViews,
        PreventSavingStacheItemsToDisk;

    public function test_once_block_evaluates_once_inside_a_loop_and_tag_contexts()
    {
        $template = <<<'EOT'
{{ loop from="1" to="10" }}
{{ once }}
<p>Once Block Before</p>
{{ title }} -- {{ value }}
<p>Once Block After</p>

{{ /once }}
{{ /loop }}
EOT;

        $expected = <<<'EOT'
<p>Once Block Before</p>
Test Title -- 1
<p>Once Block After</p>
EOT;

        $results = trim($this->renderString($template, ['title' => 'Test Title'], true));
        $this->assertSame(StringUtilities::normalizeLineEndings($expected), $results);
    }

    #[Test]
    public function its_reevaluated_across_requests()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        app()->instance('test-tag-count', 0);

        (new class extends Tags
        {
            public static $handle = 'test';

            public function index()
            {
                $count = app('test-tag-count');
                app()->instance('test-tag-count', ++$count);

                return $count;
            }
        })::register();

        $template = <<< 'EOT'
{{ loop from="1" to="3" }}<{{ test }}>{{ once }}<once:{{ test }}>{{ /once }}
{{ /loop }}
EOT;

        $this->viewShouldReturnRaw('default', $template);

        $this->createPage('home', [
            'with' => [
                'title' => 'Home Page',
                'content' => 'This is the home page.',
                'template' => 'default',
            ],
        ]);

        $resultOne = $this->getResponseContent('/home');
        $resultTwo = $this->getResponseContent('/home');
        $resultThree = $this->getResponseContent('/home');

        // The {{ test }} tag will increment each time it's evaluated.
        // Each iteration of the loop will be on a new line.
        // The contents of the {{ once }} tag will only appear on the first iteration.
        $this->assertSame(<<<'EXPECTED'
<1><once:2>
<3>
<4>
EXPECTED, $resultOne);

        $this->assertSame(<<<'EXPECTED'
<5><once:6>
<7>
<8>
EXPECTED, $resultTwo);

        $this->assertSame(<<<'EXPECTED'
<9><once:10>
<11>
<12>
EXPECTED, $resultThree);
    }

    protected function getResponseContent($uri)
    {
        $response = $this->get($uri)->assertStatus(200);

        return StringUtilities::normalizeLineEndings(trim($response->getContent()));
    }
}
