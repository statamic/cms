<?php

namespace Tests\Tags;

use Statamic\Facades\Antlers;
use Statamic\Facades\Nav;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StructureTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_renders()
    {
        $this->makeNav([
            ['title' => 'One', 'children' => [
                ['title' => 'One One'],
            ]],
            ['title' => 'Two'],
            ['title' => 'Three', 'children' => [
                ['title' => 'Three One'],
                ['title' => 'Three Two'],
            ]],
        ]);

        // The html uses <i> tags (could be any tag, but i is short) to prevent whitespace comparison issues in the assertion.
        $template = <<<'EOT'
<ul>
{{ nav:test }}
    <li>
        <i>{{ title }} {{ foo }}</i>
        {{ if children }}
        <ul>
            {{ *recursive children* }}
        </ul>
        {{ /if }}
    </li>
{{ /nav:test }}
</ul>
EOT;

        $expected = <<<'EOT'
<ul>
    <li>
        <i>One bar</i>
        <ul>
            <li>
                <i>One One bar</i>
            </li>
        </ul>
    </li>
    <li>
        <i>Two bar</i>
    </li>
    <li>
        <i>Three bar</i>
        <ul>
            <li>
                <i>Three One bar</i>
            </li>
            <li>
                <i>Three Two bar</i>
            </li>
        </ul>
    </li>
</ul>
EOT;

        $this->assertXmlStringEqualsXmlString($expected, (string) Antlers::parse($template, [
            'foo' => 'bar', // to test that cascade is inherited.
            'title' => 'outer title', // to test that cascade the page's data takes precedence over the cascading data.
        ]));
    }

    private function makeNav($tree)
    {
        $nav = Nav::make('test');

        $nav->addTree($nav->makeTree('en')->tree($tree));

        $nav->save();
    }
}
