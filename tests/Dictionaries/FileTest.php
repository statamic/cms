<?php

namespace Tests\Dictionaries;

use League\Flysystem\PathTraversalDetected;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Dictionaries\File;
use Statamic\Dictionaries\Item;
use Statamic\Facades\File as Filesystem;
use Statamic\Facades\YAML;
use Tests\TestCase;

class FileTest extends TestCase
{
    #[Test]
    #[DataProvider('optionProvider')]
    public function it_gets_options(
        $extension,
        $fileDumpCallback
    ) {
        $arr = [
            ['value' => 'apple', 'label' => 'Apple', 'emoji' => '🍎'],
            ['value' => 'banana', 'label' => 'Banana', 'emoji' => '🍌'],
            ['value' => 'cherry', 'label' => 'Cherry', 'emoji' => '🍒'],
        ];

        Filesystem::put(
            resource_path('dictionaries').'/items.'.$extension,
            $fileDumpCallback($arr, 'value', 'label')
        );

        $options = (new File)
            ->setConfig(['filename' => 'items.'.$extension])
            ->options();

        $this->assertCount(3, $options);
        $this->assertEquals([
            'apple' => 'Apple',
            'banana' => 'Banana',
            'cherry' => 'Cherry',
        ], $options);
    }

    #[Test]
    #[DataProvider('optionProvider')]
    public function it_gets_options_with_custom_value_and_label_keys(
        $extension,
        $fileDumpCallback
    ) {
        $arr = [
            ['id' => 'apple', 'name' => 'Apple', 'emoji' => '🍎'],
            ['id' => 'banana', 'name' => 'Banana', 'emoji' => '🍌'],
            ['id' => 'cherry', 'name' => 'Cherry', 'emoji' => '🍒'],
        ];

        Filesystem::put(
            resource_path('dictionaries').'/items.'.$extension,
            $fileDumpCallback($arr, 'id', 'name')
        );

        $options = (new File)
            ->setConfig([
                'filename' => 'items.'.$extension,
                'value' => 'id',
                'label' => 'name',
            ])
            ->options();

        $this->assertCount(3, $options);
        $this->assertEquals([
            'apple' => 'Apple',
            'banana' => 'Banana',
            'cherry' => 'Cherry',
        ], $options);
    }

    #[Test]
    #[DataProvider('optionProvider')]
    public function it_gets_options_with_antlers_label(
        $extension,
        $fileDumpCallback
    ) {
        $arr = [
            ['value' => 'apple', 'name' => 'Apple', 'emoji' => '🍎'],
            ['value' => 'banana', 'name' => 'Banana', 'emoji' => '🍌'],
            ['value' => 'cherry', 'name' => 'Cherry', 'emoji' => '🍒'],
        ];

        Filesystem::put(
            resource_path('dictionaries').'/items.'.$extension,
            $fileDumpCallback($arr, 'value', 'name')
        );

        $options = (new File)
            ->setConfig([
                'filename' => 'items.'.$extension,
                'label' => '{{ emoji }} {{ name }}!',
            ])
            ->options();

        $this->assertCount(3, $options);
        $this->assertEquals([
            'apple' => '🍎 Apple!',
            'banana' => '🍌 Banana!',
            'cherry' => '🍒 Cherry!',
        ], $options);
    }

    public static function optionProvider()
    {
        return [
            'yaml' => ['yaml', fn ($arr, $value, $label) => YAML::dump($arr)],
            'json' => ['json', fn ($arr, $value, $label) => json_encode($arr)],
            'csv' => ['csv', fn ($arr, $value, $label) => "{$value},{$label},emoji".PHP_EOL.implode(PHP_EOL, array_map(fn ($item) => implode(',', $item), $arr))],
        ];
    }

    #[Test]
    #[DataProvider('searchProvider')]
    public function it_searches_options($query, $expected)
    {
        $arr = [
            ['value' => 'apple', 'label' => 'Apple', 'emoji' => '🍎'],
            ['value' => 'banana', 'label' => 'Banana', 'emoji' => '🍌'],
            ['value' => 'cherry', 'label' => 'Cherry', 'emoji' => '🍒'],
        ];

        Filesystem::put(
            resource_path('dictionaries').'/items.yaml',
            YAML::dump($arr)
        );

        $dictionary = (new File)->setConfig(['filename' => 'items.yaml']);

        $this->assertEquals($expected, $dictionary->options($query));
    }

    public static function searchProvider()
    {
        return [
            'e' => [
                'e',
                [
                    'apple' => 'Apple',
                    'cherry' => 'Cherry',
                ],
            ],
            'n' => [
                'n',
                [
                    'banana' => 'Banana',
                ],
            ],
        ];
    }

    #[Test]
    public function it_gets_array_from_value()
    {
        $arr = [
            ['value' => 'apple', 'label' => 'Apple', 'emoji' => '🍎'],
            ['value' => 'banana', 'label' => 'Banana', 'emoji' => '🍌'],
            ['value' => 'cherry', 'label' => 'Cherry', 'emoji' => '🍒'],
        ];

        Filesystem::put(
            resource_path('dictionaries').'/items.yaml',
            YAML::dump($arr)
        );

        $item = (new File)
            ->setConfig(['filename' => 'items.yaml'])
            ->get('banana');

        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals('Banana', $item->label());
        $this->assertEquals('banana', $item->value());
        $this->assertEquals([
            'value' => 'banana',
            'emoji' => '🍌',
        ], $item->data());
    }

    #[Test]
    public function path_traversal_not_allowed()
    {
        $this->expectException(PathTraversalDetected::class);
        $this->expectExceptionMessage('Path traversal detected: ../secret.json');

        (new File)
            ->setConfig(['filename' => '../secret.json'])
            ->options();
    }
}
