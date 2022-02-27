<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Facades\Antlers;
use Statamic\Facades\Entry;
use Statamic\Facades\Nav;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StructureTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_does_not_lose_track_of_its_parent_with_lots_of_potential_candidates()
    {
        $this->createNav();

        // The html uses <i> tags (could be any tag, but i is short) to prevent whitespace comparison issues in the assertion.
        $template = <<<'EOT'
<ul>
{{ nav:test }}
    <li>
        <i>{{ title }} {{ foo }}</i>
        {{ if (children | count ) > 0 }}
        <ul>
            {{ children }}
                {{ if (children | count) == 0 }}
                <i>No Children: {{ title }}</i>
                {{ else }}
                <i>With Children: {{ title }}</i>
                    
                {{ if children }}
                <ul>
                    <i>{{ title }}</i>
                    <li>{{ *recursive children* }}</li>
                    <i>after recursion</i>
                </ul>
                {{ /if }}
                {{ /if }}
            {{ /children }}
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
            
                
                <i>No Children: One One</i>
                
            
        </ul>
        
    </li>

    <li>
        <i>Two notbar</i>
        
    </li>

    <li>
        <i>Three bar</i>
        
        <ul>
            
                
                <i>No Children: Three One</i>
                
            
                
                <i>With Children: Three Two</i>
                    
                
                <ul>
                    <i>Three Two</i>
                    <li>
                <i>With Children: Three Two One</i>
                    
                
                
                <i>With Children: Three Two Two</i>
                    
                
                </li>
                    <i>after recursion</i>
                </ul>
                
                
            
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
                <ul>
                    <li>
                        <i>Three Two One notbar</i>
                    </li>
                    <li>
                        <i>Three Two Two notbar</i>
                    </li>
                </ul>
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
                <ul>
                    <li>
                        <i>Three Two One notbar</i>
                    </li>
                    <li>
                        <i>Three Two Two notbar</i>
                    </li>
                </ul>
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

        $nav->makeTree('en', $tree)->save();

        $nav->save();
    }

    private function createNav()
    {
        $one = EntryFactory::collection('pages')->id('1')->data(['title' => 'One', 'nav_title' => 'Navtitle One'])->create();
        $oneOne = EntryFactory::collection('pages')->id('1-1')->data(['title' => 'One One', 'nav_title' => 'Navtitle One One'])->create();
        $two = EntryFactory::collection('pages')->id('2')->data(['title' => 'Two', 'foo' => 'notbar'])->create();
        $three = EntryFactory::collection('pages')->id('3')->data(['title' => 'Three'])->create();
        $threeOne = EntryFactory::collection('pages')->id('3-1')->data(['title' => 'Three One', 'nav_title' => 'Navtitle Three One'])->create();
        $threeTwo = EntryFactory::collection('pages')->id('3-2')->data(['title' => 'Three Two', 'foo' => 'notbar'])->create();

        $threeTwoOne = EntryFactory::collection('pages')->id('3-2-1')->data(['title' => 'Three Two One', 'foo' => 'notbar'])->create();
        $threeTwoTwo = EntryFactory::collection('pages')->id('3-2-2')->data(['title' => 'Three Two Two', 'foo' => 'notbar'])->create();

        $builder = $this->mock(QueryBuilder::class);
        $builder->shouldReceive('whereIn')->with('id', ['1', '1-1', '2', '3', '3-1', '3-2', '3-2-1', '3-2-2'])->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(collect([$one, $oneOne, $two, $three, $threeOne, $threeTwo, $threeTwoOne, $threeTwoTwo]));
        Entry::shouldReceive('query')->andReturn($builder);

        $this->makeNav([
            ['entry' => '1', 'children' => [
                ['entry' => '1-1'],
            ]],
            ['entry' => '2'],
            ['entry' => '3', 'children' => [
                ['entry' => '3-1'],
                ['entry' => '3-2', 'children' => [
                    ['entry' => '3-2-1'],
                    ['entry' => '3-2-2'],
                ]],
            ]],
        ]);
    }
}
