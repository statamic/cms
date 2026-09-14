<?php

namespace Tests\View\Scaffolding;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\View\Scaffolding\Emitters\AntlersSourceEmitter;
use Statamic\View\Scaffolding\Emitters\BladeSourceEmitter;
use Statamic\View\Scaffolding\TemplateGenerator;
use Stringable;
use Tests\TestCase;

class TemplateGeneratorTest extends TestCase
{
    #[Test]
    public function it_scaffolds_a_simple_text_field()
    {
        $field = new Field('title', ['type' => 'text']);

        $generator = new TemplateGenerator;
        $generator->withCoreGenerators();

        $result = $generator->scaffoldField($field);

        $this->assertEquals('{{ title /}}', $result);
    }

    #[Test]
    public function it_scaffolds_multiple_fields()
    {
        $fields = [
            new Field('title', ['type' => 'text']),
            new Field('content', ['type' => 'textarea']),
        ];

        $generator = new TemplateGenerator;
        $generator->withCoreGenerators();

        $this->assertEquals(
            "{{ title /}}\n{{ content /}}",
            $generator->scaffoldFields($fields),
        );
    }

    #[Test]
    public function it_scaffolds_a_blueprint()
    {
        $blueprint = Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'fields' => [
                        ['handle' => 'title', 'field' => ['type' => 'text']],
                        ['handle' => 'content', 'field' => ['type' => 'textarea']],
                    ],
                ],
            ],
        ]);

        $generator = new TemplateGenerator;
        $generator->withCoreGenerators();

        $this->assertEquals(
            "{{ title /}}\n{{ content /}}",
            $generator->scaffoldBlueprint($blueprint),
        );
    }

    #[Test]
    public function it_auto_discovers_fieldtypes()
    {
        $generator = new TemplateGenerator;
        $generator->withCoreGenerators();

        $textField = new Field('test', ['type' => 'text']);

        $this->assertSame(
            '{{ test /}}',
            $generator->scaffoldField($textField)
        );
    }

    #[Test]
    public function it_handles_unknown_fieldtypes_gracefully()
    {
        $field = new Field('unknown_field', ['type' => 'something_random_here_that_probably_doesnt_exist']);

        $generator = new TemplateGenerator;

        $this->assertEmpty($generator->scaffoldField($field));
    }

    #[Test]
    public function it_can_add_custom_generators()
    {
        $generator = new TemplateGenerator;

        $generator->addGenerator('text', function ($field) {
            return "CUSTOM: {{ {$field->handle()} }}";
        });

        $field = new Field('test', ['type' => 'text']);

        $this->assertEquals(
            'CUSTOM: {{ test }}',
            $generator->scaffoldField($field)
        );
    }

    #[Test]
    public function it_trims_whitespace_from_results()
    {
        $field = new Field('title', ['type' => 'text']);

        $generator = new TemplateGenerator;
        $generator->withCoreGenerators();

        $result = $generator->scaffoldField($field);

        $this->assertEquals(trim($result), $result);
    }

    #[Test]
    public function it_handles_stringable_returns()
    {
        $generator = new TemplateGenerator;

        $generator->addGenerator('text', function () {
            return new class implements Stringable
            {
                public function __toString(): string
                {
                    return '{{ norwegian_blue }}';
                }
            };
        });

        $field = new Field('test', ['type' => 'text']);

        $result = $generator->scaffoldField($field);

        $this->assertEquals(
            '{{ norwegian_blue }}',
            $result
        );
    }

    #[Test]
    public function it_gets_template_language()
    {
        $generator = new TemplateGenerator;

        $this->assertEquals('antlers', $generator->templateLanguage());
    }

    #[Test]
    public function it_creates_antlers_emitter_by_default()
    {
        $generator = new TemplateGenerator;

        $emitter = $generator->getEmitter();

        $this->assertInstanceOf(AntlersSourceEmitter::class, $emitter);
    }

    #[Test]
    public function it_creates_blade_emitter_when_language_is_blade()
    {
        $generator = new TemplateGenerator;
        $generator->templateLanguage('blade');

        $emitter = $generator->getEmitter();

        $this->assertInstanceOf(BladeSourceEmitter::class, $emitter);
    }

    #[Test]
    public function it_configures_line_ending()
    {
        $generator = new TemplateGenerator;
        $generator->lineEnding('CRLF');

        $emitter = $generator->getEmitter();

        $this->assertEquals("\r\n", $emitter->getLineEnding());
    }

    #[Test]
    public function it_configures_indent_type_to_tabs()
    {
        $generator = new TemplateGenerator;
        $generator->indentType('tab');

        $emitter = $generator->getEmitter();

        $this->assertEquals("\t", $emitter->getIndentChar());
    }

    #[Test]
    public function it_configures_indent_type_to_spaces()
    {
        $generator = new TemplateGenerator;
        $generator->indentType('space');

        $emitter = $generator->getEmitter();

        $this->assertEquals(' ', $emitter->getIndentChar());
    }

    #[Test]
    public function it_configures_indent_size()
    {
        $generator = new TemplateGenerator;
        $generator->indentSize(2);

        $emitter = $generator->getEmitter();

        $this->assertEquals(2, $emitter->getIndentSize());
    }

    #[Test]
    public function it_configures_final_newline()
    {
        $generator = new TemplateGenerator;
        $generator->finalNewline(true);

        $emitter = $generator->getEmitter();

        $this->assertTrue($emitter->getFinalNewline());
    }

    #[Test]
    public function it_configures_prefer_component_syntax()
    {
        $generator = new TemplateGenerator;
        $generator->preferComponentSyntax(true);

        $emitter = $generator->getEmitter();

        $this->assertTrue($emitter->getPreferComponentSyntax());
    }

    #[Test]
    public function it_chains_configuration_methods()
    {
        $generator = new TemplateGenerator;

        $result = $generator
            ->templateLanguage('blade')
            ->lineEnding('LF')
            ->indentType('tab')
            ->indentSize(2)
            ->finalNewline(true)
            ->preferComponentSyntax(false);

        $this->assertSame($generator, $result);

        $emitter = $generator->getEmitter();

        $this->assertInstanceOf(BladeSourceEmitter::class, $emitter);
        $this->assertEquals("\n", $emitter->getLineEnding());
        $this->assertEquals("\t", $emitter->getIndentChar());
        $this->assertEquals(2, $emitter->getIndentSize());
        $this->assertTrue($emitter->getFinalNewline());
        $this->assertFalse($emitter->getPreferComponentSyntax());
    }

    #[Test]
    public function it_creates_fresh_emitter_on_each_call()
    {
        $generator = new TemplateGenerator;

        $emitter1 = $generator->getEmitter();
        $emitter2 = $generator->getEmitter();

        $this->assertNotSame($emitter1, $emitter2);
    }

    #[Test]
    public function it_applies_configuration_to_each_new_emitter()
    {
        $generator = new TemplateGenerator;
        $generator->indentSize(3);

        $emitter1 = $generator->getEmitter();
        $emitter2 = $generator->getEmitter();

        $this->assertEquals(3, $emitter1->getIndentSize());
        $this->assertEquals(3, $emitter2->getIndentSize());
        $this->assertNotSame($emitter1, $emitter2);
    }

    #[Test]
    public function it_only_overrides_configured_settings()
    {
        $generator = new TemplateGenerator;

        $generator->indentSize(6);

        $emitter = $generator->getEmitter();

        $this->assertEquals(6, $emitter->getIndentSize());

        $this->assertNotNull($emitter->getLineEnding());
        $this->assertNotNull($emitter->getIndentChar());
        $this->assertIsBool($emitter->getFinalNewline());
        $this->assertIsBool($emitter->getPreferComponentSyntax());
    }

    #[Test]
    public function it_supports_different_line_ending_formats()
    {
        $testCases = [
            'LF' => "\n",
            'CRLF' => "\r\n",
            'CR' => "\r",
            'auto' => PHP_EOL,
        ];

        foreach ($testCases as $input => $expected) {
            $generator = new TemplateGenerator;
            $generator->lineEnding($input);

            $emitter = $generator->getEmitter();

            $this->assertEquals($expected, $emitter->getLineEnding(), "Failed for line ending: {$input}");
        }
    }

    #[Test]
    public function it_allows_changing_configuration_between_emitter_calls()
    {
        $generator = new TemplateGenerator;

        $generator->indentSize(2);
        $emitter1 = $generator->getEmitter();
        $this->assertEquals(2, $emitter1->getIndentSize());

        $generator->indentSize(4);
        $emitter2 = $generator->getEmitter();
        $this->assertEquals(4, $emitter2->getIndentSize());
    }
}
