<?php

namespace Tests\Antlers\Runtime;

use ReflectionProperty;
use RuntimeException;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\Tags\Loader;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Antlers\Language\Lexer\AntlersLexer;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Parser\LanguageParser;
use Statamic\View\Antlers\Language\Runtime\EnvironmentDetails;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Antlers\Language\Runtime\Tracing\NodeVisitorContract;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;
use Throwable;

class PreparserTest extends ParserTestCase
{
    public function test_visitors_registered_after_the_parser_resolves_are_used_immediately()
    {
        $parser = app(Parser::class);
        $secondParser = app(Parser::class);
        $visitor = new RecordingNodeVisitor;

        Runtime::addVisitor($visitor);

        foreach ([$parser, $secondParser, app(Parser::class)] as $index => $runtime) {
            $visitor->nodes = [];
            $runtime->parse('<p data-parser="'.$index.'">{{ title }}</p>', ['title' => 'Hello']);

            $this->assertNotEmpty($visitor->nodes);
        }
    }

    public function test_registered_visitors_keep_the_render_node_cache_across_parser_resolutions()
    {
        Runtime::addVisitor(new RecordingNodeVisitor);
        app(Parser::class)->parse('<p>{{ title }}</p>', ['title' => 'Hello']);
        $cached = RuntimeParser::getNodeCache();

        $this->assertNotEmpty($cached);

        app(Parser::class)->parse('<p>{{ title }}</p>', ['title' => 'Hello']);

        $this->assertSame($cached, RuntimeParser::getNodeCache());
    }

    public function test_runtime_facade_preparsers_reach_existing_and_future_parsers()
    {
        $parser = app(Parser::class);
        $secondParser = app(Parser::class);

        Runtime::preparse(fn ($template) => str_replace('{{ title }}', '{{ subtitle }}', $template));

        foreach ([$parser, $secondParser, app(Parser::class)] as $runtime) {
            $this->assertSame('New', (string) $runtime->parse('{{ title }}', ['title' => 'Old', 'subtitle' => 'New']));
        }
    }

    public function test_runtime_facade_preparsers_and_visitors_can_be_removed()
    {
        $parser = app(Parser::class);
        $visitor = new RecordingNodeVisitor;
        $preparser = fn ($template) => str_replace('{{ title }}', '{{ subtitle }}', $template);
        $data = ['title' => 'Old', 'subtitle' => 'New'];

        Runtime::preparse($preparser)->addVisitor($visitor);

        $this->assertSame('<p>New</p>', (string) $parser->parse('<p>{{ title }}</p>', $data));
        $this->assertNotEmpty($visitor->nodes);

        Runtime::removePreparser($preparser)->removeVisitor($visitor);
        $visitor->nodes = [];

        foreach ([$parser, app(Parser::class)] as $runtime) {
            $this->assertSame('<p>Old</p>', (string) $runtime->parse('<p>{{ title }}</p>', $data));
            $this->assertSame([], $visitor->nodes);
        }

        Runtime::preparse($preparser)->addVisitor($visitor)->flush();

        foreach ([$parser, app(Parser::class)] as $runtime) {
            $this->assertSame('<p>Old</p>', (string) $runtime->parse('<p>{{ title }}</p>', $data));
            $this->assertSame([], $visitor->nodes);
        }
    }

    public function test_reset_runtime_configuration_withdraws_its_html_annotation_and_visitors_only()
    {
        $documentParser = new DocumentParser;
        $processor = new NodeProcessor(new Loader, new EnvironmentDetails);
        $parser = new RuntimeParser($documentParser, $processor, new AntlersLexer, new LanguageParser);
        $configuredVisitor = new RecordingNodeVisitor;
        $localVisitor = new RecordingNodeVisitor;
        $sharedVisitor = new RecordingNodeVisitor;
        $lateLocalVisitor = new RecordingNodeVisitor;
        $configuration = new RuntimeConfiguration;
        $configuration->annotateHtmlContext = true;
        $configuration->addVisitor($configuredVisitor);
        $configuration->addVisitor($sharedVisitor);
        $configuration->addVisitor($lateLocalVisitor);
        $documentParser->addVisitor($localVisitor);
        $documentParser->addVisitor($sharedVisitor);

        $parser->setRuntimeConfiguration($configuration);
        $parser->parse('<p>{{ first }}</p>', ['first' => 'First']);

        $this->assertNotEmpty($configuredVisitor->nodes);
        $this->assertSame($configuredVisitor->nodes, $sharedVisitor->nodes);
        $this->assertNotNull(collect($documentParser->getNodes())
            ->first(fn ($node) => $node instanceof AntlersNode)
            ->htmlContext);

        $configuredVisitor->nodes = [];
        $localVisitor->nodes = [];
        $sharedVisitor->nodes = [];
        $lateLocalVisitor->nodes = [];
        $documentParser->addVisitor($lateLocalVisitor);
        $parser->resetRuntimeConfiguration();
        $parser->parse('<p>{{ first }}</p>', ['first' => 'Second']);

        $this->assertSame([], $configuredVisitor->nodes);
        $this->assertNotEmpty($localVisitor->nodes);
        $this->assertSame($localVisitor->nodes, $sharedVisitor->nodes);
        $this->assertSame($localVisitor->nodes, $lateLocalVisitor->nodes);
        $this->assertNull(collect($documentParser->getNodes())
            ->first(fn ($node) => $node instanceof AntlersNode)
            ->htmlContext);
    }

