<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Bard;
use Statamic\Fieldtypes\Markdown;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class MarkTest extends TestCase
{
    #[Test]
    public function it_marks()
    {
        $value = 'Lorem, ipsum dolor sit amet';
        $words = 'lorem sit';

        $expected = '<mark>Lorem</mark>, ipsum dolor <mark>sit</mark> amet';

        $this->assertEquals($expected, $this->modify($value, $words));
    }

    #[Test]
    public function it_marks_with_class()
    {
        $value = 'Lorem, ipsum dolor sit amet';
        $words = 'ipsum';
        $param = 'class:highlight';

        $expected = 'Lorem, <mark class="highlight">ipsum</mark> dolor sit amet';

        $this->assertEquals($expected, $this->modify($value, $words, $param));
    }

    #[Test]
    public function it_marks_inside_words_by_default()
    {
        $this->assertEquals('Ne<mark>two</mark>rk', $this->modify('Network', 'two'));
        $this->assertEquals('Ne<mark>two</mark>rk', $this->modify('Network', 'two', 'substring:true'));
    }

    #[Test]
    public function it_marks_whole_words_when_substring_is_false()
    {
        $value = 'Passive House Network interviews Matthew at the association';
        $words = 'Two years in at Passive House';

        $expected = '<mark>Passive</mark> <mark>House</mark> Network interviews Matthew <mark>at</mark> the association';

        $this->assertEquals($expected, $this->modify($value, $words, 'substring:false'));
        $this->assertEquals('<mark>House</mark>,', $this->modify('House,', 'house', 'substring:false'));
    }

    #[Test]
    public function it_uses_the_last_substring_flag_regardless_of_case()
    {
        $this->assertEquals('Ne<mark>two</mark>rk', $this->modify('Network', 'two', 'SUBSTRING:FALSE', 'substring:true'));
        $this->assertEquals('Network', $this->modify('Network', 'two', 'substring:true', 'SUBSTRING:FALSE'));
    }

    #[Test]
    public function it_marks_whole_words_from_the_query_string()
    {
        $value = 'Passive House Network interviews Matthew at the association';

        $expected = '<mark>Passive</mark> <mark>House</mark> Network interviews Matthew <mark>at</mark> the association';

        $this->assertEquals($expected, $this->modifyFromQuery($value, 'Two years in at Passive House', 'substring:false'));
    }

    #[Test]
    public function it_marks_whole_words_with_a_class()
    {
        $expected = 'Passive <mark class="highlight">House</mark> Network';

        $this->assertEquals($expected, $this->modify('Passive House Network', 'house', 'substring:false', 'class:highlight'));
    }

    #[Test]
    public function it_returns_the_value_when_substring_false_has_no_query()
    {
        $this->assertEquals('Network', $this->modify('Network', 'substring:false'));
    }

    #[Test]
    public function it_marks_with_tags()
    {
        $value = 'Lorem, ipsum <x-amet class="ipsum">dolor</x-amet> sit amet';
        $words = 'ipsum amet';

        $expected = 'Lorem, <mark>ipsum</mark> <x-amet class="ipsum">dolor</x-amet> sit <mark>amet</mark>';

        $this->assertEquals($expected, $this->modify($value, $words));
    }

    #[Test]
    public function it_marks_with_specialchars()
    {
        $value = 'Lorem, ipsum &lt; 4 dolor &gt; 2 sit amet';
        $words = 'dolor';

        $expected = 'Lorem, ipsum &lt; 4 <mark>dolor</mark> &gt; 2 sit amet';

        $this->assertEquals($expected, $this->modify($value, $words));
    }

    #[Test]
    public function it_marks_with_entities()
    {
        $value = 'Lorem, ipsum el&uuml;t sit amet';
        $words = 'elüt';

        $expected = 'Lorem, ipsum <mark>el&uuml;t</mark> sit amet';

        $this->assertEquals($expected, $this->modify($value, $words));
    }

    #[Test]
    public function it_marks_bard_value()
    {
        $data = new Value([
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Lorem, ipsum elüt sit '],
                    ['type' => 'text', 'text' => 'amet', 'marks' => [['type' => 'bold']]],
                ],
            ],
        ], 'content', new Bard());
        $words = 'elüt amet';

        $expected = '<p>Lorem, ipsum <mark>el&uuml;t</mark> sit <strong><mark>amet</mark></strong></p>';

        $this->assertEquals($expected, $this->modify($data->value(), $words));
    }

    #[Test]
    public function it_marks_markdown_value()
    {
        $data = new Value('Lorem, ipsum elüt sit **amet**', 'content', new Markdown());
        $words = 'elüt amet';

        $expected = '<p>Lorem, ipsum <mark>el&uuml;t</mark> sit <strong><mark>amet</mark></strong></p>
';

        $this->assertEquals($expected, $this->modify($data->value(), $words));
    }

    protected function modify($arr, ...$args)
    {
        return Modify::value($arr)->mark($args)->fetch();
    }

    protected function modifyFromQuery($value, string $query, ...$args)
    {
        return Modify::value($value)->context(['get' => ['q' => $query]])->mark($args)->fetch();
    }
}
