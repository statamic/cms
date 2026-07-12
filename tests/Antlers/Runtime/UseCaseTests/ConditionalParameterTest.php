<?php

namespace Tests\Antlers\Runtime\UseCaseTests;

use Tests\Antlers\Fixtures\Addon\Tags\TestTags as Test;
use Tests\Antlers\ParserTestCase;

class ConditionalParameterTest extends ParserTestCase
{
    public function test_switch_conditions_can_be_used_in_parameters()
    {
        // Use case: developer needs to conditionally supply a parameter value.

        Test::register();

        $template = <<<'EOT'
{{ test variable="{switch(
            (size == 'sm') => '(min-width: 768px) 35vw, 90vw',
            (size == 'md') => '(min-width: 768px) 55vw, 90vw',
            (size == 'lg') => '(min-width: 768px) 75vw, 90vw',
            (size == 'xl') => '90vw'
        )}" }}
EOT;

        $this->assertSame('(min-width: 768px) 35vw, 90vw', $this->renderString($template, [
            'size' => 'sm',
        ], true));

        $this->assertSame('(min-width: 768px) 55vw, 90vw', $this->renderString($template, [
            'size' => 'md',
        ], true));

        $this->assertSame('(min-width: 768px) 75vw, 90vw', $this->renderString($template, [
            'size' => 'lg',
        ], true));

        $this->assertSame('90vw', $this->renderString($template, [
            'size' => 'xl',
        ], true));
    }
}
