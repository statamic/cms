<?php

namespace Tests\Fields;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\ClassRuleParser;
use Tests\TestCase;

class ClassRuleParserTest extends TestCase
{
    #[Test]
    #[DataProvider('classRuleProvider')]
    public function it_parses_class_rules($input, $expected)
    {
        $output = (new ClassRuleParser)->parse($input);

        $this->assertSame($expected, $output);
    }

    public static function classRuleProvider()
    {
        return [
            'just class' => [
                'new App\MyRule',
                ['App\MyRule', []],
            ],
            'single quoted string' => [
                "new App\MyRule('foo')",
                ['App\MyRule', ['foo']],
            ],
            'double quoted string' => [
                'new App\MyRule("foo")',
                ['App\MyRule', ['foo']],
            ],
            'multiple arguments' => [
                "new App\MyRule('foo', 123, 'bar')",
                ['App\MyRule', ['foo', 123, 'bar']],
            ],
            'quote in a single quoted string' => [
                "new App\MyRule('it\'s a me mario')",
                ['App\MyRule', ["it's a me mario"]],
            ],
            'double quote in a double quoted string' => [
                'new App\MyRule("stop trying to make \"fetch\" happen")',
                ['App\MyRule', ['stop trying to make "fetch" happen']],
            ],
            'only named arguments' => [
                'new App\MyRule(a: "foo", b: 123)',
                ['App\MyRule', ['a' => 'foo', 'b' => 123]],
            ],
            'only named arguments with no spaces' => [
                'new App\MyRule(a:"foo", b:123)',
                ['App\MyRule', ['a' => 'foo', 'b' => 123]],
            ],
            'some named arguments' => [
                'new App\MyRule("foo", c: 123)',
                ['App\MyRule', ['foo', 'c' => 123]],
            ],
            'non-named argument with colon' => [
                'new App\MyRule("foo:bar")',
                ['App\MyRule', ['foo:bar']],
            ],
            'named argument with colon' => [
                'new App\MyRule(a: "foo:bar")',
                ['App\MyRule', ['a' => 'foo:bar']],
            ],
            'null argument' => [
                'new App\MyRule(null)',
                ['App\MyRule', [null]],
            ],
            'true boolean argument' => [
                'new App\MyRule(true)',
                ['App\MyRule', [true]],
            ],
            'false boolean argument' => [
                'new App\MyRule(false)',
                ['App\MyRule', [false]],
            ],
        ];
    }
}
