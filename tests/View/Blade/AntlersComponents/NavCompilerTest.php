<?php

namespace Tests\View\Blade\AntlersComponents;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Nav;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('blade-compiler')]
class NavCompilerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('view:clear');
        $this->makeNavTree();
    }

    private function makeNavTree()
    {
        $tree = [
            ['id' => 'home', 'title' => 'Home', 'url' => '/'],
            [
                'id' => 'about', 'title' => 'About', 'url' => 'about',
                'children' => [
                    ['id' => 'team', 'title' => 'Team', 'url' => 'team'],
                    ['id' => 'leadership', 'title' => 'Leadership', 'url' => 'leadership'],
                ],
            ],
            [
                'id' => 'projects', 'title' => 'Projects', 'url' => 'projects',
                'children' => [
                    ['id' => 'project-1', 'title' => 'Project-1', 'url' => 'project-1'],
                    [
                        'id' => 'project-2', 'title' => 'Project-2', 'url' => 'project-2',
                        'children' => [
                            ['id' => 'project-2-nested', 'title' => 'Project 2 Nested', 'url' => 'project-2-nested'],
                        ],
                    ],
                ],
            ],
            ['id' => 'contact', 'title' => 'Contact', 'url' => 'contact'],
        ];

        $nav = Nav::make('main');
        $nav->makeTree('en', $tree)->save();
        $nav->save();
    }

    #[Test]
    public function it_renders_simple_navs()
    {
        $template = <<<'BLADE'
<ul>
<s:nav:main>
<li>{{ $title }}</li>
</s:nav:main>
</ul>
BLADE;

        $expected = <<<'EXPECTED'
<ul>
<li>Home</li>
<li>About</li>
<li>Projects</li>
<li>Contact</li>
</ul>
EXPECTED;

        $this->assertSame(
            $expected,
            Blade::render($template)
        );
    }

    #[Test]
    public function it_renders_simple_recursive_children()
    {
        $template = <<<'BLADE'
<ul>
<s:nav:main>
<li>
    {{ $title }} - {{ $depth }}
    
    @if (count($children) > 0)
    <ul class="the-wrapper">
        @recursive_children
    </ul>
    @endif
</li>
</s:nav:main>
</ul>
BLADE;

        $expected = <<<'EXPECTED'
<ul>
<li>
    Home - 1
    
    </li>
<li>
    About - 1
    
        <ul class="the-wrapper">
        <li>
    Team - 2
    
    </li>
<li>
    Leadership - 2
    
    </li>
    </ul>
    </li>
<li>
    Projects - 1
    
        <ul class="the-wrapper">
        <li>
    Project-1 - 2
    
    </li>
<li>
    Project-2 - 2
    
        <ul class="the-wrapper">
        <li>
    Project 2 Nested - 3
    
    </li>
    </ul>
    </li>
    </ul>
    </li>
<li>
    Contact - 1
    
    </li>
</ul>
EXPECTED;

        $this->assertSame(
            $expected,
            Blade::render($template)
        );
    }

    #[Test]
    public function it_renders_aliased_navs()
    {
        $template = <<<'BLADE'
<ul>
<s:nav:main as="the_items">
@foreach ($the_items as $item)
<li>{{ $item['title'] }}</li>
@endforeach
</s:nav:main>
</ul>
BLADE;

        $expected = <<<'EXPECTED'
<ul>
<li>Home</li>
<li>About</li>
<li>Projects</li>
<li>Contact</li>
</ul>
EXPECTED;

        $this->assertSame(
            $expected,
            Blade::render($template)
        );
    }

    #[Test]
    public function it_renders_aliased_recursive_children()
    {
        $template = <<<'BLADE'
<ul>
<s:nav:main as="the_items">
@foreach ($the_items as $item)
<li>{{ $item['title'] }} - {{ $item['depth'] }}</li>

@if (isset($item['children']) && count($item['children']))
<ul class="wrapper">
@recursive_children($item['children'])
</ul>
@endif
@endforeach
</s:nav:main>
</ul>
BLADE;

        $expected = <<<'EXPECTED'
<ul>
<li>Home - 1</li>

<li>About - 1</li>

<ul class="wrapper">
<li>Team - 2</li>

<li>Leadership - 2</li>

</ul>
<li>Projects - 1</li>

<ul class="wrapper">
<li>Project-1 - 2</li>

<li>Project-2 - 2</li>

<ul class="wrapper">
<li>Project 2 Nested - 3</li>

</ul>
</ul>
<li>Contact - 1</li>

</ul>
EXPECTED;

        $this->assertSame(
            $expected,
            Blade::render($template)
        );
    }
}
