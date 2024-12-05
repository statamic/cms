<?php

namespace Tests\Fields;

use Facades\Statamic\Fields\FieldtypeRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\ConfigFields;
use Statamic\Fields\Fieldtype;
use Tests\TestCase;

class ConfigFieldsTest extends TestCase
{
    #[Test]
    public function it_preprocesses_each_fields_values_by_its_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')->with('fieldtype')->andReturn(new class extends Fieldtype
        {
            public function preProcess($data)
            {
                return $data.' preprocessed';
            }

            public function preProcessConfig($data)
            {
                return $data.' preprocessed config';
            }
        });

        $fields = new ConfigFields([
            ['handle' => 'one', 'field' => ['type' => 'fieldtype']],
            ['handle' => 'two', 'field' => ['type' => 'fieldtype']],
        ]);

        $this->assertEquals(['one' => null, 'two' => null], $fields->values()->all());

        $fields = $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $preProcessed = $fields->preProcess();

        $this->assertNotSame($fields, $preProcessed);
        $this->assertEquals([
            'one' => 'foo preprocessed config',
            'two' => 'bar preprocessed config',
        ], $preProcessed->values()->all());
    }
}
