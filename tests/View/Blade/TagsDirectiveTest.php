<?php

namespace Tests\View\Blade;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Tags\Tags;
use Statamic\View\Blade\TagsDirective;
use Tests\TestCase;

class TagsDirectiveTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        TheTag::register();
    }

    #[Test]
    #[DataProvider('singleTagProvider')]
    public function it_gets_single_tag($tag, $expected)
    {
        $variables = TagsDirective::handle($tag);

        $this->assertEquals($expected, $variables);
    }

    public static function singleTagProvider()
    {
        return [
            '1 part' => ['the_tag', ['theTag' => 'the index, foo: ']],
            '2 parts' => ['the_tag:another_one', ['theTagAnotherOne' => 'another, baz: ']],
        ];
    }

    #[Test]
    #[DataProvider('singleTagWithAliasProvider')]
    public function it_aliases_using_array($alias, $tag, $expected)
    {
        $variables = TagsDirective::handle([$alias => $tag]);

        $this->assertEquals($expected, $variables);
    }

    public static function singleTagWithAliasProvider()
    {
        return [
            '1 part' => ['myTag', 'the_tag', ['myTag' => 'the index, foo: ']],
            '2 parts' => ['secondTag', 'the_tag:another_one', ['secondTag' => 'another, baz: ']],
        ];
    }

    #[Test]
    #[DataProvider('withParametersProvider')]
    public function it_aliases_with_parameters($alias, $tag, $params, $expected)
    {
        $variables = TagsDirective::handle([
            $alias => [$tag => $params],
        ]);

        $this->assertEquals($expected, $variables);
    }

    public static function withParametersProvider()
    {
        return [
            '1 part' => ['myTag', 'the_tag', ['foo' => 'bar'],  ['myTag' => 'the index, foo: bar']],
            '2 parts' => ['secondTag', 'the_tag:another_one', ['baz' => 'qux'], ['secondTag' => 'another, baz: qux']],
        ];
    }

    #[Test]
    public function it_supports_multiple_tags()
    {
        $variables = TagsDirective::handle([
            'myTag' => ['the_tag' => ['foo' => 'bar']],
            'anotherTag' => ['the_tag:another_one' => ['baz' => 'qux']],
        ]);

        $this->assertEquals([
            'myTag' => 'the index, foo: bar',
            'anotherTag' => 'another, baz: qux',
        ], $variables);
    }
}

class TheTag extends Tags
{
    public function index()
    {
        return 'the index, foo: '.$this->params->get('foo');
    }

    public function anotherOne()
    {
        return 'another, baz: '.$this->params->get('baz');
    }
}
