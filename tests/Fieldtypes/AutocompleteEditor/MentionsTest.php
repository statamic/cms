<?php

namespace Tests\Fieldtypes\AutocompleteEditor;

use PHPUnit\Framework\Attributes\Test;
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
            '\\\\John',
            Mentions::replace('\\\\[[ first_name ]]', ['first_name' => 'John'])
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
}
