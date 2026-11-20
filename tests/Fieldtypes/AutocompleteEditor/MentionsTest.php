<?php

namespace Tests\Fieldtypes\AutocompleteEditor;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Statamic\Fieldtypes\AutocompleteEditor;
use Statamic\Fieldtypes\AutocompleteEditor\Mentions;
use Tests\TestCase;

class MentionsTest extends TestCase
{
    #[Test]
    public function it_replaces_a_single_mention()
    {
        $this->assertSame(
            'Hi John, thanks!',
            Mentions::replace('Hi [[ first_name ]], thanks!', ['first_name' => 'John'])
        );
    }

    #[Test]
    public function it_replaces_multiple_mentions()
    {
        $this->assertSame(
            'John Doe',
            Mentions::replace('[[ first_name ]] [[ last_name ]]', [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ])
        );
    }

    #[Test]
    public function it_replaces_the_same_mention_more_than_once()
    {
        $this->assertSame(
            'John, oh John.',
            Mentions::replace('[[ first_name ]], oh [[ first_name ]].', ['first_name' => 'John'])
        );
    }

    #[Test]
    public function whitespace_inside_the_brackets_is_optional()
    {
        $this->assertSame(
            'John John John',
            Mentions::replace('[[first_name]] [[ first_name]] [[   first_name   ]]', ['first_name' => 'John'])
        );
    }

    #[Test]
    public function handles_may_contain_word_characters_dots_and_dashes()
    {
        $this->assertSame(
            'a b c',
            Mentions::replace('[[ one.two ]] [[ three-four ]] [[ five_6 ]]', [
                'one.two' => 'a',
                'three-four' => 'b',
                'five_6' => 'c',
            ])
        );
    }

    #[Test]
    public function an_unknown_key_resolves_to_an_empty_string()
    {
        $this->assertSame(
            'Hi , thanks!',
            Mentions::replace('Hi [[ first_name ]], thanks!', [])
        );
    }

    #[Test]
    public function an_escaped_literal_is_left_untouched()
    {
        $this->assertSame(
            'Type \[\[ first_name \]\] literally',
            Mentions::replace('Type \[\[ first_name \]\] literally', ['first_name' => 'John'])
        );
    }

    #[Test]
    public function an_escaped_literal_is_left_untouched_alongside_a_real_mention()
    {
        $this->assertSame(
            'Hi John, see \[\[ first_name \]\] literally',
            Mentions::replace('Hi [[ first_name ]], see \[\[ first_name \]\] literally', ['first_name' => 'John'])
        );

        $this->assertSame(
            'See \[\[ first_name \]\] then John',
            Mentions::replace('See \[\[ first_name \]\] then [[ first_name ]]', ['first_name' => 'John'])
        );
    }

    #[Test]
    public function a_token_preceded_by_an_escaped_backslash_is_still_replaced()
    {
        $this->assertSame(
            '\\John',
            Mentions::replace('\\\\[[ first_name ]]', ['first_name' => 'John'])
        );
    }

    #[Test]
    public function an_escaped_plain_text_literal_is_unescaped_and_left_unresolved()
    {
        $this->assertSame(
            '[[ first_name ]] then John',
            Mentions::replace('\\[[ first_name ]] then [[ first_name ]]', ['first_name' => 'John'])
        );
    }

    #[Test]
    public function the_parity_of_a_backslash_run_before_brackets_decides_whether_they_are_escaped()
    {
        $values = ['a' => 'John'];

        $this->assertSame('\\[[ a ]]', Mentions::replace('\\\\\\[[ a ]]', $values));
        $this->assertSame('\\\\John', Mentions::replace('\\\\\\\\[[ a ]]', $values));
        $this->assertSame('\\\\[[ a ]]', Mentions::replace('\\\\\\\\\\[[ a ]]', $values));
    }

