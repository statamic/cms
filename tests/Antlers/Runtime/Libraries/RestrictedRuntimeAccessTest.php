<?php

namespace Tests\Antlers\Runtime\Libraries;

use Facade\Ignition\Exceptions\ViewException;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Tests\Antlers\ParserTestCase;

class RestrictedRuntimeAccessTest extends ParserTestCase
{
    public function test_fields_attempting_to_call_protected_libraries_throws_exception()
    {
        $this->expectException(ViewException::class);
        $fieldtype = new class extends Fieldtype
        {
            public function augment($value)
            {
                return 'augmented '.$value;
            }
        };

        $fieldtype->setField(new Field('test', ['antlers' => true]));

        $value = new Value('the value with {{ sys.os() }} in it', null, $fieldtype);

        $this->renderString('{{ test }}', [
            'test' => $value,
            'var' => 'howdy',
        ], true);
    }
}
