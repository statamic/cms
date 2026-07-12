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
}
