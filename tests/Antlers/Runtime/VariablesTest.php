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
}
