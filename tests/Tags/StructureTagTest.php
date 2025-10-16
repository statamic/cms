<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_renders_a_nav()
    {
        $this->createCollectionAndNav();

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

    #[Test]
    public function it_renders_a_nav_with_selected_fields()
    {
        $this->createCollectionAndNav();

        // The html uses <i> tags (could be any tag, but i is short) to prevent whitespace comparison issues in the assertion.
        $template = <<<'EOT'
<ul>
{{ nav:test select="title" }}
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

        $parsed = (string) Antlers::parse($template, [
            'foo' => 'bar', // to test that cascade is inherited.
            'title' => 'outer title', // to test that cascade the page's data takes precedence over the cascading data.
        ]);

        // This is really what we're interested in testing. The "Two" entry has a foo value
        // of "notbar", but we're only selecting the title, so we shouldn't get the real value.
        if (str_contains($parsed, 'Two notbar')) {
            $this->fail('The "Two" entry\'s "foo" value was included.');
        }

        $this->assertXmlStringEqualsXmlString($expected, $parsed);
    }

    #[Test]
    public function it_renders_a_nav_with_scope()
    {
        $this->createCollectionAndNav();

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

    #[Test]
    public function it_renders_a_nav_with_as()
    {
        $this->createCollectionAndNav();

        // The html uses <i> tags (could be any tag, but i is short) to prevent whitespace comparison issues in the assertion.
        $template = <<<'EOT'
<ul>
{{ nav:test as="navtastic" }}
    <li>Something before the loop</li>
    {{ navtastic }}
    <li>
        <i>{{ nav_title or title }} {{ foo }}</i>
    </li>
    {{ /navtastic }}
{{ /nav:test }}
</ul>
EOT;

        $expected = <<<'EOT'
<ul>
    <li>Something before the loop</li>
    <li>
        <i>Navtitle One bar</i>
    </li>
    <li>
        <i>Two notbar</i>
    </li>
    <li>
        <i>Three bar</i>
    </li>
    <li>
        <i>Title only bar</i>
    </li>
</ul>
EOT;

        $this->assertXmlStringEqualsXmlString($expected, (string) Antlers::parse($template, [
            'foo' => 'bar', // to test that cascade is inherited.
        ]));
    }

    #[Test]
    public function it_hides_unpublished_entries_by_default()
    {
        $this->createCollectionAndNav();

        $this->assertEquals('[1][1-1][1-2][2][3][3-1][3-2][5][5-1]', $this->parseBasicTemplate('test'));

        $this->assertEquals('[1][1-1][2][3][3-1][3-2]', $this->parseBasicTemplate('collection:pages'));
    }

    #[Test]
    public function it_shows_or_hides_unpublished_entries_using_the_show_unpublished_parameter()
    {
        $this->createCollectionAndNav();

        $this->assertEquals('[1][1-1][1-2][2][3][3-1][3-2][5][5-1]', $this->parseBasicTemplate('test', 'show_unpublished="false"'));
        $this->assertEquals('[1][1-1][1-2][2][3][3-1][3-2][3-3][4][4-1][5][5-1]', $this->parseBasicTemplate('test', 'show_unpublished="true"'));

        $this->assertEquals('[1][1-1][2][3][3-1][3-2]', $this->parseBasicTemplate('collection:pages', 'show_unpublished="false"'));
        $this->assertEquals('[1][1-1][2][3][3-1][3-2][3-3][4][4-1]', $this->parseBasicTemplate('collection:pages', 'show_unpublished="true"'));
    }

    #[Test]
    public function it_filters_by_status()
    {
        $this->createCollectionAndNav();

        $this->assertEquals('[1][1-1][2][3][3-1][3-2]', $this->parseBasicTemplate('collection:pages', 'status:is="published"'));
        $this->assertEquals('[4]', $this->parseBasicTemplate('collection:pages', 'status:not="published"'));
        $this->assertEquals('[4]', $this->parseBasicTemplate('collection:pages', 'status:is="draft"'));
        $this->assertEquals('[1][1-1][2][3][3-1][3-2][3-3][4][4-1]', $this->parseBasicTemplate('collection:pages', 'status:in="draft|published"'));
    }

    #[Test]
    public function it_filters_by_published_boolean()
    {
        $this->createCollectionAndNav();

        $this->assertEquals('[1][1-1][2][3][3-1][3-2]', $this->parseBasicTemplate('collection:pages', 'published:is="true"'));
        $this->assertEquals('[4]', $this->parseBasicTemplate('collection:pages', 'published:is="false"'));
    }

    #[Test]
    public function it_filters_by_entry_data()
    {
        $this->createCollectionAndNav();

        $this->assertEquals('[3][3-1][3-2]', $this->parseBasicTemplate('collection:pages', 'title:contains="Three"'));
        $this->assertEquals('[2]', $this->parseBasicTemplate('collection:pages', 'foo:is="notbar"'));
        $this->assertEquals('[1][1-1][3][3-1]', $this->parseBasicTemplate('collection:pages', 'foo:isnt="notbar"'));
        $this->assertEquals('[1]', $this->parseBasicTemplate('collection:pages', 'nav_title:in="Grapes|Navtitle Four|Bananas|Navtitle One"'));
        $this->assertEquals('[]', $this->parseBasicTemplate('collection:pages', 'never:is="true"'));
    }

    #[Test]
    public function it_sets_is_current_and_is_parent_for_a_nav()
    {
        $template = '{{ nav:test }}[{{ id }}{{ if is_parent }}=parent{{ /if }}{{ if is_current }}=current{{ /if }}]{{ if children }}{{ *recursive children* }}{{ /if }}{{ /nav:test }}';

        $mock = \Mockery::mock(\Statamic\Facades\URL::getFacadeRoot())->makePartial();
        \Statamic\Facades\URL::swap($mock);

        $this->makeNav([
            ['id' => 'home', 'title' => 'Home', 'url' => '/', 'children' => [
                ['id' => 'home-1', 'title' => 'home-1', 'url' => '/foo'],
            ]],
            ['id' => '1', 'title' => '1', 'url' => '/1', 'children' => [
                ['id' => '1-1', 'title' => '1.1', 'url' => '/1/1', 'children' => [
                    ['id' => '1-1-1', 'title' => '1.1.1', 'url' => '/1/1/1', 'children' => [
                        ['id' => '1-1-1-1', 'title' => '1.1.1.1', 'url' => '/1/1/1/1'],
                    ]],
                ]],
            ]],
            ['id' => '2', 'title' => '2', 'url' => '/2'],
            ['id' => '3', 'title' => '3'],
        ]);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/1');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[home][home-1][1=current][1-1][1-1-1][1-1-1-1][2][3]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/1/1');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[home][home-1][1=parent][1-1=current][1-1-1][1-1-1-1][2][3]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/1/1/1');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[home][home-1][1=parent][1-1=parent][1-1-1=current][1-1-1-1][2][3]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/1/1/1/1');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[home][home-1][1=parent][1-1=parent][1-1-1=parent][1-1-1-1=current][2][3]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/2');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[home][home-1][1][1-1][1-1-1][1-1-1-1][2=current][3]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[home=current][home-1][1][1-1][1-1-1][1-1-1-1][2][3]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/foo');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[home=parent][home-1=current][1][1-1][1-1-1][1-1-1-1][2][3]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/other');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[home][home-1][1][1-1][1-1-1][1-1-1-1][2][3]', $result);

        // Only the last child has an URL.
        $this->makeNav([
            ['id' => '1', 'title' => '1', 'children' => [
                ['id' => '1-1', 'title' => '1.1', 'children' => [
                    ['id' => '1-1-1', 'title' => '1.1.1', 'children' => [
                        ['id' => '1-1-1-1', 'title' => '1.1.1.1', 'url' => '/1/1/1/1'],
                    ]],
                ]],
            ]],
        ]);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/1/1/1/1');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[1=parent][1-1=parent][1-1-1=parent][1-1-1-1=current]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[1][1-1][1-1-1][1-1-1-1]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/other');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[1][1-1][1-1-1][1-1-1-1]', $result);

        // Only the top parent has an URL.
        $this->makeNav([
            ['id' => '1', 'title' => '1', 'url' => '/1', 'children' => [
                ['id' => '1-1', 'title' => '1.1', 'children' => [
                    ['id' => '1-1-1', 'title' => '1.1.1', 'children' => [
                        ['id' => '1-1-1-1', 'title' => '1.1.1.1'],
                    ]],
                ]],
            ]],
        ]);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/1');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[1=current][1-1][1-1-1][1-1-1-1]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[1][1-1][1-1-1][1-1-1-1]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/other');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[1][1-1][1-1-1][1-1-1-1]', $result);
    }

    #[Test]
    public function it_sets_is_current_and_is_parent_for_a_nav_when_home_is_an_entry()
    {
        tap(Collection::make('pages')->routes('{slug}')->structureContents(['expects_root' => true]))->save();
        $home = EntryFactory::collection('pages')->id('home')->data(['title' => 'One'])->create();

        $template = '{{ nav:test }}[{{ id }}{{ if is_parent }}=parent{{ /if }}{{ if is_current }}=current{{ /if }}]{{ if children }}{{ *recursive children* }}{{ /if }}{{ /nav:test }}';

        $mock = \Mockery::mock(\Statamic\Facades\URL::getFacadeRoot())->makePartial();
        \Statamic\Facades\URL::swap($mock);

        $this->makeNav([
            ['id' => 'home', 'title' => 'Home', 'entry' => $home],
            ['id' => '1', 'title' => '1', 'url' => '/1', 'children' => [
                ['id' => '1-1', 'title' => '1.1', 'url' => '/1/1', 'children' => [
                    ['id' => '1-1-1', 'title' => '1.1.1', 'url' => '/1/1/1', 'children' => [
                        ['id' => '1-1-1-1', 'title' => '1.1.1.1', 'url' => '/1/1/1/1'],
                    ]],
                ]],
            ]],
            ['id' => '2', 'title' => '2', 'url' => '/2'],
            ['id' => '3', 'title' => '3'],
        ]);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[home=current][1][1-1][1-1-1][1-1-1-1][2][3]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/1');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[home][1=current][1-1][1-1-1][1-1-1-1][2][3]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/1/1/1');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[home][1=parent][1-1=parent][1-1-1=current][1-1-1-1][2][3]', $result);
    }

    #[Test]
    public function it_sets_is_parent_based_on_the_url_too()
    {
        $template = '{{ nav:test }}[{{ id }}{{ if is_parent }}=parent{{ /if }}{{ if is_current }}=current{{ /if }}]{{ if children }}{{ *recursive children* }}{{ /if }}{{ /nav:test }}';

        $mock = \Mockery::mock(\Statamic\Facades\URL::getFacadeRoot())->makePartial();
        \Statamic\Facades\URL::swap($mock);

        $this->makeNav([
            ['id' => '1', 'title' => 'One', 'url' => '/1', 'children' => [
                ['id' => '2', 'title' => 'Two', 'url' => '/1/2'],
            ]],
        ]);

        tap(Collection::make('rad')->routes('/1/2/{slug}'))->save();
        EntryFactory::collection('rad')->id('3')->slug('3')->data(['title' => 'Three'])->create();

        $mock->shouldReceive('getCurrent')->once()->andReturn('/1/2/3');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[1=parent][2=parent]', $result);
    }

    #[Test]
    public function it_sets_is_current_and_is_parent_for_a_collection()
    {
        $collection = tap(Collection::make('pages'))->save();

        $page_1 = EntryFactory::collection('pages')->id('1')->data(['title' => 'One'])->create();
        $page_1_1 = EntryFactory::collection('pages')->id('1-1')->data(['title' => 'One One'])->create();
        $page_1_1_1 = EntryFactory::collection('pages')->id('1-1-1')->data(['title' => 'One One One'])->create();
        $page_1_1_1_1 = EntryFactory::collection('pages')->id('1-1-1-1')->data(['title' => 'One One One One'])->create();
        $page_2 = EntryFactory::collection('pages')->id('2')->data(['title' => 'Two'])->create();

        $collection->structureContents(['foo' => 'bar'])->save();
        $collection->structure()->in('en')->tree([
            ['entry' => '1', 'url' => '/1', 'children' => [
                ['entry' => '1-1', 'url' => '/1/1', 'children' => [
                    ['entry' => '1-1-1', 'url' => '/1/1/1', 'children' => [
                        ['entry' => '1-1-1-1', 'url' => '/1/1/1/1'],
                    ]],
                ]],
            ]],
            ['entry' => '2', 'url' => '/2'],
        ])->save();

        $ids = ['1', '1-1', '1-1-1', '1-1-1-1', '2'];

        $builder = $this->mock(QueryBuilder::class);
        $builder->shouldReceive('whereIn')->with('id', $ids)->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(collect([$page_1, $page_1_1, $page_1_1_1, $page_1_1_1_1, $page_2]));
        Entry::shouldReceive('query')->andReturn($builder);

        $template = '{{ nav }}[{{ id }}{{ if is_parent }}=parent{{ /if }}{{ if is_current }}=current{{ /if }}]{{ if children }}{{ *recursive children* }}{{ /if }}{{ /nav }}';

        $mock = \Mockery::mock(\Statamic\Facades\URL::getFacadeRoot())->makePartial();
        \Statamic\Facades\URL::swap($mock);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[1][1-1][1-1-1][1-1-1-1][2]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/other');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[1][1-1][1-1-1][1-1-1-1][2]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/2');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[1][1-1][1-1-1][1-1-1-1][2=current]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/1');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[1=current][1-1][1-1-1][1-1-1-1][2]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/1/1');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[1=parent][1-1=current][1-1-1][1-1-1-1][2]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/1/1/1');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[1=parent][1-1=parent][1-1-1=current][1-1-1-1][2]', $result);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/1/1/1/1');
        $result = (string) Antlers::parse($template);
        $this->assertEquals('[1=parent][1-1=parent][1-1-1=parent][1-1-1-1=current][2]', $result);
    }

    #[Test]
    public function it_doesnt_render_anything_when_nav_from_is_invalid()
    {
        $this->createCollectionAndNav();
        Entry::shouldReceive('findByUri')->andReturn(null);

        // The html uses <i> tags (could be any tag, but i is short) to prevent whitespace comparison issues in the assertion.
        $template = <<<'EOT'
<ul>
{{ nav from="something-invalid" }}
    <li>
        <i>{{ title }}</i>
        {{ if children }}
        <ul>
            {{ *recursive children* }}
        </ul>
        {{ /if }}
    </li>
{{ /nav }}
</ul>
EOT;

        $expected = <<<'EOT'
<ul>
    <li>
        <i>outer title</i>
    </li>
</ul>
EOT;

        $this->assertXmlStringEqualsXmlString($expected, (string) Antlers::parse($template, [
            'title' => 'outer title', // to test that cascade the page's data takes precedence over the cascading data.
        ]));
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
