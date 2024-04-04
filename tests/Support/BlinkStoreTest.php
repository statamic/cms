<?php

namespace Tests\Support;

use Statamic\Support\BlinkStore;
use Tests\TestCase;

class BlinkStoreTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->blink = new BlinkStore();
    }

    /** @test */
    public function it_can_store_a_key_value_pair()
    {
        $this->blink->put('key', 'value');

        $this->assertSame('value', $this->blink->get('key'));
    }

    /** @test */
    public function it_can_determine_if_the_blink_cache_holds_a_value_for_a_given_name_with_a_wild_card_when_wildcards_are_enabled()
    {
        $this->assertFalse($this->blink->has('prefix.*.suffix'));

        $this->blink->put('prefix.middle.suffix', 'value');

        $this->blink->withWildcards();

        $this->assertTrue($this->blink->has('prefix.*.suffix'));
        $this->assertTrue($this->blink->has('*.suffix'));
        $this->assertTrue($this->blink->has('prefix.*'));
        $this->assertTrue($this->blink->has('*'));
        $this->assertFalse($this->blink->has('*.no'));
        $this->assertFalse($this->blink->has('no.*'));
        $this->assertTrue($this->blink->has('prefix.middle.suffix'));
    }

    /** @test */
    public function it_cannot_determine_if_the_blink_cache_holds_a_value_for_a_given_name_with_a_wild_card_when_wildcards_are_disabled()
    {
        $this->assertFalse($this->blink->has('prefix.*.suffix'));

        $this->blink->put('prefix.middle.suffix', 'value');

        $this->assertFalse($this->blink->has('prefix.*.suffix'));
        $this->assertFalse($this->blink->has('*.suffix'));
        $this->assertFalse($this->blink->has('prefix.*'));
        $this->assertFalse($this->blink->has('*'));
        $this->assertFalse($this->blink->has('*.no'));
        $this->assertFalse($this->blink->has('no.*'));

        $this->assertTrue($this->blink->has('prefix.middle.suffix'));
    }
}
