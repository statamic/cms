<?php

namespace Fieldtypes;

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

    private function field($config = [])
    {
        $ft = new Lists;

        return $ft->setField(new Field('test', array_merge($config, ['type' => $ft->handle()])));
    }
}
