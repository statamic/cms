<?php

namespace Tests\Fieldtypes;

use Illuminate\Support\HtmlString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\AutocompleteEditor;
use Statamic\Fieldtypes\AutocompleteEditor\Mentions;
use Tests\TestCase;

class AutocompleteEditorTest extends TestCase
{
    #[Test]
    public function it_is_not_selectable()
    {
        $this->assertFalse((new AutocompleteEditor)->selectable());
    }

    #[Test]
    public function it_has_expected_config_defaults()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame('@', $fieldtype->config('trigger'));
        $this->assertNull($fieldtype->config('placeholder'));
        $this->assertFalse($fieldtype->config('inline'));
        $this->assertFalse($fieldtype->config('enable_line_breaks'));
        $this->assertTrue($fieldtype->config('markdown'));
        $this->assertSame(
            ['bold', 'italic', 'h2', 'h3', 'bulletlist', 'orderedlist'],
            $fieldtype->config('buttons')
        );
    }

    #[Test]
    public function config_values_can_be_overridden()
    {
        $fieldtype = $this->fieldtype([
            'trigger' => '#',
            'inline' => true,
            'enable_line_breaks' => true,
            'buttons' => ['bold'],
        ]);

        $this->assertSame('#', $fieldtype->config('trigger'));
        $this->assertTrue($fieldtype->config('inline'));
        $this->assertTrue($fieldtype->config('enable_line_breaks'));
        $this->assertSame(['bold'], $fieldtype->config('buttons'));
    }

    #[Test]
    public function it_processes_null_and_empty_values_to_null()
    {
        $fieldtype = $this->fieldtype();

        $this->assertNull($fieldtype->process(null));
        $this->assertNull($fieldtype->process(''));
    }

    #[Test]
    public function it_processes_a_markdown_string_unchanged()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame('Hi [[ first_name ]], thanks!', $fieldtype->process('Hi [[ first_name ]], thanks!'));
    }

    #[Test]
    public function preload_normalizes_a_list_of_plain_string_options()
    {
        $fieldtype = $this->fieldtype([
            'options' => ['red', 'green'],
        ]);

        $this->assertSame(
            [
                ['value' => 'red', 'label' => 'red'],
                ['value' => 'green', 'label' => 'green'],
            ],
            $fieldtype->preload()['options']
        );
    }

    #[Test]
    public function preload_normalizes_an_associative_array_of_options()
    {
        $fieldtype = $this->fieldtype([
            'options' => ['red' => 'Red', 'green' => 'Green'],
        ]);

        $this->assertSame(
            [
                ['value' => 'red', 'label' => 'Red'],
                ['value' => 'green', 'label' => 'Green'],
            ],
            $fieldtype->preload()['options']
        );
    }

    #[Test]
    public function preload_normalizes_key_value_rows_from_the_array_fieldtype()
    {
        $fieldtype = $this->fieldtype([
            'options' => [
                ['key' => 'red', 'value' => 'Red'],
                ['key' => 'green', 'value' => 'Green'],
            ],
        ]);

        $this->assertSame(
            [
                ['value' => 'red', 'label' => 'Red'],
                ['value' => 'green', 'label' => 'Green'],
            ],
            $fieldtype->preload()['options']
        );
    }

    #[Test]
    public function preload_accepts_values_matching_the_token_grammar()
    {
        $fieldtype = $this->fieldtype(['options' => ['first_name' => 'First', 'user.last-name' => 'Last']]);

        $this->assertSame(['first_name', 'user.last-name'], collect($fieldtype->preload()['options'])->pluck('value')->all());
    }

    #[Test]
    public function preload_casts_numeric_values_to_strings()
    {
        $fieldtype = $this->fieldtype(['options' => [1 => 'One', 2 => 'Two']]);

        $this->assertSame(['1', '2'], collect($fieldtype->preload()['options'])->pluck('value')->all());
    }

    #[Test]
    #[DataProvider('invalidOptionValues')]
    public function preload_rejects_values_outside_the_token_grammar($value)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('['.$value.']');

        $this->fieldtype(['options' => [$value => 'Label']])->preload();
    }

    public static function invalidOptionValues()
    {
        return [
            'space' => ['a b'],
            'closing brackets' => ['a]]b'],
            'empty' => [' '],
        ];
    }

    #[Test]
    public function preload_returns_an_empty_options_array_when_none_are_configured()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame([], $fieldtype->preload()['options']);
    }

    #[Test]
    public function it_augments_markdown_to_html()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame(
            "<p>Hi <strong>John</strong></p>\n",
            $fieldtype->augment('Hi **John**')
        );
    }

    #[Test]
    public function it_augments_multiple_blocks_of_markdown()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame(
            "<p>One</p>\n<p>Two</p>\n",
            $fieldtype->augment("One\n\nTwo")
        );
    }

    #[Test]
    public function it_unwraps_the_paragraph_when_inline()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);

        $this->assertSame('Hi <strong>John</strong>', $fieldtype->augment('Hi **John**'));
    }

    #[Test]
    public function it_leaves_multiple_paragraphs_wrapped_when_inline()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);

        $this->assertSame("<p>One</p>\n<p>Two</p>", $fieldtype->augment("One\n\nTwo"));
    }

    #[Test]
    public function it_augments_null_and_empty_values_to_null()
    {
        $fieldtype = $this->fieldtype();

        $this->assertNull($fieldtype->augment(null));
        $this->assertNull($fieldtype->augment(''));
    }

    #[Test]
    public function augment_delegates_to_the_parse_markdown_method()
    {
        $this->assertSame(AutocompleteEditor::parseMarkdown('Hi **John**'), $this->fieldtype()->augment('Hi **John**'));
        $this->assertSame(AutocompleteEditor::parseMarkdown('Hi **John**', inline: true), $this->fieldtype(['inline' => true])->augment('Hi **John**'));
    }

    #[Test]
    public function parse_markdown_unwraps_the_paragraph_when_inline()
    {
        $this->assertSame('<p>Hi <strong>John</strong></p>', trim(AutocompleteEditor::parseMarkdown('Hi **John**')));
        $this->assertSame('Hi <strong>John</strong>', AutocompleteEditor::parseMarkdown('Hi **John**', inline: true));
    }

    #[Test]
    public function augment_parses_markdown_without_a_field()
    {
        $this->assertSame('<p>Hi <strong>John</strong></p>', trim((new AutocompleteEditor)->augment('Hi **John**')));
    }

    #[Test]
    public function it_leaves_mention_tokens_alone_when_augmenting()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame("<p>Hi [[ first_name ]]</p>\n", $fieldtype->augment('Hi [[ first_name ]]'));
    }

    #[Test]
    public function antlers_is_parsed_after_the_markdown_is_augmented()
    {
        $fieldtype = $this->fieldtype(['antlers' => true, 'inline' => true]);

        $value = new Value('Hi **{{ first_name }}**', 'test', $fieldtype);

        $this->assertSame(
            'Hi <strong>John</strong>',
            (string) Antlers::parse('{{ test }}', ['test' => $value, 'first_name' => 'John'])
        );
    }

    #[Test]
    public function antlers_is_not_parsed_when_the_config_is_disabled()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);

        $value = new Value('Hi {{ first_name }}', 'test', $fieldtype);

        $this->assertSame(
            'Hi {{ first_name }}',
            (string) Antlers::parse('{{ test }}', ['test' => $value, 'first_name' => 'John'])
        );
    }

    #[Test]
    public function an_escaped_literal_survives_augmenting_while_a_real_mention_resolves()
    {
        $fieldtype = $this->fieldtype();

        $html = $fieldtype->augment('Hi [[ first_name ]], type \\[\\[ first_name \\]\\] literally');

        $this->assertSame(
            "<p>Hi John, type [[ first_name ]] literally</p>\n",
            html_entity_decode(Mentions::replace($html, ['first_name' => 'John']))
        );
        $this->assertStringContainsString('type &#91;&#91; first_name ]] literally', $html);
        $this->assertStringContainsString('Hi [[ first_name ]],', $html);
    }

    #[Test]
    public function an_escaped_literal_survives_augmenting_when_inline()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);

        $html = $fieldtype->augment('[[ a ]] \\[\\[ a \\]\\] **[[ a ]]**');

        $this->assertSame('1 &#91;&#91; a ]] <strong>1</strong>', Mentions::replace($html, ['a' => '1']));
    }

    #[Test]
    public function an_escaped_literal_survives_antlers_and_mention_replacement()
    {
        $fieldtype = $this->fieldtype(['antlers' => true, 'inline' => true]);

        $value = new Value('Hi [[ name ]] \\[\\[ name \\]\\] {{ other }}', 'test', $fieldtype);

        $parsed = (string) Antlers::parse('{{ test }}', ['test' => $value, 'other' => 'X']);

        $this->assertSame('Hi John &#91;&#91; name ]] X', Mentions::replace($parsed, ['name' => 'John']));
    }

    #[Test]
    public function mention_tokens_keep_their_original_spacing_and_work_in_links_and_code()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);

        $html = $fieldtype->augment('[[a]] [[   b   ]] [x]([[ c ]]) `[[ d ]]`');

        $this->assertSame('[[a]] [[   b   ]] <a href="[[ c ]]">x</a> <code>[[ d ]]</code>', $html);
    }

    #[Test]
    public function an_escaped_backslash_before_a_real_token_still_resolves()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);

        $html = $fieldtype->augment('\\\\[[ a ]]');

        $this->assertSame('&#92;[[ a ]]', $html);
        $this->assertSame('&#92;1', Mentions::replace($html, ['a' => '1']));
    }

    #[Test]
    public function augmented_markdown_resolves_the_same_as_before_plain_text_escapes_existed()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);
        $values = ['a' => '1'];

        $cases = [
            '[[ a ]]' => '1',
            'x \\[\\[ a \\]\\] [[ a ]]' => 'x &#91;&#91; a ]] 1',
            '\\\\[[ a ]]' => '&#92;1',
            '\\\\\\\\[[ a ]]' => '&#92;&#92;1',
            '\\[[[ a ]]' => '[1',
            '\\\\\\[[[ a ]]' => '&#92;[1',
            '`\\[[ a ]]`' => '<code>&#92;1</code>',
        ];

        foreach ($cases as $markdown => $expected) {
            $this->assertSame($expected, Mentions::replace($fieldtype->augment($markdown), $values), $markdown);
        }
    }

    #[Test]
    public function augmented_markdown_never_leaves_a_backslash_or_bracket_where_replace_would_treat_it_as_an_escape()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);
        $values = ['a' => '1'];
        $alphabet = ['\\', '[', ']', ' ', 'x', '`', '[[ a ]]', '\\[\\['];

        $sequences = [''];

        for ($length = 1; $length <= 4; $length++) {
            $sequences = collect($sequences)->flatMap(fn ($sequence) => collect($alphabet)->map(fn ($item) => $sequence.$item))->all();

            foreach ($sequences as $markdown) {
                $html = (string) $fieldtype->augment($markdown);

                $before = preg_replace_callback(
                    Mentions::TOKEN,
                    fn ($matches) => $values[$matches[1]] ?? '',
                    str_replace('&#92;', '\\', $html)
                );

                $this->assertSame(
                    html_entity_decode($before),
                    html_entity_decode(Mentions::replace($html, $values)),
                    $markdown
                );
            }
        }
    }

    #[Test]
    public function text_resembling_the_sentinel_is_left_alone()
    {
        $fieldtype = $this->fieldtype(['inline' => true]);

        $this->assertSame('mentionabcn0e [[ a ]]', $fieldtype->augment('mentionabcn0e [[ a ]]'));
    }

    #[Test]
    public function it_augments_plain_text_unchanged_when_markdown_is_disabled()
    {
        $fieldtype = $this->fieldtype(['markdown' => false]);

        $value = "**Hi** _there_ # \\ `x` - 1. > Tom & Jerry <b> \\[[ literal ]] [[ first_name ]]\nline two";

        $this->assertSame($value, $fieldtype->augment($value));
    }

    #[Test]
    public function it_augments_null_and_empty_plain_text_to_null()
    {
        $fieldtype = $this->fieldtype(['markdown' => false]);

        $this->assertNull($fieldtype->augment(null));
        $this->assertNull($fieldtype->augment(''));
    }

    #[Test]
    public function render_html_escapes_values()
    {
        $html = AutocompleteEditor::renderHtml('Hi [[ name ]]', ['name' => '<b>Tom</b> & "Jerry" \'s'], inline: true);

        $this->assertSame('Hi &lt;b&gt;Tom&lt;/b&gt; &amp; &quot;Jerry&quot; &#039;s', $html);
    }

    #[Test]
    public function render_html_turns_newlines_in_values_into_line_breaks()
    {
        $html = AutocompleteEditor::renderHtml('Hi [[ name ]]', ['name' => "Tom\nJerry"], inline: true);

        $this->assertSame("Hi Tom<br />\nJerry", $html);
    }

    #[Test]
    public function render_html_inserts_htmlable_values_as_is()
    {
        $html = AutocompleteEditor::renderHtml('Hi [[ name ]]', ['name' => new HtmlString("<b>Tom</b>\nJerry")], inline: true);

        $this->assertSame("Hi <b>Tom</b>\nJerry", $html);
    }

    #[Test]
    public function render_html_unwraps_the_paragraph_only_when_inline()
    {
        $this->assertSame('Hi Tom', AutocompleteEditor::renderHtml('Hi [[ name ]]', ['name' => 'Tom'], inline: true));
        $this->assertSame('<p>Hi Tom</p>', trim(AutocompleteEditor::renderHtml('Hi [[ name ]]', ['name' => 'Tom'])));
    }

    #[Test]
    public function render_html_resolves_unknown_handles_to_an_empty_string()
    {
        $this->assertSame('Hi ', AutocompleteEditor::renderHtml('Hi [[ nope ]]', ['name' => 'Tom'], inline: true));
    }

    #[Test]
    public function render_html_keeps_an_authors_escaped_literal()
    {
        $html = AutocompleteEditor::renderHtml('\\[\\[ x \\]\\] and [[ x ]]', ['x' => 'X'], inline: true);

        $this->assertSame('&#91;&#91; x ]] and X', $html);
    }

    #[Test]
    public function render_html_returns_an_empty_string_for_empty_markdown()
    {
        $this->assertSame('', AutocompleteEditor::renderHtml('', ['name' => 'Tom']));
    }

    #[Test]
    public function render_html_passes_the_callback_through_without_exposing_values()
    {
        $data = ['name' => 'Tom', 'note' => 'a [[ secret ]] and {{ config:app.key }}'];

        $html = AutocompleteEditor::renderHtml(
            'Hi [[ name ]], {{ note }} / [[ note ]]',
            ['name' => 'Tom', 'note' => $data['note'], 'secret' => 'S3CRET'],
            inline: true,
            using: fn ($template) => (string) Antlers::parse($template, $data)
        );

        $this->assertSame(
            'Hi Tom, a [[ secret ]] and {{ config:app.key }} / a [[ secret ]] and {{ config:app.key }}',
            $html
        );
    }

    #[Test]
    public function render_html_matches_doing_it_by_hand()
    {
        $markdown = "# Hi [[ name ]]\n\n**Bold** [[ note ]] \\[\\[ x \\]\\] [[ missing ]]";
        $values = ['name' => 'Tom & Jerry', 'note' => "a\nb <i>"];

        $expected = Mentions::replace(
            AutocompleteEditor::parseMarkdown($markdown),
            ['name' => 'Tom &amp; Jerry', 'note' => "a<br />\nb &lt;i&gt;"]
        );

        $this->assertSame($expected, AutocompleteEditor::renderHtml($markdown, $values));
    }

    private function fieldtype($config = [])
    {
        return (new AutocompleteEditor)->setField(new Field('test', array_merge(['type' => 'autocomplete_editor'], $config)));
    }
}
