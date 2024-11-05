<?php

namespace Tests\View\Blade\AntlersComponents;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\FakesViews;
use Tests\TestCase;

#[Group('blade-compiler')]
class PartialCompilerTest extends TestCase
{
    use FakesViews;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withFakeViews();
        $this->artisan('view:clear');

    }

    #[Test]
    public function it_compiles_partial_tags()
    {
        $alert = <<<'ALERT'
<div>{{ $title }}</div>
ALERT;
        $this->viewShouldReturnRaw('alert', $alert, 'blade.php');

        $expected = '<div>The Title</div>';

        $this->assertSame(
            $expected,
            Blade::render('<s:partial:alert />', ['title' => 'The Title'])
        );

        $this->assertSame(
            $expected,
            Blade::render('<s:partial:alert></s:partial:alert>', ['title' => 'The Title'])
        );

        $expected = '<div>Custom Title</div>';

        $this->assertSame(
            $expected,
            Blade::render('<s:partial:alert title="Custom Title" />', ['title' => 'The Title'])
        );

        $this->assertSame(
            $expected,
            Blade::render('<s:partial:alert title="Custom Title"></s:partial:alert>', ['title' => 'The Title'])
        );
    }

    #[Test]
    public function it_compiles_slots()
    {
        $alert = <<<'ALERT'
<div>{{ $slot }}</div>
ALERT;
        $this->viewShouldReturnRaw('alert', $alert);

        $template = <<<'BLADE'
<s:partial:alert>
  I am the slot content.
</s:partial:alert>
BLADE;

        $this->assertSame(
            '<div>I am the slot content.</div>',
            Blade::render($template)
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
        $this->viewShouldReturnRaw('alert', $alert);

        $template = <<<'BLADE'
<s:partial:alert>
  <s:slot:header>The header</s:slot:header>
  <s:slot.footer>The footer</s:slot.footer>
  I am the slot content.
</s:partial:alert>
BLADE;

        $expected = <<<'EXPECTED'
<div id="header">The header</div>
<div>I am the slot content.</div>
<div id="footer">The footer</div>
EXPECTED;

        $this->assertSame(
            $expected,
            Blade::render($template)
        );
    }

    #[Test]
    public function it_forwards_exists_method_calls()
    {
        $template = <<<'TEMPLATE'
<s:partial:exists src="alert">Yes</s:partial:exists>
TEMPLATE;

        $this->assertSame('', Blade::render($template));

        $this->viewShouldReturnRaw('alert', 'some content');

        $this->assertSame('Yes', Blade::render($template));
    }

    #[Test]
    public function it_forwards_if_exists_method_calls()
    {
        $template = <<<'TEMPLATE'
<s:partial:if_exists src="alert" />
TEMPLATE;

        $this->assertSame('', Blade::render($template));

        $this->viewShouldReturnRaw('alert', 'some content');

        $this->assertSame('some content', Blade::render($template));
    }

    #[Test]
    public function it_compiles_when_parameter()
    {
        $this->viewShouldReturnRaw('the_partial', 'The content');

        $template = <<<'TEMPLATE'
<s:partial:the_partial :when="$theValue" />
TEMPLATE;

        $this->assertSame('', Blade::render($template, ['theValue' => false]));
        $this->assertSame('The content', Blade::render($template, ['theValue' => true]));
    }

    #[Test]
    public function it_compiles_unless_parameter()
    {
        $this->viewShouldReturnRaw('the_partial', 'The content');

        $template = <<<'TEMPLATE'
<s:partial:the_partial :unless="$theValue" />
TEMPLATE;

        $this->assertSame('', Blade::render($template, ['theValue' => true]));
        $this->assertSame('The content', Blade::render($template, ['theValue' => false]));
    }

    #[Test]
    public function it_compiles_conditional_parameters_with_slots()
    {
        $alert = <<<'ALERT'
<div id="header">{{ $header }}</div>
<div>{{ $slot }}</div>
<div id="footer">{{ $footer }}</div>
ALERT;
        $this->viewShouldReturnRaw('alert', $alert);

        $template = <<<'BLADE'
<s:partial:alert :when="$theValue">
  <s:slot:header>The header</s:slot:header>
  <s:slot.footer>The footer</s:slot.footer>
  I am the slot content.
</s:partial:alert>
BLADE;

        $expected = <<<'EXPECTED'
<div id="header">The header</div>
<div>I am the slot content.</div>
<div id="footer">The footer</div>
EXPECTED;

        $this->assertSame('', Blade::render($template, ['theValue' => false]));
        $this->assertSame($expected, Blade::render($template, ['theValue' => true]));
    }

    #[Test]
    public function it_compiles_nested_partials()
    {
        $alert = <<<'ANTLERS'
<div id="header">{{ $header }}</div>
<div>{{ slot }}</div>
<div id="footer">{{ $footer }}</div>
ANTLERS;

        $this->viewShouldReturnRaw('alert', $alert);

        $template = <<<'BLADE'
<s:partial:alert>
  <s:slot:header>The header</s:slot:header>
  <s:slot.footer>The footer</s:slot.footer>
  I am the slot content.
  
  <s:partial:alert>
    <s:slot:header>The header</s:slot:header>
    <s:slot.footer>The footer2</s:slot.footer>
    I am the second slot content.
  </s:partial:alert>
</s:partial:alert>
BLADE;

        $expected = <<<'EXPECTED'
<div id="header">The header</div>
<div>I am the slot content.
  
  <div id="header">The header</div>
<div>I am the second slot content.</div>
<div id="footer">The footer2</div></div>
<div id="footer">The footer</div>
EXPECTED;

        $this->assertSame(
            $expected,
            Blade::render($template)
        );
    }

    #[Test]
    public function it_merges_frontmatter_using_directive()
    {
        $this->withFakeViews();

        $partial = <<<'BLADE'
@frontmatter([
  'name'  => 'The Name!',
  'image' => 'https://example.com/placeholder.png',
])

Without view: {{ $name ?? '' }} {{ $image ?? '' }} |
With view: {{ $view['name'] }} {{ $view['image'] }}
BLADE;

        $this->viewShouldReturnRaw('the_partial', $partial, 'blade.php');

        $this->assertSame(
            'Without view: | With view: The Name! https://example.com/placeholder.png',
            Str::squish(Blade::render('<s:partial:the_partial />'))
        );

        $this->assertSame(
            'Without view: A different name! | With view: A different name! https://example.com/placeholder.png',
            Str::squish(Blade::render('<s:partial:the_partial name="A different name!" />'))
        );
    }

    #[Test]
    public function frontmatter_populates_view_array()
    {
        $this->withFakeViews();

        $partial = <<<'BLADE'
@frontmatter([
  'name'  => 'The Name!',
  'image' => 'https://example.com/placeholder.png',
])

{{ $view['name'] }} {{ $view['image'] }}
BLADE;

        $this->viewShouldReturnRaw('the_partial', $partial, 'blade.php');

        $this->assertSame(
            'The Name! https://example.com/placeholder.png',
            Blade::render('<s:partial:the_partial />')
        );

        $this->assertSame(
            'A different name! https://example.com/placeholder.png',
            Blade::render('<s:partial:the_partial name="A different name!" />')
        );
    }

    #[Test]
    public function it_compiles_nested_self_closing_partial_tags()
    {
        $this->withFakeViews();

        $this->viewShouldReturnRaw('one', 'Just Some Text', 'blade.php');

        $this->viewShouldReturnRaw('two', '{{ $slot }}', 'blade.php');

        $template = <<<'BLADE'
<s:partial:one />
|
<s:partial:two>
Some More Text
|
    <s:partial:one />
    | After Nested Partial Call
</s:partial:two>
BLADE;

        $this->assertSame(
            'Just Some Text| Some More Text | Just Some Text | After Nested Partial Call',
            Str::squish(Blade::render($template))
        );

        $template = <<<'BLADE'
<s:partial:one />
|
Partial Two Call One
<s:partial:two>
Some More Text
|
    <s:partial:one />
    | After Nested Partial Call
    | Partial Two Call Two
    |
    <s:partial:two>
Some Even More Text
|
    <s:partial:one />
    | After Another Nested Partial Call
</s:partial:two>
</s:partial:two>
BLADE;

        $this->assertSame(
            'Just Some Text| Partial Two Call One Some More Text | Just Some Text | After Nested Partial Call | Partial Two Call Two | Some Even More Text | Just Some Text | After Another Nested Partial Call',
            Str::squish(Blade::render($template))
        );
    }

    #[Test]
    public function slot_content_does_not_need_to_be_manually_escaped()
    {
        $this->withFakeViews();

        $partial = <<<'BLADE'
Partial Start {{ $slot }} Partial End
BLADE;

        $this->viewShouldReturnRaw('the_partial', $partial, 'blade.php');

        $template = <<<'BLADE'
<s:partial:the_partial>
<strong>I am the slot content!</strong>
</s:partial:the_partial>
BLADE;

        $this->assertSame(
            'Partial Start <strong>I am the slot content!</strong> Partial End',
            Blade::render($template),
        );
        $partial = <<<'BLADE'
Header Start {{ $header }} Header End
Partial Start {{ $slot }} Partial End
BLADE;

        $this->viewShouldReturnRaw('the_partial', $partial, 'blade.php');

        $template = <<<'BLADE'
<s:partial:the_partial>
<s:slot:header>I am <em>the header!</em></s:slot:header>
<strong>I am the slot content!</strong>
</s:partial:the_partial>
BLADE;

        $expected = <<<'EXPECTED'
Header Start I am <em>the header!</em> Header End
Partial Start <strong>I am the slot content!</strong> Partial End
EXPECTED;

        $this->assertSame(
            $expected,
            Blade::render($template),
        );
    }
}
