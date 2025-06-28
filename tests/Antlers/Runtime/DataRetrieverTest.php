<?php

namespace Tests\Antlers\Runtime;

use Statamic\Facades\Antlers;
use Statamic\Facades\Cascade;
use Statamic\View\Antlers\Language\Runtime\PathDataManager;
use Tests\Antlers\ParserTestCase;

class DataRetrieverTest extends ParserTestCase
{
    private function getPathValue($path, $data)
    {
        $dataRetriever = new PathDataManager();

        return $dataRetriever->getData($this->parsePath($path), $data);
    }

    public function test_simple_data_is_retrieved()
    {
        $data = [
            'view' => [
                'key' => 'value',
            ],
        ];

        $value = $this->getPathValue('view', $data);
        $this->assertIsArray($value);
        $this->assertCount(1, $value);
        $this->assertArrayHasKey('key', $value);

        $value = $this->getPathValue('view:key', $data);
        $this->assertSame('value', $value);
    }

    public function test_dynamic_keys_are_retrieved()
    {
        $data = [
            'page' => [
                'value' => 'Hello, world!',
            ],
            'view' => [
                'nested' => [
                    'nested1' => [
                        'nested2' => 'value',
                    ],
                ],
            ],
        ];

        // This should retrieve "value" from the inner path first,
        // and use that to resolve relative to the "page" value.
        $value = $this->getPathValue('page[view:nested:nested1:nested2]', $data);
        $this->assertSame('Hello, world!', $value);
    }

    public function test_dynamic_keys_are_correctly_set()
    {
        $data = [
            'page' => [
                'value' => 'Hello, world!',
            ],
            'view' => [
                'nested' => [
                    'nested1' => [
                        'nested2' => 'value',
                    ],
                ],
            ],
        ];

        // This should retrieve "value" from the inner path first,
        // and use that to resolve relative to the "page" value.
        $value = $this->getPathValue('page[view:nested:nested1:nested2]', $data);
        $this->assertSame('Hello, world!', $value);
        $data = $this->evaluate('page[view:nested:nested1:nested2] = 12345;', $data);

        $value = $this->getPathValue('page[view:nested:nested1:nested2]', $data);
        $this->assertSame(12345, $value);
    }

    public function test_object_properties_are_retrieved()
    {
        $data = [
            'view' => [
                'object' => new class
                {
                    public string $publicProperty = 'Hello Public World!';

                    protected string $protectedProperty = 'Hello Protected World!';

                    private string $privateProperty = 'Hello Private World!';
                },
            ],
        ];

        $value = $this->getPathValue('view.object.public_property', $data);
        $this->assertSame('Hello Public World!', $value);

        $value = $this->getPathValue('view.object.protected_property', $data);
        $this->assertNull($value);

        $value = $this->getPathValue('view.object.private_property', $data);
        $this->assertNull($value);
    }
}
