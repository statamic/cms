<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Facades\Antlers;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Nav;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StructureTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->createCollectionAndNav();
    }

    /** @test */
    public function it_renders_a_nav()
    {
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
            <li>
                <i>URL and title bar</i>
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
    <li>
        <i>Title only bar</i>
        <ul>
            <li>
                <i>URL only bar</i>
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
    public function it_renders_a_nav_with_scope()
    {
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
            <li>
                <i>URL and title bar</i>
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
    <li>
        <i>Title only bar</i>
        <ul>
            <li>
                <i>URL only bar</i>
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

    /** @test */
    public function it_hides_unpublished_entries_by_default()
    {
        $this->assertEquals('[1][1-1][1-2][2][3][3-1][3-2][5][5-1]', $this->parseBasicTemplate('test'));

        $this->assertEquals('[1][1-1][2][3][3-1][3-2]', $this->parseBasicTemplate('collection:pages'));
    }

    /** @test */
    public function it_shows_or_hides_unpublished_entries_using_the_show_unpublished_parameter()
    {
        $this->assertEquals('[1][1-1][1-2][2][3][3-1][3-2][5][5-1]', $this->parseBasicTemplate('test', 'show_unpublished="false"'));
        $this->assertEquals('[1][1-1][1-2][2][3][3-1][3-2][3-3][4][4-1][5][5-1]', $this->parseBasicTemplate('test', 'show_unpublished="true"'));

        $this->assertEquals('[1][1-1][2][3][3-1][3-2]', $this->parseBasicTemplate('collection:pages', 'show_unpublished="false"'));
        $this->assertEquals('[1][1-1][2][3][3-1][3-2][3-3][4][4-1]', $this->parseBasicTemplate('collection:pages', 'show_unpublished="true"'));
    }

    /** @test */
    public function it_filters_by_status()
    {
        $this->assertEquals('[1][1-1][2][3][3-1][3-2]', $this->parseBasicTemplate('collection:pages', 'status:is="published"'));
        $this->assertEquals('[4]', $this->parseBasicTemplate('collection:pages', 'status:not="published"'));
        $this->assertEquals('[4]', $this->parseBasicTemplate('collection:pages', 'status:is="draft"'));
        $this->assertEquals('[1][1-1][2][3][3-1][3-2][3-3][4][4-1]', $this->parseBasicTemplate('collection:pages', 'status:in="draft|published"'));
    }

    /** @test */
    public function it_filters_by_published_boolean()
    {
        $this->assertEquals('[1][1-1][2][3][3-1][3-2]', $this->parseBasicTemplate('collection:pages', 'published:is="true"'));
        $this->assertEquals('[4]', $this->parseBasicTemplate('collection:pages', 'published:is="false"'));
    }

    /** @test */
    public function it_filters_by_entry_data()
    {
        $this->assertEquals('[3][3-1][3-2]', $this->parseBasicTemplate('collection:pages', 'title:contains="Three"'));
        $this->assertEquals('[2]', $this->parseBasicTemplate('collection:pages', 'foo:is="notbar"'));
        $this->assertEquals('[1][1-1][3][3-1]', $this->parseBasicTemplate('collection:pages', 'foo:isnt="notbar"'));
        $this->assertEquals('[1]', $this->parseBasicTemplate('collection:pages', 'nav_title:in="Grapes|Navtitle Four|Bananas|Navtitle One"'));
        $this->assertEquals('[]', $this->parseBasicTemplate('collection:pages', 'never:is="true"'));
    }

    private function makeNav($tree)
    {
        $nav = Nav::make('test');

        $nav->makeTree('en', $tree)->save();

        $nav->save();
    }

    private function parseBasicTemplate($handle, $params = null)
    {
        return (string) Antlers::parse($this->createBasicTemplate($handle, $params));
    }

    private function createBasicTemplate($handle, $params = null)
    {
        return "{{ nav:$handle $params }}[{{ entry_id ?? id }}]{{ if children }}{{ *recursive children* }}{{ /if }}{{ /nav:$handle }}";
    }

    private function createCollectionAndNav()
    {
        $collection = tap(Collection::make('pages'))->save();

        $one = EntryFactory::collection('pages')->id('1')->data(['title' => 'One', 'nav_title' => 'Navtitle One'])->create();
        $oneOne = EntryFactory::collection('pages')->id('1-1')->data(['title' => 'One One', 'nav_title' => 'Navtitle One One'])->create();
        $two = EntryFactory::collection('pages')->id('2')->data(['title' => 'Two', 'foo' => 'notbar'])->create();
        $three = EntryFactory::collection('pages')->id('3')->data(['title' => 'Three'])->create();
        $threeOne = EntryFactory::collection('pages')->id('3-1')->data(['title' => 'Three One', 'nav_title' => 'Navtitle Three One'])->create();
        $threeTwo = EntryFactory::collection('pages')->id('3-2')->data(['title' => 'Three Two', 'foo' => 'notbar'])->create();
        $threeThree = EntryFactory::collection('pages')->id('3-3')->data(['title' => 'Three Three', 'nav_title' => 'Navtitle Three Three'])->published(false)->create();
        $four = EntryFactory::collection('pages')->id('4')->data(['title' => 'Four', 'nav_title' => 'Navtitle Four'])->published(false)->create();
        $fourOne = EntryFactory::collection('pages')->id('4-1')->data(['title' => 'Four One', 'nav_title' => 'Navtitle Four One'])->published(true)->create();

        $collection->structureContents(['foo' => 'bar'])->save();
        $collection->structure()->in('en')->tree([
            ['entry' => '1', 'children' => [
                ['entry' => '1-1'],
            ]],
            ['entry' => '2'],
            ['entry' => '3', 'children' => [
                ['entry' => '3-1'],
                ['entry' => '3-2'],
                ['entry' => '3-3'],
            ]],
            ['entry' => '4', 'children' => [
                ['entry' => '4-1'],
            ]],
        ])->save();

        $ids = collect(['1', '1-1', null, '2', '3', '3-1', '3-2', '3-3', '4', '4-1', null, null])->filter();

        $builder = $this->mock(QueryBuilder::class);
        $builder->shouldReceive('whereIn')->with('id', $ids->all())->andReturnSelf();
        $builder->shouldReceive('whereIn')->with('id', $ids->values()->all())->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(collect([$one, $oneOne, $two, $three, $threeOne, $threeTwo, $threeThree, $four, $fourOne]));
        Entry::shouldReceive('query')->andReturn($builder);

        $this->makeNav([
            ['entry' => '1', 'children' => [
                ['entry' => '1-1'],
                ['id' => '1-2', 'title' => 'URL and title', 'url' => 'https://statamic.com'],
            ]],
            ['entry' => '2'],
            ['entry' => '3', 'children' => [
                ['entry' => '3-1'],
                ['entry' => '3-2'],
                ['entry' => '3-3'],
            ]],
            ['entry' => '4', 'children' => [
                ['entry' => '4-1'],
            ]],
            ['id' => '5', 'title' => 'Title only', 'children' => [
                ['id' => '5-1', 'title' => 'URL only', 'url' => 'https://statamic.com'],
            ]],
        ]);
    }
}
