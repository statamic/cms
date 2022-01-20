<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;

class AutomaticStatementTerminatorsTest extends ParserTestCase
{
    public function test_automatic_statement_terminators_are_added_after_the_righthand_side_of_an_assignment()
    {
        $template = <<<'EOT'
{{
    $michael = 9986000
    $minutes_in_a_year = 60 * 24 * 365
    (($michael / $minutes_in_a_year) | format_number(0)) + " years"
}}
EOT;

        $this->assertSame('19 years', $this->renderString($template));
    }
}
