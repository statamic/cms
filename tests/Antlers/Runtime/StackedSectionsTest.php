<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;

class StackedSectionsTest extends ParserTestCase
{
    public function test_sections_can_be_used_like_stacks_when_nested()
    {
        $template = <<<'EOT'
<docstart>
{{ section:section_name }}
    <one>
{{ yield:section_name }}

{{ /section:section_name }}

{{ section:section_name }}
    <two>
    {{ yield:section_name }}
{{ /section:section_name }}

{{ section:section_name }}
    <three>
    {{ yield:section_name }}
{{ /section:section_name }}

<before>
{{ yield:section_name }}
<after>
<docend>
EOT;

        // The inverted order (three, two, one) was intentional to match the existing behavior.
        $expected = <<<'EOT'
<docstart>






<before>

    <three>
    
    <two>
    
    <one>





<after>
<docend>
EOT;

        $this->assertSame($expected, $this->renderString($template, [], true));
    }
}
