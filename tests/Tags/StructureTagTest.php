<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Antlers;
use Statamic\Facades\Entry;
use Statamic\Facades\Nav;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StructureTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_renders()
    {
        $this->createNav();

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
        <i>Two notbar</i>
    </li>
    <li>
        <i>Three bar</i>
        <ul>
            <li>
                <i>Three One bar</i>
            </li>
            <li>
                <i>Three Two notbar</i>
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

    /** @test */
    public function it_renders_with_scope()
    {
        $this->createNav();

        // The html uses <i> tags (could be any tag, but i is short) to prevent whitespace comparison issues in the assertion.
        $template = <<<'EOT'
<ul>
{{ nav:test scope="n" }}
    <li>
        <i>{{ n:nav_title or title }} {{ foo }}</i>
        {{ if children }}
        <ul>
            {{ *recursive n:children* }}
        </ul>
        {{ /if }}
    </li>
{{ /nav:test }}
</ul>
EOT;

        $expected = <<<'EOT'
<ul>
    <li>
        <i>Navtitle One bar</i>
        <ul>
            <li>
                <i>Navtitle One One bar</i>
            </li>
        </ul>
    </li>
    <li>
        <i>Two notbar</i>
    </li>
    <li>
        <i>Three bar</i>
        <ul>
            <li>
                <i>Navtitle Three One bar</i>
            </li>
            <li>
                <i>Three Two notbar</i>
            </li>
        </ul>
    </li>
</ul>
EOT;

        $this->assertXmlStringEqualsXmlString($expected, (string) Antlers::parse($template, [
            'foo' => 'bar', // to test that cascade is inherited.
            'title' => 'outer title', // to test that cascade the page's data takes precedence over the cascading data.
            'nav_title' => 'outer nav_title', // to test that the cascade doesn't leak into the iterated scope
        ]));
    }

    private function makeNav($tree)
    {
        $nav = Nav::make('test');

        $nav->addTree($nav->makeTree('en')->tree($tree));

        $nav->save();
    }

    private function createNav()
    {
        $one = EntryFactory::collection('pages')->data(['title' => 'One', 'nav_title' => 'Navtitle One'])->create();
        $oneOne = EntryFactory::collection('pages')->data(['title' => 'One One', 'nav_title' => 'Navtitle One One'])->create();
        $two = EntryFactory::collection('pages')->data(['title' => 'Two', 'foo' => 'notbar'])->create();
        $three = EntryFactory::collection('pages')->data(['title' => 'Three'])->create();
        $threeOne = EntryFactory::collection('pages')->data(['title' => 'Three One', 'nav_title' => 'Navtitle Three One'])->create();
        $threeTwo = EntryFactory::collection('pages')->data(['title' => 'Three Two', 'foo' => 'notbar'])->create();

        Entry::shouldReceive('find')->with('1')->andReturn($one);
        Entry::shouldReceive('find')->with('1-1')->andReturn($oneOne);
        Entry::shouldReceive('find')->with('2')->andReturn($two);
        Entry::shouldReceive('find')->with('3')->andReturn($three);
        Entry::shouldReceive('find')->with('3-1')->andReturn($threeOne);
        Entry::shouldReceive('find')->with('3-2')->andReturn($threeTwo);

        $this->makeNav([
            ['entry' => '1', 'children' => [
                ['entry' => '1-1'],
            ]],
            ['entry' => '2'],
            ['entry' => '3', 'children' => [
                ['entry' => '3-1'],
                ['entry' => '3-2'],
            ]],
        ]);
    }
}
