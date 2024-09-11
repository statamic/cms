<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Carbon\Carbon;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Bard;
use Tests\Antlers\ParserTestCase;

class BardFieldtypeTest extends ParserTestCase
{
    public function test_render_bard_field()
    {
        $this->runFieldTypeTest('bard');
    }

    public function test_raw_parameter_style_modifier_can_be_used_on_values()
    {
        $this->runFieldTypeTest('bard', 'bard_raw_parameter_modifier');
    }

    public function test_antlers_true_bard_fields_correct_for_html_encoded_values()
    {
        $bard = new Bard();
        $field = new Field('test', [
            'type' => 'bard',
            'antlers' => true,
        ]);
        $bard->setField($field);

        $textContent = <<<'EOT'
{{ if 1 > 3 }}Yes.{{ else }}No.{{ /if }}
{{ if 1 < 3 && true == true }}Yes.{{ else }}No.{{ /if }}
{{ if 3 > 1 }}3 is bigger{{ /if }}
{{ now format="Y" }}
Just some content
EOT;

        $data = [
            [
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $textContent,
                    ],
                ],
            ],
        ];

        $value = new Value($data, 'test', $bard);

        $template = <<<'EOT'
{{ test }}
EOT;

        $expected = <<<'EOT'
<p>No.
Yes.
3 is bigger
2019
Just some content</p>
EOT;

        $this->assertSame($expected, $this->renderString($template, [
            'now' => Carbon::parse('2019-03-10 13:00'),
            'test' => $value,
        ]));
    }
}
