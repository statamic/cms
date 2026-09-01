<?php

namespace Tests\View\Blade\AntlersComponents;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Tags\Tags;
use Tests\FakesViews;
use Tests\TestCase;

#[Group('blade-compiler')]
class IncludeCompilerTest extends TestCase
{
    use FakesViews;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withFakeViews();
        $this->artisan('view:clear');
    }

    #[Test]
    public function it_compiles_include_tags()
    {
        $this->viewShouldReturnRaw('alert', '<div>{{ $title }}</div>', 'blade.php');

        $expected = '<div>The Title</div>';

        $this->assertSame($expected, Blade::render('<s:include:alert title="The Title" />'));
        $this->assertSame($expected, Blade::render('<s:include:alert title="The Title"></s:include:alert>'));
    }

    #[Test]
    public function it_compiles_include_tags_with_a_src_parameter()
    {
        $this->viewShouldReturnRaw('alert', '<div>{{ $title }}</div>', 'blade.php');

        $this->assertSame('<div>The Title</div>', Blade::render('<s:include src="alert" title="The Title" />'));
    }

    #[Test]
    public function it_compiles_a_view_named_index()
    {
        $this->viewShouldReturnRaw('index', 'IDX');

        $this->assertSame('IDX', Blade::render('<s:include:index />'));
        $this->assertSame('IDX', Blade::render('<s:include src="index" />'));
    }

    #[Test]
    public function it_does_not_capture_the_caller_scope()
    {
        $this->viewShouldReturnRaw('alert', '[{{ $secret ?? "none" }}][{{ $passed ?? "none" }}]');

        $this->assertSame(
            '[none][yes]',
            Blade::render('<s:include:alert passed="yes" />', ['secret' => 'LEAK'])
        );
    }

    #[Test]
    public function it_compiles_slots()
    {
        $this->viewShouldReturnRaw('alert', '<div>{{ $slot }}</div>');

        $template = <<<'BLADE'
<s:include:alert>
  I am the slot content.
</s:include:alert>
BLADE;

        $this->assertSame('<div>I am the slot content.</div>', Blade::render($template));
        $this->assertSame(
            '<div>Title</div>',
            Blade::render('<s:include:alert title="Title">{{ $params[\'title\'] }}</s:include:alert>')
        );
    }

    #[Test]
    public function slot_content_sees_the_caller_scope()
    {
        $this->viewShouldReturnRaw('alert', '<div>{{ $slot }}</div>');

        $this->assertSame(
            '<div>LEAK</div>',
            Blade::render('<s:include:alert>{{ $secret }}</s:include:alert>', ['secret' => 'LEAK'])
        );
    }

    #[Test]
    public function it_compiles_named_slots()
    {
        $alert = <<<'ALERT'
<div id="header">{{ $header }}</div>
<div>{{ $slot }}</div>
<div id="footer">{{ $footer }}</div>
ALERT;
        $this->viewShouldReturnRaw('alert', $alert, 'blade.php');

        $template = <<<'BLADE'
<s:include:alert>
  <s:slot:header>The header</s:slot:header>
  <s:slot.footer>The footer</s:slot.footer>
  I am the slot content.
</s:include:alert>
BLADE;

        $expected = <<<'EXPECTED'
<div id="header">The header</div>
<div>I am the slot content.</div>
<div id="footer">The footer</div>
EXPECTED;

        $this->assertSame($expected, Blade::render($template));
    }

    #[Test]
    public function it_rejects_duplicate_named_slots()
    {
        $this->viewShouldReturnRaw('w', '<d>{{ $slot }}</d>', 'blade.php');

        $template = <<<'BLADE'
<s:include:w>
    <s:slot:a>AAA</s:slot:a>D1
    <s:slot:b>BBB</s:slot:b>D2
    <s:slot:c>CCC</s:slot:c>D3
    <s:slot:a>AAA2</s:slot:a>
</s:include:w>
BLADE;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The include tag cannot define the [a] slot more than once.');

        Blade::render($template);
    }

    #[Test]
    public function it_compiles_scoped_slots()
    {
        $this->viewShouldReturnRaw('list', "@foreach(\$rows as \$person)<s:slot:row :name=\"\$person['name']\" :index=\"\$loop->iteration\" />@endforeach", 'blade.php');

        $template = <<<'BLADE'
<s:include:list :rows="$people">
  <s:slot:row>[{{ $name }}#{{ $index }}]</s:slot:row>
</s:include:list>
BLADE;

        $this->assertSame(
            '[Alice#1][Bob#2]',
            Blade::render($template, ['people' => [['name' => 'Alice'], ['name' => 'Bob']]])
        );
    }

    #[Test]
    public function a_named_slot_can_be_output_with_the_slot_tag()
    {
        $this->viewShouldReturnRaw('card', '<header><s:slot:header /></header>', 'blade.php');

        $this->assertSame(
            '<header>Hi</header>',
            Blade::render('<s:include:card><s:slot:header>Hi</s:slot:header></s:include:card>')
        );
        $this->assertSame('<header></header>', Blade::render('<s:include:card />'));
    }

    #[Test]
    public function a_paired_named_slot_falls_back_to_its_body_when_the_slot_is_not_provided()
    {
        $this->viewShouldReturnRaw('card', '<footer><s:slot:footer>Default footer</s:slot:footer></footer>', 'blade.php');

        $this->assertSame('<footer>Default footer</footer>', Blade::render('<s:include:card />'));
    }

    #[Test]
    public function a_paired_named_slot_renders_the_provided_slot_instead_of_its_body()
    {
        $this->viewShouldReturnRaw('card', '<footer><s:slot:footer>Default footer</s:slot:footer></footer>', 'blade.php');

        $this->assertSame(
            '<footer>Provided</footer>',
            Blade::render('<s:include:card><s:slot:footer>Provided</s:slot:footer></s:include:card>')
        );
    }

    #[Test]
    public function a_paired_default_slot_falls_back_to_its_body_when_the_slot_is_not_provided()
    {
        $this->viewShouldReturnRaw('card', '<div><s:slot>Default body</s:slot></div>', 'blade.php');

        $this->assertSame('<div>Default body</div>', Blade::render('<s:include:card />'));
        $this->assertSame('<div>Provided</div>', Blade::render('<s:include:card>Provided</s:include:card>'));
    }

    #[Test]
    public function it_forwards_exists_method_calls()
    {
        $template = '<s:include:exists src="alert">Yes</s:include:exists>';

        $this->assertSame('', Blade::render($template));

        $this->viewShouldReturnRaw('alert', 'some content');

        $this->assertSame('Yes', Blade::render($template));
    }

    #[Test]
    public function it_forwards_if_exists_method_calls()
    {
        $template = '<s:include:if_exists src="alert" />';

        $this->assertSame('', Blade::render($template));

        $this->viewShouldReturnRaw('alert', 'some content');

        $this->assertSame('some content', Blade::render($template));
    }

    #[Test]
    public function it_compiles_when_parameter()
    {
        $this->viewShouldReturnRaw('the_partial', 'The content');

        $template = '<s:include:the_partial :when="$theValue" />';

        $this->assertSame('', Blade::render($template, ['theValue' => false]));
        $this->assertSame('The content', Blade::render($template, ['theValue' => true]));
    }

    #[Test]
    public function it_compiles_unless_parameter()
    {
        $this->viewShouldReturnRaw('the_partial', 'The content');

        $template = '<s:include:the_partial :unless="$theValue" />';

        $this->assertSame('', Blade::render($template, ['theValue' => true]));
        $this->assertSame('The content', Blade::render($template, ['theValue' => false]));
    }

    #[Test]
    public function it_isolates_the_caller_scope_through_nesting()
    {
        $this->viewShouldReturnRaw('outer', 'O[{{ $a ?? "none" }}]<s:include:inner />', 'blade.php');
        $this->viewShouldReturnRaw('inner', 'I[{{ $a ?? "none" }}]', 'blade.php');

        $this->assertSame('O[none]I[none]', Blade::render('<s:include:outer />', ['a' => 'CALLER']));
    }

    #[Test]
    public function a_param_does_not_leak_into_the_next_include()
    {
        $this->viewShouldReturnRaw('card', 'C[{{ $class ?? "none" }}]', 'blade.php');

        $this->assertSame(
            'C[cool]C[none]',
            Blade::render('<s:include:card class="cool" /><s:include:card />')
        );
    }

    #[Test]
    public function an_assignment_inside_an_include_does_not_leak_to_a_sibling()
    {
        $this->viewShouldReturnRaw('setter', '{{ v = "set" }}S');
        $this->viewShouldReturnRaw('getter', 'G[{{ v ?? "none" }}]');

        $this->assertSame('SG[none]', Blade::render('<s:include:setter /><s:include:getter />'));
    }

    #[Test]
    public function it_compiles_nested_includes()
    {
        $this->viewShouldReturnRaw('one', 'Just Some Text');
        $this->viewShouldReturnRaw('two', '{{ $slot }}');

        $template = <<<'BLADE'
<s:include:two>
  <s:include:one />
</s:include:two>
BLADE;

        $this->assertSame('Just Some Text', trim(Blade::render($template)));
    }

    #[Test]
    public function slot_tags_still_compile_outside_includes()
    {
        (new class extends Tags
        {
            protected static $handle = 'slot';

            public function wildcard($tag)
            {
                return 'custom';
            }
        })::register();

        $this->assertSame('custom', Blade::render('<s:slot:header />', ['header' => 'data']));

        $this->assertSame('custom', Blade::render('<s:slot:bad-name />'));
    }

    #[Test]
    public function invalid_slot_names_are_rejected()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid slot name [bad-name]');

        Blade::render('<s:include:card><s:slot:bad-name>Bad</s:slot:bad-name></s:include:card>');
    }

    #[Test]
    public function a_slot_does_not_replace_the_variables_the_include_provides()
    {
        $this->viewShouldReturnRaw('card', "@frontmatter(['fm' => 'F'])[{{ \$params['title'] }}][{{ \$view['fm'] }}][<s:slot:params />][<s:slot:view />]", 'blade.php');

        $template = <<<'BLADE'
<s:include:card title="T">
  <s:slot:params>P</s:slot:params>
  <s:slot:view>V</s:slot:view>
</s:include:card>
BLADE;

        $this->assertSame('[T][F][P][V]', trim(Blade::render($template)));
    }

    #[Test]
    public function a_partial_inside_an_include_resolves_its_own_slots()
    {
        $this->viewShouldReturnRaw('card', '<s:partial:badge><s:slot:label>FromPartial</s:slot:label></s:partial:badge>', 'blade.php');
        $this->viewShouldReturnRaw('badge', '[{{ $label }}]', 'blade.php');

        $this->assertSame(
            '[FromPartial]',
            Blade::render('<s:include:card><s:slot:label>FromInclude</s:slot:label></s:include:card>')
        );
    }

    #[Test]
    public function the_if_exists_default_slot_is_lazy()
    {
        $spy = new class extends Tags
        {
            protected static $handle = 'if_exists_spy';

            public static $count = 0;

            public function index()
            {
                self::$count++;

                return 'SPY';
            }
        };
        $spy::register();

        $this->viewShouldReturnRaw('ignores_slot', 'Card', 'blade.php');
        $this->viewShouldReturnRaw('uses_slot', 'Card[{{ $slot }}]', 'blade.php');

        $spy::$count = 0;
        $this->assertSame('Card', Blade::render('<s:include:if_exists src="ignores_slot"><s:if_exists_spy /></s:include:if_exists>'));
        $this->assertSame(0, $spy::$count);

        $spy::$count = 0;
        $this->assertSame('Card[SPY]', Blade::render('<s:include:if_exists src="uses_slot"><s:if_exists_spy /></s:include:if_exists>'));
        $this->assertSame(1, $spy::$count);
    }

    #[Test]
    public function unused_default_slots_are_not_rendered()
    {
        $spy = new class extends Tags
        {
            protected static $handle = 'include_spy';

            public static $count = 0;

            public function index()
            {
                self::$count++;

                return '';
            }
        };

        $spy::register();
        $this->viewShouldReturnRaw('card', 'Card', 'blade.php');

        $this->assertSame('Card', Blade::render('<s:include:card><s:include_spy /></s:include:card>'));
        $this->assertSame(0, $spy::$count);
    }

    #[Test]
    public function slots_named_after_framework_variables_are_not_aliased()
    {
        $this->viewShouldReturnRaw('card_env', "@forelse ([1] as \$i)\nok\n@empty\nx\n@endforelse\n[<s:slot:__env />]", 'blade.php');

        $this->assertSame('ok [E]', Str::squish(Blade::render('<s:include:card_env><s:slot:__env>E</s:slot:__env></s:include:card_env>')));
    }

    #[Test]
    public function slot_content_containing_the_hoisted_nowdoc_terminator_compiles()
    {
        $this->viewShouldReturnRaw('alert', '<div>{{ $slot }}</div>', 'blade.php');

        $this->assertSame(
            "<div>before\nCOMPILED;\nafter</div>",
            Blade::render("<s:include:alert>before\nCOMPILED;\nafter</s:include:alert>")
        );
    }

    #[Test]
    public function a_whitespace_only_body_is_not_a_slot()
    {
        $this->viewShouldReturnRaw('wrapper', '{{ if slot }}HAS{{ else }}NONE{{ /if }}');

        $this->assertSame('NONE', Blade::render("<s:include:wrapper>\n   \n</s:include:wrapper>"));
    }
}
