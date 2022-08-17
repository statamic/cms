<?php

namespace Tests\Antlers\Runtime;

use Statamic\Tags\Loader;
use Statamic\View\Antlers\Language\Lexer\AntlersLexer;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Parser\LanguageParser;
use Statamic\View\Antlers\Language\Runtime\EnvironmentDetails;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;

class PreparserTest extends ParserTestCase
{
    public function test_pre_parser_can_modify_text()
    {
        $data = [
            'title' => 'hello',
            'subtitle' => 'world',
        ];

        $text = <<<'EOT'
{{ title }}
EOT;

        $documentParser = new DocumentParser();
        $loader = new Loader();
        $envDetails = new EnvironmentDetails();

        $processor = new NodeProcessor($loader, $envDetails);
        $processor->setData($data);

        $runtimeParser = new RuntimeParser($documentParser, $processor, new AntlersLexer(), new LanguageParser());
        $runtimeParser->preparse(function ($text) {
            return str_replace('{{ title }}', '{{ subtitle | upper }}', $text);
        });

        $result = StringUtilities::normalizeLineEndings((string) $runtimeParser->parse($text, $data));
        $this->assertSame('WORLD', $result);
    }
}
