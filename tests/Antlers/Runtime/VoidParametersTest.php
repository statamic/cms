<?php

namespace Tests\Antlers\Runtime;

use Statamic\Tags\Tags;
use Tests\Antlers\ParserTestCase;

class VoidParametersTest extends ParserTestCase
{
    public function test_voided_parameters_are_not_sent_to_tag()
    {
        (new class extends Tags
        {
            public static $handle = 'test_void';

            public function index()
            {
                if ($this->params->has('test')) {
                    return '<'.(string) $this->params->get('test').'>';
                }

                return 'No Parameter';
            }
        })::register();

        $data = [
            'true_value' => true,
            'false_value' => false,
        ];

        $this->assertSame('<hello>', $this->renderString('{{ test_void test="hello" }}', $data, true));

        $template = <<<'EOT'
{{ test_void :test="true_value ? 'test' : 'no'" }}
EOT;

        $this->assertSame('<test>', $this->renderString($template, $data, true));

        $template = <<<'EOT'
{{ test_void :test="true_value ? void : 'no'" }}
EOT;

        $this->assertSame('No Parameter', $this->renderString($template, $data, true));

        $template = <<<'EOT'
{{ test_void test="{true_value ? void : 'no'}" }}
EOT;

        $this->assertSame('No Parameter', $this->renderString($template, $data, true));
    }
}