    #[Test]
    public function a_bracket_directly_before_a_token_is_kept()
    {
        $values = ['a' => 'John'];

        $this->assertSame('[John', Mentions::replace('[[[ a ]]', $values));
        $this->assertSame('\\[John', Mentions::replace('\\\\[[[ a ]]', $values));
        $this->assertSame('[[[John', Mentions::replace('\\[[[[[ a ]]', $values));
    }

    #[Test]
    public function an_escaped_literal_directly_before_a_token_is_unescaped_while_the_token_resolves()
    {
        $this->assertSame('[[John', Mentions::replace('\\[[[[ a ]]', ['a' => 'John']));
    }

    #[Test]
    public function backslashes_not_touching_brackets_are_left_alone()
    {
        $this->assertSame(
            'C:\\path\\to \\[ x ] \\ John \\',
            Mentions::replace('C:\\path\\to \\[ x ] \\ [[ a ]] \\', ['a' => 'John'])
        );
    }

    #[Test]
    public function a_half_escaped_token_is_left_untouched()
    {
        $this->assertSame(
            '[[ first_name \]\]',
            Mentions::replace('[[ first_name \]\]', ['first_name' => 'John'])
        );
    }

    #[Test]
    public function substituted_values_are_not_reprocessed()
    {
        $this->assertSame(
            '[[ last_name ]]',
            Mentions::replace('[[ first_name ]]', [
                'first_name' => '[[ last_name ]]',
                'last_name' => 'Doe',
            ])
        );
    }

    #[Test]
    public function substituted_values_are_not_escaped()
    {
        $this->assertSame(
            'Hi <b>John</b> **Doe**',
            Mentions::replace('Hi [[ first_name ]] [[ last_name ]]', [
                'first_name' => '<b>John</b>',
                'last_name' => '**Doe**',
            ])
        );
    }

    #[Test]
    public function it_leaves_content_without_mentions_alone()
    {
        $this->assertSame('Nothing to see here', Mentions::replace('Nothing to see here', ['first_name' => 'John']));
        $this->assertSame('', Mentions::replace('', ['first_name' => 'John']));
    }

    #[Test]
    public function the_callback_receives_the_template_with_placeholders_instead_of_values()
    {
        $received = null;

        $result = Mentions::replace('Hi [[ first_name ]] [[ last_name ]]', [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ], using: function ($template) use (&$received) {
            $received = $template;

            return $template;
        });

        $this->assertSame('Hi John Doe', $result);
        $this->assertStringNotContainsString('[[', $received);
        $this->assertStringNotContainsString('John', $received);
        $this->assertStringNotContainsString('Doe', $received);
        $this->assertMatchesRegularExpression('/^Hi mention\w+n0e mention\w+n1e$/', $received);
    }

    #[Test]
    public function repeated_mentions_share_a_placeholder()
    {
        $received = null;

        Mentions::replace('[[ a ]] [[ a ]]', ['a' => 'John'], using: function ($template) use (&$received) {
            $received = $template;

            return $template;
        });

        [$first, $second] = explode(' ', $received);
        $this->assertSame($first, $second);
    }

    #[Test]
    public function a_mention_produced_by_the_callback_stays_literal()
    {
        $this->assertSame(
            'Hi [[ last_name ]] John',
            Mentions::replace('Hi {{ x }} [[ first_name ]]', ['first_name' => 'John', 'last_name' => 'Doe'], using: fn ($template) => str_replace('{{ x }}', '[[ last_name ]]', $template))
        );
    }

    #[Test]
    public function a_forged_placeholder_with_a_different_nonce_stays_literal()
    {
        $forged = 'mention'.str_repeat('0', 16).'n0e';

        $this->assertSame(
            "$forged John",
            Mentions::replace('{{ x }} [[ a ]]', ['a' => 'John'], using: fn ($template) => str_replace('{{ x }}', $forged, $template))
        );
    }

