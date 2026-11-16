<?php

namespace Tests\View\Blade;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Cascade;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Markdown;
use Statamic\Fieldtypes\Textarea;
use Tests\TestCase;

class AntlersValueTest extends TestCase
{
    #[Test]
    public function it_parses_antlers_in_a_value_when_the_field_has_antlers_enabled()
    {
        Cascade::set('name', 'World');

        $value = $this->value(new Textarea, 'Hello {{ name }}', ['antlers' => true]);

        $this->assertSame('Hello World', Blade::render('{!! $text !!}', ['text' => $value]));
    }

    #[Test]
    public function it_doesnt_parse_antlers_in_a_value_when_the_field_has_antlers_disabled()
    {
        Cascade::set('name', 'World');

        $value = $this->value(new Textarea, 'Hello {{ name }}', ['antlers' => false]);

        $this->assertSame('Hello {{ name }}', Blade::render('{!! $text !!}', ['text' => $value]));
    }

    #[Test]
    public function it_parses_antlers_before_augmenting_a_markdown_value()
    {
        Cascade::set('name', 'World');

        $value = $this->value(new Markdown, '# Hello {{ name }}', ['antlers' => true]);

        $this->assertSame("<h1>Hello World</h1>\n", Blade::render('{!! $text !!}', ['text' => $value]));
    }

    #[Test]
    public function it_escapes_parsed_antlers_in_an_escaped_echo()
    {
        Cascade::set('name', '<b>World</b>');

        $value = $this->value(new Textarea, 'Hello {{ name }}', ['antlers' => true]);

        $this->assertSame('Hello &lt;b&gt;World&lt;/b&gt;', Blade::render('{{ $text }}', ['text' => $value]));
    }

    private function value($fieldtype, $raw, array $config)
    {
        return new Value($raw, 'text', $fieldtype->setField(new Field('text', $config)));
    }
}
