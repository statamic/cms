<?php

namespace Tests\Antlers\Parser;

use Tests\Antlers\ParserTestCase;

class CommentsTest extends ParserTestCase
{
    public function test_antlers_in_comments_does_not_get_parsed_or_trigger_errors()
    {
        $template = <<<'EOT'
{{# {{ validate | contains:required ?= "required" }} #}}
EOT;

        $this->assertSame('', $this->renderString($template));
    }

    public function test_comments_at_start_of_document_remove_whitespace()
    {
        $template = <<<'EOT'
{{# name #}}
{{ xml_header }}
<start>
    {{# comment #}}
    
<end>
EOT;

        // Ensures that the whitespace surrounding the 2nd comment is left in-tact;
        // we cannot know for certain if that whitespace was intentional or not.
        $expected = <<<'EOT'
<?xml version="1.0" encoding="utf-8" ?>
<start>
    
    
<end>
EOT;

        $this->assertSame($expected, $this->renderString($template, ['xml_header' => '<?xml version="1.0" encoding="utf-8" ?>']));
    }
}
