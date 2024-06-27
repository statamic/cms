<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class SentenceListTest extends TestCase
{
    #[Test]
    public function it_makes_sentence_lists_with_arrays()
    {
        $this->assertEquals('this', $this->modify(['this']));
        $this->assertEquals('this and that', $this->modify(['this', 'that']));
        $this->assertEquals('this, that, and the other', $this->modify(['this', 'that', 'the other']));

        $this->assertEquals('this', $this->modify(['this'], '&'));
        $this->assertEquals('this & that', $this->modify(['this', 'that'], '&'));
        $this->assertEquals('this, that, & the other', $this->modify(['this', 'that', 'the other'], '&'));

        $this->assertEquals('this', $this->modify(['this'], 'and', false));
        $this->assertEquals('this and that', $this->modify(['this', 'that'], 'and', false));
        $this->assertEquals('this, that and the other', $this->modify(['this', 'that', 'the other'], 'and', false));
    }

    #[Test]
    public function it_makes_sentence_lists_with_collections()
    {
        $this->assertEquals('this', $this->modify(collect(['this'])));
        $this->assertEquals('this and that', $this->modify(collect(['this', 'that'])));
        $this->assertEquals('this, that, and the other', $this->modify(collect(['this', 'that', 'the other'])));

        $this->assertEquals('this', $this->modify(collect(['this']), '&'));
        $this->assertEquals('this & that', $this->modify(collect(['this', 'that']), '&'));
        $this->assertEquals('this, that, & the other', $this->modify(collect(['this', 'that', 'the other']), '&'));

        $this->assertEquals('this', $this->modify(collect(['this']), 'and', false));
        $this->assertEquals('this and that', $this->modify(collect(['this', 'that']), 'and', false));
        $this->assertEquals('this, that and the other', $this->modify(collect(['this', 'that', 'the other']), 'and', false));
    }

    #[Test]
    public function it_returns_original_value_when_not_iterable()
    {
        $this->assertEquals('foo', $this->modify('foo'));
    }

    public function modify($arr, ...$args)
    {
        return Modify::value($arr)->sentenceList($args)->fetch();
    }
}
