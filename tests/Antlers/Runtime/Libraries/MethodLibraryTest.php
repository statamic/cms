<?php

namespace Tests\Antlers\Runtime\Libraries;

use Tests\Antlers\ParserTestCase;

class MethodLibraryTest extends ParserTestCase
{
    public function test_method_call_without_arguments()
    {
        $template = <<<'EOT'
{{ results = method.call('\Tests\Antlers\Fixtures\TestClass@noArgs') }}{{ value }}{{ /results }}
EOT;

        $this->assertSame('12345', $this->renderString($template));
    }

    public function test_method_call_with_arguments()
    {
        $template = <<<'EOT'
{{ results = method.call('\Tests\Antlers\Fixtures\TestClass@withArgs', 'a', 'e') }}{{ value }}{{ /results }}
EOT;

        $this->assertSame('abcde', $this->renderString($template));
    }
}
