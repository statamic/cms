<?php

namespace Tests\Forms\Logic;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Logic\RuleEvaluator;
use Tests\TestCase;

class RuleEvaluatorTest extends TestCase
{
    private function condition(string $operator, mixed $value, string $field = 'field', ?string $join = null): array
    {
        return array_filter([
            'join' => $join,
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
        ], fn ($value) => $value !== null);
    }

    #[Test]
    public function an_empty_set_of_conditions_does_not_pass()
    {
        $this->assertFalse((new RuleEvaluator)->passes([], ['field' => 'whatever']));
    }

    #[Test]
    #[DataProvider('operatorProvider')]
    public function it_evaluates_operators(string $operator, mixed $value, mixed $fieldValue, bool $expected)
    {
        $passes = (new RuleEvaluator)->passes(
            [$this->condition($operator, $value)],
            ['field' => $fieldValue],
        );

        $this->assertEquals($expected, $passes);
    }

    public static function operatorProvider(): array
    {
        return [
            'equals pass' => ['equals', 'blue', 'blue', true],
            'equals fail' => ['equals', 'blue', 'red', false],
            'equals trims whitespace' => ['equals', 'blue', '  blue  ', true],
            'not pass' => ['not', 'blue', 'red', true],
            'not fail' => ['not', 'blue', 'blue', false],

            'contains string pass' => ['contains', 'ell', 'hello', true],
            'contains string fail' => ['contains', 'xyz', 'hello', false],
            'contains array pass' => ['contains', 'b', ['a', 'b', 'c'], true],
            'contains array fail' => ['contains', 'z', ['a', 'b', 'c'], false],

            'contains_any string pass' => ['contains_any', 'red, blue', 'i like blue', true],
            'contains_any string fail' => ['contains_any', 'red, green', 'i like blue', false],
            'contains_any array pass' => ['contains_any', 'blue, green', ['red', 'green'], true],
            'contains_any array fail' => ['contains_any', 'blue, yellow', ['red', 'green'], false],

            'strict equals pass' => ['===', '5', '5', true],
            'strict not equals pass' => ['!==', '5', '6', true],

            'greater than pass' => ['>', '5', '10', true],
            'greater than fail' => ['>', '5', '3', false],
            'greater than or equal pass' => ['>=', '5', '5', true],
            'less than pass' => ['<', '5', '3', true],
            'less than or equal pass' => ['<=', '5', '5', true],
            'numeric comparison ignores non-numeric' => ['>', '5', 'banana', false],

            'null literal pass' => ['equals', 'null', null, true],
            'true literal pass' => ['equals', 'true', true, true],
            'false literal pass' => ['equals', 'false', false, true],

            'empty pass when null' => ['equals', 'empty', null, true],
            'empty pass when empty string' => ['equals', 'empty', '', true],
            'empty fail when present' => ['equals', 'empty', 'hello', false],

            'empty string field is treated as null' => ['equals', 'blue', '', false],
            'missing field does not pass equals' => ['equals', 'blue', null, false],
        ];
    }

    #[Test]
    public function and_joined_conditions_all_have_to_pass()
    {
        $evaluator = new RuleEvaluator;

        $conditions = [
            $this->condition('equals', 'a', field: 'one'),
            $this->condition('equals', 'b', field: 'two', join: 'and'),
        ];

        $this->assertTrue($evaluator->passes($conditions, ['one' => 'a', 'two' => 'b']));
        $this->assertFalse($evaluator->passes($conditions, ['one' => 'a', 'two' => 'x']));
    }

    #[Test]
    public function or_joined_conditions_only_need_one_to_pass()
    {
        $evaluator = new RuleEvaluator;

        $conditions = [
            $this->condition('equals', 'a', field: 'one'),
            $this->condition('equals', 'b', field: 'two', join: 'or'),
        ];

        $this->assertTrue($evaluator->passes($conditions, ['one' => 'a', 'two' => 'x']));
        $this->assertTrue($evaluator->passes($conditions, ['one' => 'x', 'two' => 'b']));
        $this->assertFalse($evaluator->passes($conditions, ['one' => 'x', 'two' => 'x']));
    }

    #[Test]
    public function and_binds_tighter_than_or()
    {
        // a OR b AND c  =>  a OR (b AND c)
        $conditions = [
            $this->condition('equals', 'a', field: 'one'),
            $this->condition('equals', 'b', field: 'two', join: 'or'),
            $this->condition('equals', 'c', field: 'three', join: 'and'),
        ];

        $evaluator = new RuleEvaluator;

        // a true, the (b AND c) group false. Grouping => passes. A naive
        // left-to-right fold ((a OR b) AND c) would fail.
        $this->assertTrue($evaluator->passes($conditions, ['one' => 'a', 'two' => 'x', 'three' => 'x']));

        // a false, b true but c false => (b AND c) false => whole thing false.
        $this->assertFalse($evaluator->passes($conditions, ['one' => 'x', 'two' => 'b', 'three' => 'x']));

        // a false, b and c true => (b AND c) true => passes.
        $this->assertTrue($evaluator->passes($conditions, ['one' => 'x', 'two' => 'b', 'three' => 'c']));
    }
}