    #[Test]
    public function values_come_out_verbatim_when_using_a_callback()
    {
        $values = [
            'a' => '{{ config:app.key }}',
            'b' => '[[ a ]]',
            'c' => 'C:\\path \\[[ x ]] \\\\',
        ];

        $this->assertSame(
            '{{ config:app.key }} | [[ a ]] | C:\\path \\[[ x ]] \\\\',
            Mentions::replace('[[ a ]] | [[ b ]] | [[ c ]]', $values, using: fn ($template) => $template)
        );
    }

    #[Test]
    public function escaped_literals_still_come_out_as_visible_brackets_when_using_a_callback()
    {
        $this->assertSame(
            '[[ a ]] then John',
            Mentions::replace('\\[[ a ]] then [[ a ]]', ['a' => 'John'], using: fn ($template) => $template)
        );

        $this->assertSame(
            'See &#91;&#91; a &#93;&#93; then John',
            Mentions::replace('See &#91;&#91; a &#93;&#93; then [[ a ]]', ['a' => 'John'], using: fn ($template) => $template)
        );
    }

    #[Test]
    public function unknown_handles_resolve_to_an_empty_string_when_using_a_callback()
    {
        $this->assertSame(
            'Hi , thanks!',
            Mentions::replace('Hi [[ nope ]], thanks!', [], using: fn ($template) => $template)
        );

        $this->assertSame(
            'Hi John,  thanks!',
            Mentions::replace('Hi [[ a ]], [[ nope ]] thanks!', ['a' => 'John'], using: fn ($template) => $template)
        );
    }

    #[Test]
    public function the_callback_may_drop_or_repeat_a_placeholder()
    {
        $values = ['a' => 'John', 'b' => 'Doe'];

        $this->assertSame(
            'Doe',
            Mentions::replace('[[ a ]] [[ b ]]', $values, using: fn ($template) => explode(' ', $template)[1])
        );

        $this->assertSame(
            'John John Doe',
            Mentions::replace('[[ a ]] [[ b ]]', $values, using: function ($template) {
                [$a, $b] = explode(' ', $template);

                return "$a $a $b";
            })
        );
    }

    #[Test]
    public function the_callback_return_value_is_cast_to_a_string()
    {
        $this->assertSame('', Mentions::replace('[[ a ]]', ['a' => 'John'], using: fn () => null));
    }

    #[Test]
    public function placeholders_are_distinct_with_ten_or_more_handles()
    {
        $values = [];
        for ($i = 0; $i < 12; $i++) {
            $values["h$i"] = "v$i";
        }

        $content = implode(' ', array_map(fn ($handle) => "[[ $handle ]]", array_keys($values)));

        $this->assertSame(
            implode(' ', $values),
            Mentions::replace($content, $values, using: fn ($template) => $template)
        );
    }

    #[Test]
    public function submitted_text_survives_antlers_in_a_plain_text_value()
    {
        $data = ['name' => '[[ secret ]] {{ config:app.key }} \\[[ secret ]] \\\\ C:\\x'];

        $this->assertSame(
            'Hi '.$data['name'].' from Jane',
            Mentions::replace(
                'Hi {{ name }} from [[ who ]]',
                ['who' => 'Jane', 'secret' => 'TOPSECRET'],
                using: fn ($template) => (string) Antlers::parse($template, $data)
            )
        );
    }

    #[Test]
    public function submitted_text_survives_antlers_in_a_markdown_value()
    {
        $data = ['name' => '[[ secret ]] {{ config:app.key }} \\[[ secret ]] \\\\ C:\\x'];

        $html = AutocompleteEditor::parseMarkdown('Hi {{ name }} from [[ who ]] and \\[\\[ who \\]\\]', inline: true);

        $this->assertSame(
            'Hi '.$data['name'].' from Jane and &#91;&#91; who ]]',
            Mentions::replace(
                $html,
                ['who' => 'Jane', 'secret' => 'TOPSECRET'],
                using: fn ($template) => (string) Antlers::parse($template, $data)
            )
        );
    }
}
