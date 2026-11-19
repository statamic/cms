<?php

namespace Tests\Antlers\Parser;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Statamic\View\Antlers\Language\Analyzers\NodeTypeAnalyzer;
use Statamic\View\Antlers\Language\Parser\IdentifierFinder;
use Tests\Antlers\ParserTestCase;

class IdentifierFinderTest extends ParserTestCase
{
    #[Test]
    public function it_finds_identifiers()
    {
        $template = <<<'EOT'
{{ foo }}
{{ bar:baz }}
{{ collection:blog :limit="the_limit" alfa="bravo" charlie="{delta} {echo}" }}
  {{ title }}
{{ /collection:blog }}
EOT;
        $this->assertEquals([
            'foo',
            'bar',
            'baz',
            'collection',
            'blog',
            'delta',
            'echo',
            'the_limit',
            'title',
        ], (new IdentifierFinder)->getIdentifiers($template));
    }

    #[Test]
    public function it_finds_identifiers_when_nothing_has_resolved_the_parser()
    {
        NodeTypeAnalyzer::$environmentDetails = null;

        $this->assertEquals(['slug'], Antlers::identifiers('blog/{{ slug }}'));
    }
}
