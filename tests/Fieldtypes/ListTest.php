<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Lists;
use Tests\TestCase;

class ListTest extends TestCase
{
    #[Test]
    public function it_processes()
    {
        $this->assertSame(
            ['a', 2, 3, '4 and a half', 5.7, 8.3],
            $this->field()->process(['a', '2', 3, '4 and a half', '5.7', 8.3])
        );
    }

    #[Test]
    public function it_has_add_row_config()
    {
        $configFields = (new \ReflectionMethod(Lists::class, 'configFieldItems'))->invoke(new Lists);

        $this->assertArrayHasKey('add_row', $configFields);
        $this->assertSame('text', $configFields['add_row']['type']);
        $this->assertSame('Add Item Label', $configFields['add_row']['display']);
        $this->assertSame('Add Item', $configFields['add_row']['placeholder']);
    }

    private function field($config = [])
    {
        $ft = new Lists;

        return $ft->setField(new Field('test', array_merge($config, ['type' => $ft->handle()])));
    }
}