    public function test_a_preparser_registered_on_both_the_parser_and_the_configuration_runs_once()
    {
        $calls = 0;
        $preparser = function ($template) use (&$calls) {
            $calls++;

            return $template;
        };

        $config = new RuntimeConfiguration();
        $config->preparse($preparser);

        $parser = $this->parser();
        $parser->setRuntimeConfiguration($config);

        $parser->preparse($preparser);
        $parser->parse('{{ title }}', ['title' => 'Hello']);

        $this->assertSame(1, $calls);
    }

    public function test_preparsers_changed_after_a_render_apply_to_the_next_render()
    {
        $configured = fn ($template) => str_replace('{{ title }}', '{{ subtitle }}', $template);
        $local = fn ($template) => str_replace('{{ subtitle }}', '{{ caption }}', $template);
        $data = ['title' => 'Title', 'subtitle' => 'Subtitle', 'caption' => 'Caption'];

        $config = new RuntimeConfiguration;
        $parser = $this->parser();
        $parser->setRuntimeConfiguration($config);

        $this->assertSame('Title', (string) $parser->parse('{{ title }}', $data));

        $config->preparse($configured);

        $this->assertSame('Subtitle', (string) $parser->parse('{{ title }}', $data));

        $parser->preparse($local);

        $this->assertSame('Caption', (string) $parser->parse('{{ title }}', $data));

        $config->removePreparser($configured);

        $this->assertSame('Title', (string) $parser->parse('{{ title }}', $data));
        $this->assertSame('Caption', (string) $parser->parse('{{ subtitle }}', $data));
    }

    public function test_html_context_annotation_follows_the_configuration_between_renders()
    {
        $documentParser = new DocumentParser;
        $processor = new NodeProcessor(new Loader, new EnvironmentDetails);
        $parser = new RuntimeParser($documentParser, $processor, new AntlersLexer, new LanguageParser);
        $config = new RuntimeConfiguration;
        $parser->setRuntimeConfiguration($config);
        $firstNode = fn () => collect($documentParser->getNodes())->first(fn ($node) => $node instanceof AntlersNode);

        $parser->parse('<p>{{ first }}</p>', ['first' => 'First']);

        $this->assertFalse($documentParser->annotatesHtmlContext());
        $this->assertNull($firstNode()->htmlContext);

        $config->annotateHtmlContext = true;
        $parser->parse('<p>{{ first }}</p>', ['first' => 'First']);

        $this->assertTrue($documentParser->annotatesHtmlContext());
        $this->assertNotNull($firstNode()->htmlContext);

        $config->annotateHtmlContext = false;
        $parser->parse('<p>{{ second }}</p>', ['second' => 'Second']);

        $this->assertFalse($documentParser->annotatesHtmlContext());
        $this->assertNull($firstNode()->htmlContext);
    }

    public function test_a_failing_preparser_does_not_leak_the_parsers_render_depth()
    {
        $calls = 0;
        $parser = $this->parser();
        $parser->preparse(function ($template) use (&$calls) {
            $calls++;

            if ($calls === 1) {
                throw new RuntimeException('preparser failed');
            }

            return $template;
        });

        try {
            $parser->parse('{{ title }}', ['title' => 'First']);
            $this->fail('Expected the preparser to throw.');
        } catch (Throwable $throwable) {
            $this->assertStringContainsString('preparser failed', $throwable->getMessage());
        }

        $depth = new ReflectionProperty($parser, 'parseStack');
        $depth->setAccessible(true);

        $this->assertSame(0, $depth->getValue($parser));
        $this->assertSame('Second', (string) $parser->parse('{{ title }}', ['title' => 'Second']));
    }

    public function test_null_and_empty_templates_render_as_empty_strings()
    {
        $parser = $this->parser();
        $parser->preparse(fn ($template) => $template);

        $this->assertSame('', (string) $parser->parse(null, []));
        $this->assertSame('', (string) $parser->parse('', []));
    }

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

class RecordingNodeVisitor implements NodeVisitorContract
{
    public array $nodes = [];

    public function visit(AbstractNode $node)
    {
        $this->nodes[] = $node;
    }
}
