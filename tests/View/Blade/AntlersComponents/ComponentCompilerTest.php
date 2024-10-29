<?php

namespace Tests\View\Blade\AntlersComponents;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Statamic\Facades\Collection;
use Statamic\Tags\Concerns\RendersAttributes;
use Statamic\Tags\Tags;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('blade-compiler')]
class ComponentCompilerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('view:clear');
        $this->makeTestData();
    }

    protected function makeTestData()
    {
        Collection::make('blog')->routes(['en' => '{slug}'])->save();
        EntryFactory::collection('blog')->id('1')->data(['title' => 'One'])->create();
        EntryFactory::collection('blog')->id('2')->data(['title' => 'Two'])->create();
        EntryFactory::collection('blog')->id('3')->data(['title' => 'Three'])->create();
        EntryFactory::collection('blog')->id('4')->data(['title' => 'Four'])->create();
    }

    #[Test]
    public function it_extracts_variables_inside_loops()
    {
        $template = <<<'BLADE'
<s:collection:blog sort="id:asc">{{ $title }}</s:collection:blog>
BLADE;

        $this->assertSame('OneTwoThreeFour', Blade::render($template));
    }

    #[Test]
    public function it_injects_loop_variable()
    {
        $template = <<<'BLADE'
<s:collection:blog sort="id:asc">
    @if ($loop->first)
        The First: {{ $title }} |
    @else
        {{ $title }}{{ $loop->last ? '' : ' |' }}
    @endif
</s:collection:blog>
BLADE;

        $this->assertSame(
            'The First: One | Two | Three | Four',
            Str::squish(Blade::render($template))
        );
    }

    #[Test]
    public function it_applies_scope()
    {
        $template = <<<'BLADE'
<statamic:collection:blog scope="entry" sort="id:desc">{{ $entry->title }}</statamic:collection:blog>
BLADE;

        $this->assertSame('FourThreeTwoOne', Blade::render($template));

        $template = <<<'BLADE'
<statamic:collection:blog scope="$entry" sort="id:desc">{{ $entry->title }}</statamic:collection:blog>
BLADE;

        $this->assertSame('FourThreeTwoOne', Blade::render($template));

        // I see you.
        $template = <<<'BLADE'
<statamic:collection:blog scope="$$$$$$$$entry" sort="id:desc">{{ $entry->title }}</statamic:collection:blog>
BLADE;

        $this->assertSame('FourThreeTwoOne', Blade::render($template));
    }

    #[Test]
    public function it_does_not_leak_data()
    {
        $template = <<<'BLADE'
{{ $title }}|<s:collection:blog sort="id:asc">{{ $title }}</s:collection:blog>|{{ $title }}
BLADE;

        $this->assertSame('The Title!|OneTwoThreeFour|The Title!', Blade::render($template, ['title' => 'The Title!']));
    }

    #[Test]
    public function it_does_not_allow_modifications_to_the_page_variable_from_to_persist()
    {
        $template = <<<'BLADE'
{{ $page }}|<s:collection:blog limit="1"><?php $page = 'hello, world!'; ?>{{ $page }}</s:collection:blog>|{{ $page }}
BLADE;

        $this->assertSame(
            'Running with Scissors|hello, world!|Running with Scissors',
            Blade::render($template, ['page' => 'Running with Scissors'])
        );
    }

    #[Test]
    public function it_compiles_nested_tags()
    {
        $template = <<<'BLADE'
<s:collection:blog as="posts" sort="title:desc">
   Before:
   There are {{ count($posts) }} posts.
   
   <s:collection:blog as="posts" sort="title:asc" limit="2">
   There are {{ count($posts) }} posts.
    @foreach ($posts as $post) {{ $post->title }} @endforeach
   </s:collection:blog>
   After:
   
   {{-- The original $posts array should be restored. --}}
   There are {{ count($posts) }} posts.
   @foreach ($posts as $post) {{ $post->title }} @endforeach
   
</s:collection:blog>
BLADE;

        $this->assertSame(
            'Before: There are 4 posts. There are 2 posts. Four One After: There are 4 posts. Two Three One Four',
            Str::squish(Blade::render($template))
        );
    }

    #[Test]
    public function it_compiles_self_closing_tags()
    {
        $template = <<<'BLADE'
<s:collection:count from="blog" />
BLADE;

        $this->assertSame(
            '4',
            Blade::render($template)
        );
    }

    #[Test]
    public function it_compiles_shorthand_variable_parameters()
    {
        $template = <<<'BLADE'
<s:collection :$from sort="id:asc">{{ $title }}</s:collection>
BLADE;

        $this->assertSame(
            'OneTwoThreeFour',
            Blade::render($template, ['from' => 'blog'])
        );
    }

    #[Test]
    public function test_it_compiles_escaped_parameters()
    {
        (new class extends Tags
        {
            use RendersAttributes;

            protected static $handle = 'test';

            public function index()
            {
                $params = $this->renderAttributesFromParams(except: ['src']);

                return $params.'|'.$this->params->get('src');
            }
        })::register();

        // Internally Blade's escaped param syntax will
        // be converted to the attr:src="$test" form
        // that existing Tags implementations use
        $template = <<<'BLADE'
<s:test :src="$test" ::src="$test" />
BLADE;

        $this->assertSame(
            ':src="$test"|the test',
            Blade::render($template, ['test' => 'the test'])
        );
    }

    #[Test]
    public function it_compiles_interpolated_parameters()
    {
        $template = <<<'BLADE'
<s:collection from="{{ $from }}" sort="id:asc">{{ $title }}</s:collection>
BLADE;

        $this->assertSame(
            'OneTwoThreeFour',
            Blade::render($template, ['from' => 'blog'])
        );
    }

    #[Test]
    public function it_can_alias_results()
    {
        $template = <<<'BLADE'
<s:collection:blog sort="id:asc" as="entries">
    @if (! isset($title)) Should not have a title @endif
    @foreach ($entries as $entry)
    {{ $entry->title }}
    @endforeach
</s:collection:blog>
BLADE;

        $result = Str::squish(Blade::render($template));
        $this->assertSame('Should not have a title One Two Three Four', $result);

        $template = <<<'BLADE'
<s:collection:blog sort="id:asc" as="$entries">
    @if (! isset($title)) Should not have a title @endif
    @foreach ($entries as $entry)
    {{ $entry->title }}
    @endforeach
</s:collection:blog>
BLADE;

        $result = Str::squish(Blade::render($template));
        $this->assertSame('Should not have a title One Two Three Four', $result);
    }

    #[Test]
    public function it_allows_variables_to_be_updated()
    {
        $template = <<<'BLADE'
<?php $myFancyCounter = 0; ?>
<s:collection:blog sort="id:asc" as="entries">
    @foreach ($entries as $entry)
    <?php $myFancyCounter++; ?>
    @endforeach
</s:collection:blog>
{{ $myFancyCounter }}
BLADE;

        $this->assertSame('4', trim(Blade::render($template)));
    }

    #[Test]
    public function it_passes_full_tag_name()
    {
        (new class extends Tags
        {
            protected static $handle = 'my_tag';

            public function wildcard()
            {
                return $this->tag;
            }
        })::register();

        $this->assertSame(
            'my_tag:the_method',
            Blade::render('<s:my_tag:the_method />'),
        );
    }

    #[Test]
    public function it_compiles_no_results()
    {
        (new class extends Tags
        {
            protected static $handle = 'my_tag';

            public function index()
            {
                return $this->context['the_array'];
            }
        })::register();

        $template = <<<'BLADE'
<s:my_tag>
  {{ $value }}
  
  <s:no_results>
     {{ $title }} Nothing to see here!
  </s:no_results>
</s:my_tag>
BLADE;

        $this->assertSame(
            'a b c',
            Str::squish(Blade::render($template, ['the_array' => ['a', 'b', 'c']])),
        );

        $this->assertSame(
            'Hello! Nothing to see here!',
            Str::squish(Blade::render($template, ['the_array' => [], 'title' => 'Hello!'])),
        );
    }

    #[Test]
    public function it_detects_blade_vs_antlers()
    {
        (new class extends Tags
        {
            protected static $handle = 'my_tag';

            public function index()
            {
                if ($this->isAntlersBladeComponent()) {
                    return 'Hello, Blade!';
                }

                return 'Hello, Antlers!';
            }
        })::register();

        $this->assertSame(
            'Hello, Blade!',
            Blade::render('<s:my_tag />'),
        );

        $this->assertSame(
            'Hello, Antlers!',
            (string) Antlers::parse('{{ my_tag }}'),
        );
    }

    #[Test]
    public function it_supports_void_params()
    {
        (new class extends Tags
        {
            protected static $handle = 'my_tag';

            public function index()
            {
                if ($this->params->has('the_param')) {
                    return 'It does!';
                }

                return 'It does not.';
            }
        })::register();

        $template = <<<'BLADE'
@php
  use function \Statamic\View\Blade\{void};
@endphp
<s:my_tag
  :the_param="$do_include ? 'Yes' : void()"
/>
BLADE;

        $this->assertSame(
            'It does!',
            Blade::render($template, ['do_include' => true]),
        );

        $this->assertSame(
            'It does not.',
            Blade::render($template, ['do_include' => false]),
        );
    }

    #[Test]
    public function it_correctly_compiles_nested_self_closing_tags()
    {
        (new class extends Tags
        {
            protected static $handle = 'my_tag';

            public function index()
            {
                if (! $this->isPair) {
                    return 'Just a self-closing tag.';
                }

                return [['title' => 'One'], ['title' => 'Two']];
            }
        })::register();

        $template = <<<'BLADE'
<s:my_tag />|
<s:my_tag>
{{ $title }}
<s:my_tag />|
</s:my_tag>
BLADE;

        $this->assertSame(
            'Just a self-closing tag.| One Just a self-closing tag.| Two Just a self-closing tag.|',
            Str::squish(Blade::render($template)),
        );
    }
}
