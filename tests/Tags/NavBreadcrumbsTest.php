<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Parse;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class NavBreadcrumbsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $collection = tap(Collection::make('pages')->routes('{parent_uri}/{slug}')->structureContents(['root' => true]))->save();

        EntryFactory::collection('pages')->id('home')->slug('home')->data(['title' => 'Home'])->create();
        EntryFactory::collection('pages')->id('about')->slug('about')->data(['title' => 'About'])->create();
        EntryFactory::collection('pages')->id('team')->slug('team')->data(['title' => 'Team'])->create();

        $collection->structure()->in('en')->tree([
            ['entry' => 'home'],
            ['entry' => 'about', 'children' => [
                ['entry' => 'team'],
            ]],
        ])->save();
    }

    private function tag($tag)
    {
        return (string) Parse::template($tag, [], trusted: true);
    }

    #[Test]
    public function it_includes_home_by_default()
    {
        $this->get('/about/team');

        $titles = $this->tag('{{ nav:breadcrumbs }}{{ title }}|{{ /nav:breadcrumbs }}');

        $this->assertSame('Home|About|Team|', $titles);
    }

    #[Test]
    public function it_excludes_home_when_include_home_is_false()
    {
        $this->get('/about/team');

        $titles = $this->tag('{{ nav:breadcrumbs include_home="false" }}{{ title }}|{{ /nav:breadcrumbs }}');

        $this->assertSame('About|Team|', $titles);
    }

    #[Test]
    public function it_excludes_home_when_include_home_is_false_on_a_top_level_page()
    {
        $this->get('/about');

        $titles = $this->tag('{{ nav:breadcrumbs include_home="false" }}{{ title }}|{{ /nav:breadcrumbs }}');

        $this->assertSame('About|', $titles);
    }

    #[Test]
    public function it_returns_no_breadcrumbs_on_the_home_page_when_include_home_is_false()
    {
        $this->get('/');

        $titles = $this->tag('{{ nav:breadcrumbs include_home="false" }}{{ title }}|{{ /nav:breadcrumbs }}');

        $this->assertSame('', $titles);
    }
}
