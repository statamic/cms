<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Code;
use Tests\Antlers\ParserTestCase;

class CodeFieldtypeTest extends ParserTestCase
{
    public function test_render_code_fieldtype()
    {
        $this->runFieldTypeTest('code');
    }

    public function test_code_fieldtype_with_antlers_true()
    {
        $code = new Code();
        $field = new Field('code_field', [
            'type' => 'code',
            'antlers' => true,
        ]);

        $code->setField($field);
        $value = new Value('Hello, {{ name }}.', 'code_field', $code);

        $template = <<<'EOT'
<{{ code_field }}>
EOT;

        $this->assertSame('<Hello, wilderness.>', $this->renderString($template, ['code_field' => $value, 'name' => 'wilderness',]));
    }
}
