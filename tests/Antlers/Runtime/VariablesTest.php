<?php

namespace Tests\Antlers\Runtime;

use Statamic\Fields\LabeledValue;
use Tests\Antlers\ParserTestCase;

class VariablesTest extends ParserTestCase
{
    public function test_value_objects_can_be_used_for_array_lookups()
    {
        $template = <<<'EOT'
<{{ sizes[size] }}><{{ sizes[size_two] }}><{{ sizes[size_three] }}><{{ view:sizes[size] }}><{{ view:sizes[size_two] }}><{{ view:sizes[size_three] }}>
EOT;

        $value = new LabeledValue('xl', 'XL');
        $valueTwo = new LabeledValue('md', 'MD');
        $valueThree = new LabeledValue('lg', 'LG');

        $sizes = [
            'md' => '(min-width: 768px) 55vw, 90vw',
            'lg' => '(min-width: 768px) 75vw, 90vw',
            'xl' => '90vw',
        ];

        $data = [
            'sizes' => $sizes,
            'view' => [
                'sizes' => $sizes,
            ],
            'size' => $value,
            'size_two' => $valueTwo,
            'size_three' => $valueThree,
        ];

        $this->assertSame('<90vw><(min-width: 768px) 55vw, 90vw><(min-width: 768px) 75vw, 90vw><90vw><(min-width: 768px) 55vw, 90vw><(min-width: 768px) 75vw, 90vw>', $this->renderString(
            $template, $data
        ));
    }

    public function test_value_objects_can_still_be_augmented_when_used_for_array_lookups()
    {
        $template = <<<'EOT'
<{{ sizes[size.label] }}><{{ sizes[size_two.label] }}><{{ sizes[size_three.label] }}><{{ view:sizes[size:label] }}><{{ view:sizes[size_two:label] }}><{{ view:sizes[size_three:label] }}>
EOT;

        $value = new LabeledValue('xl', 'extra_large');
        $valueTwo = new LabeledValue('md', 'medium');
        $valueThree = new LabeledValue('lg', 'large');

        $sizes = [
            'medium' => '(min-width: 768px) 55vw, 90vw',
            'large' => '(min-width: 768px) 75vw, 90vw',
            'extra_large' => '90vw',
        ];

        $data = [
            'sizes' => $sizes,
            'view' => [
                'sizes' => $sizes,
            ],
            'size' => $value,
            'size_two' => $valueTwo,
            'size_three' => $valueThree,
        ];

        $this->assertSame('<90vw><(min-width: 768px) 55vw, 90vw><(min-width: 768px) 75vw, 90vw><90vw><(min-width: 768px) 55vw, 90vw><(min-width: 768px) 75vw, 90vw>', $this->renderString(
            $template, $data
        ));
    }

    public function test_variables_with_hyphens_resolve_their_values()
    {
        $data = [
            'test' => 100,
            'var' => 45,
            'test-var' => 'The Value',
            'var-one' => 100,
            'var-two' => 45,
            'array-test' => [
                'key' => 'The Key',
                'secret' => 'The Secret',
            ],
            'array-values' => [
                'one',
                'two',
                'three',
            ],
            'play-sad_Trombone' => 'Test Value',
            'sOmeth0adsfj89235-f9_23598sdfg3294sdf_-why_would-yoUdoThis-butletsS-e_e-W-h-a-t-H_a-p-p-e-n-s' => 'Hello',
        ];

        $this->assertSame('Hello', $this->renderString('{{ sOmeth0adsfj89235-f9_23598sdfg3294sdf_-why_would-yoUdoThis-butletsS-e_e-W-h-a-t-H_a-p-p-e-n-s }}', $data));
        $this->assertSame('Test Value', $this->renderString('{{play-sad_Trombone }}', $data));
        $this->assertSame('<The Value><100><45><55>', $this->renderString('<{{ test-var }}><{{ test }}><{{ var }}><{{ test - var }}>', $data));
        $this->assertSame('<The Key><The Secret>', $this->renderString('<{{ array-test:key }}><{{array-test:secret}}>', $data));
        $this->assertSame('<The Key><The Secret>', $this->renderString('{{ array-test }}<{{key}}><{{ secret }}>{{ /array-test }}', $data));
        $this->assertSame('<one><two><three>', $this->renderString('{{array-values }}<{{ value}}>{{ /array-values}}', $data));
        $this->assertSame('100', $this->renderString('{{ var-one }}', $data));
        $this->assertSame('45', $this->renderString('{{ var-two }}', $data));
        $this->assertSame('55', $this->renderString('{{ var-one - var-two }}', $data));
        $this->assertSame('-55', $this->renderString('{{ -1 * var-one - -1 * var-two }}', $data));
    }
}
