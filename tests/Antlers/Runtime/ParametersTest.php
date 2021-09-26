<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\Fixtures\Addon\Tags\Test;
use Tests\Antlers\ParserTestCase;

class ParametersTest extends ParserTestCase
{
    public function test_using_interpolations_with_variable_reference_resolves_correctly()
    {
        Test::register();

        $data = [
            'size_small' => 'Value one',
            'size_large' => 'Value two',
        ];

        $template = <<<'EOT'
{{ test :variable="size_{size}" }}
EOT;

        $this->assertSame('Value one', $this->renderString($template, array_merge(
            $data, ['size' => 'small']
        ), true));

        $this->assertSame('Value two', $this->renderString($template, array_merge(
            $data, ['size' => 'large']
        ), true));

        $this->assertSame('', $this->renderString($template, array_merge(
            $data, ['size' => 'medium']
        ), true));
    }
}
