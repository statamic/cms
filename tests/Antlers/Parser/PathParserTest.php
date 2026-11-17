<?php

namespace Tests\Antlers\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use Statamic\View\Antlers\Language\Exceptions\SyntaxErrorException;
use Statamic\View\Antlers\Language\Nodes\Paths\PathNode;
use Statamic\View\Antlers\Language\Nodes\Paths\VariableReference;
use Tests\Antlers\ParserTestCase;

class PathParserTest extends ParserTestCase
{
    public function test_simple_paths_are_parsed()
    {
        $result = $this->parsePath('var_name');
        $this->assertCount(1, $result->pathParts);
        $this->assertInstanceOf(PathNode::class, $result->pathParts[0]);
        $this->assertSame('var_name', $result->pathParts[0]->name);
    }

    public function test_strict_variable_references_are_parsed()
    {
        $result = $this->parsePath('$var_name');
        $this->assertTrue($result->isStrictVariableReference);
        $this->assertSame('var_name', $result->pathParts[0]->name);
    }

    public function test_explicit_variable_references_are_parsed()
    {
        $result = $this->parsePath('$$var_name');
        $this->assertTrue($result->isStrictVariableReference);
        $this->assertTrue($result->isExplicitVariableReference);
        $this->assertSame('var_name', $result->pathParts[0]->name);
    }

    public function test_it_parses_complex_paths()
    {
        $result = $this->parsePath('view:data:test[nested.key[path:path1]]');
        $this->assertInstanceOf(VariableReference::class, $result);
        $this->assertFalse($result->isStrictVariableReference);
        $this->assertFalse($result->isExplicitVariableReference);
        $this->assertCount(4, $result->pathParts);

        $part1 = $result->pathParts[0];
        $part2 = $result->pathParts[1];
        $part3 = $result->pathParts[2];

        /** @var VariableReference $part4 */
        $part4 = $result->pathParts[3];

        $this->assertInstanceOf(PathNode::class, $part1);
        $this->assertInstanceOf(PathNode::class, $part2);
        $this->assertInstanceOf(PathNode::class, $part3);
        $this->assertInstanceOf(VariableReference::class, $part4);

        $this->assertSame('view', $part1->name);
        $this->assertSame('data', $part2->name);
        $this->assertSame('test', $part3->name);

        $this->assertCount(3, $part4->pathParts);

        $nestedPart1 = $part4->pathParts[0];
        $nestedPart2 = $part4->pathParts[1];
        $nestedPart3 = $part4->pathParts[2];

        $this->assertInstanceOf(PathNode::class, $nestedPart1);
        $this->assertInstanceOf(PathNode::class, $nestedPart2);
        $this->assertInstanceOf(VariableReference::class, $nestedPart3);

        $this->assertSame('nested', $nestedPart1->name);
        $this->assertSame('key', $nestedPart2->name);

        $this->assertCount(2, $nestedPart3->pathParts);

        $subNestedPart1 = $nestedPart3->pathParts[0];
        $subNestedPart2 = $nestedPart3->pathParts[1];

        $this->assertInstanceOf(PathNode::class, $subNestedPart1);
        $this->assertInstanceOf(PathNode::class, $subNestedPart2);

        $this->assertSame('path', $subNestedPart1->name);
        $this->assertSame('path1', $subNestedPart2->name);
    }

    public function test_it_parses_trailing_array_accessors()
    {
        $data = [
            'values' => [
                'one' => 'Value One',
                'two' => 'Value Two',
                'three' => 'Value Three',
            ],
            'value' => 'two',
        ];
        $result = $this->renderString('{{ values[value] }}', $data);

        $this->assertSame('Value Two', $result);

        $data = [
            'values' => [
                'one' => 'Value One',
                'two' => 'Value Two',
                'three' => 'Value Three',
            ],
        ];
        $result = $this->renderString('{{ values[value] }}', $data);

        $this->assertSame('', $result);
    }

    #[DataProvider('unclosedAccessorProvider')]
    public function test_unclosed_accessors_throw_instead_of_looping($path)
    {
        $this->expectException(SyntaxErrorException::class);

        $this->parsePath($path);
    }

    public static function unclosedAccessorProvider()
    {
        return [
            'missing outer bracket' => ['values[keys[0]'],
            'ends on an opening bracket' => ['values[keys['],
            'ends on an opening quote' => ['values["'],
        ];
    }
}
