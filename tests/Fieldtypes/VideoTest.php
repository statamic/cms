<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Text;
use Statamic\Fieldtypes\Video;
use Tests\TestCase;

class VideoTest extends TestCase
{
    #[Test]
    public function it_preloads_empty_field()
    {
        $fieldtype = (new Video)->setField(new Field('test', ['type' => 'video']));

        $meta = $fieldtype->preload();

        $this->assertSame('Cloudflare', $meta['providers'][0]['provider']);
        $this->assertFalse(isset($meta['provider']));
    }

    #[Test]
    #[DataProvider('preloadValuesProvider')]
    public function it_preloads_with_value($provider, $value)
    {
        $fieldtype = tap(new Video, fn (Video $v) => $v
            ->setField(new Field('test', ['type' => 'video']))
            ->field()->setValue($value)
        );

        $this->assertSame($provider, $fieldtype->preload()['provider']);
    }

    public static function preloadValuesProvider()
    {
        return [
            ['Youtube', 'https://www.youtube.com/watch?v=FK3dav4bA4s'],
            ['Cloudflare', 'cloudflare:1234'],
        ];
    }

    #[Test]
    #[DataProvider('processValuesProvider')]
    public function it_processes_values($mode, $values)
    {
        $field = (new Text)->setField(new Field('test', [
            'type' => 'text',
            'input_type' => $mode,
        ]));

        $this->assertSame($values[0], $field->process('test'));
        $this->assertSame($values[1], $field->process('3'));
        $this->assertSame($values[2], $field->process('3test'));
        $this->assertSame($values[3], $field->process('3.14'));
        $this->assertSame($values[4], $field->process(null));
    }

    public static function processValuesProvider()
    {
        return [
            'text' => ['text', ['test', '3', '3test', '3.14', null]],
            'number' => ['number', [0, 3, 3, 3.14, null]],
        ];
    }

    #[Test]
    #[DataProvider('preProcessIndexProvider')]
    public function it_pre_processes_index_values($config, $value, $expected)
    {
        $field = (new Text)->setField(new Field('test', array_merge([
            'type' => 'text',
        ], $config)));

        $this->assertSame($expected, $field->preProcessIndex($value));
    }

    public static function preProcessIndexProvider()
    {
        return [
            'string value' => [[], 'hello', 'hello'],
            'null value' => [[], null, null],
            'zero integer' => [[], 0, '0'],
            'zero string' => [[], '0', '0'],
            'zero with prepend' => [['prepend' => '$'], 0, '$0'],
            'zero with append' => [['append' => '%'], 0, '0%'],
            'zero with prepend and append' => [['prepend' => '$', 'append' => '%'], 0, '$0%'],
            'string with prepend' => [['prepend' => '$'], 'hello', '$hello'],
        ];
    }
}
