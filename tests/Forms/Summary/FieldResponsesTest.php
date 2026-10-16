<?php

namespace Tests\Forms\Summary;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Summary\FieldResponseCounter;
use Statamic\Forms\Summary\FieldResponses;
use Tests\TestCase;

class FieldResponsesTest extends TestCase
{
    #[Test]
    public function it_only_counts_filled_values()
    {
        $responses = $this->responses([false, 0, null, '', [], 'a']);

        $this->assertSame(3, $responses->total());
        $this->assertSame(['false' => 1, 0 => 1, 'a' => 1], $responses->counts()->all());
    }

    #[Test]
    public function it_keys_booleans_as_strings()
    {
        $responses = $this->responses([true, false, true]);

        $this->assertSame(['true' => 2, 'false' => 1], $responses->counts()->all());
    }

    #[Test]
    public function it_flattens_list_values_one_level()
    {
        $responses = $this->responses([['a', 'b'], ['b'], 'c']);

        $this->assertSame(3, $responses->total());
        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 1], $responses->counts()->all());
    }

    #[Test]
    public function it_skips_non_scalar_items()
    {
        $responses = $this->responses([['a', ['nested'], null, (object) []]]);

        $this->assertSame(1, $responses->total());
        $this->assertSame(['a' => 1], $responses->counts()->all());
    }

    #[Test]
    public function it_records_positions_within_list_values()
    {
        $responses = $this->responses([['a', 'b', 'c'], ['b', 'a'], ['first' => 'c', 'second' => 'a'], 'b']);

        $this->assertSame([
            'a' => [1 => 1, 2 => 2],
            'b' => [2 => 1, 1 => 2],
            'c' => [3 => 1, 1 => 1],
        ], $responses->positions()->all());
    }

    #[Test]
    public function it_keeps_counts_in_first_seen_order()
    {
        $responses = $this->responses(['c', 'a', 'b', 'a', 'c']);

        $this->assertSame(['c', 'a', 'b'], $responses->counts()->keys()->all());
    }

    #[Test]
    public function counts_returns_a_copy()
    {
        $responses = $this->responses(['a', 'b']);

        $responses->counts()->put('c', 5)->forget('a');

        $this->assertSame(['a' => 1, 'b' => 1], $responses->counts()->all());
    }

    #[Test]
    public function numeric_is_null_without_numeric_keys()
    {
        $this->assertNull($this->responses(['a', 'b', true])->numeric());
        $this->assertNull($this->responses([])->numeric());
    }

    #[Test]
    public function numeric_summarizes_numeric_keys()
    {
        $numeric = $this->responses([1, '3.5', 'nonsense', 1, '-2', 10])->numeric();

        $this->assertSame(5, $numeric->count());
        $this->assertEquals(13.5, $numeric->sum());
        $this->assertEquals(-2, $numeric->min());
        $this->assertEquals(10, $numeric->max());
        $this->assertEquals(2.7, $numeric->average());
    }

    #[Test]
    public function numeric_is_memoized()
    {
        $responses = $this->responses([1, 2]);

        $this->assertSame($responses->numeric(), $responses->numeric());
    }

    #[Test]
    public function from_values_matches_the_counter()
    {
        $field = new FormField('color', ['type' => 'checkboxes']);

        $counter = new FieldResponseCounter($field);
        $counter->add(['red', 'blue']);
        $counter->add('red');

        $fromValues = FieldResponses::fromValues($field, collect([['red', 'blue'], 'red']));

        $this->assertSame($field, $fromValues->field());
        $this->assertEquals($counter->responses(), $fromValues);
        $this->assertSame(2, $fromValues->total());
        $this->assertSame(['red' => 2, 'blue' => 1], $fromValues->counts()->all());
    }

    #[Test]
    public function key_casts_values_to_strings()
    {
        $this->assertSame('true', FieldResponses::key(true));
        $this->assertSame('false', FieldResponses::key(false));
        $this->assertSame('5', FieldResponses::key(5));
        $this->assertSame('3.5', FieldResponses::key(3.5));
        $this->assertSame('a', FieldResponses::key('a'));
    }

    private function responses(iterable $values): FieldResponses
    {
        return FieldResponses::fromValues(new FormField('field', ['type' => 'number']), $values);
    }
}
