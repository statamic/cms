<?php

namespace Tests\Antlers;

use Statamic\Facades\YAML;
use Statamic\Fields\Blueprint;
use Statamic\Fields\BlueprintRepository;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Tags\Loader;
use Statamic\View\Antlers\Language\Analyzers\NodeTypeAnalyzer;
use Statamic\View\Antlers\Language\Exceptions\AntlersException;
use Statamic\View\Antlers\Language\Lexer\AntlersLexer;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\Conditions\ConditionNode;
use Statamic\View\Antlers\Language\Nodes\Conditions\ExecutionBranch;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Parser\LanguageParser;
use Statamic\View\Antlers\Language\Parser\PathParser;
use Statamic\View\Antlers\Language\Runtime\EnvironmentDetails;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\ModifierManager;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Antlers\Language\Runtime\Sandbox\Environment;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Cascade;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ParserTestCase extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /**
     * @var Blueprint|null
     */
    protected static $testBlueprint = null;

    protected static $testFieldValues = null;

    protected function setUp(): void
    {
        parent::setUp();

        GlobalRuntimeState::resetGlobalState();

        $this->setupTestBlueprintAndFields();

        NodeTypeAnalyzer::$environmentDetails = new EnvironmentDetails();
        NodeTypeAnalyzer::$environmentDetails->setModifierNames(['upper', 'lower']);
    }

    /**
     * Retrieves a test field.
     *
     * @param  string  $handle  The field's handle.
     * @return Field
     */
    protected function getTestField($handle)
    {
        return self::$testBlueprint->field($handle);
    }

    /**
     * @param  string  $handle  The test field's handle.
     * @return Value
     */
    protected function getTestValue($handle)
    {
        $field = $this->getTestField($handle);

        if ($field == null) {
            return null;
        }

        $value = self::$testFieldValues[$handle];

        return new Value($value, $handle, $field->fieldtype());
    }

    protected function setupTestBlueprintAndFields()
    {
        if (self::$testBlueprint == null) {
            $blueprintContents = YAML::parse(file_get_contents(__DIR__.'/../__fixtures__/blueprints/article.yaml'));
            $blueprintFields = collect($blueprintContents['tabs']['main']['fields'])->keyBy(function ($item) {
                return $item['handle'];
            })->map(function ($item) {
                return $item['field'];
            })->all();

            $blueprintsRepository = new BlueprintRepository();
            self::$testBlueprint = $blueprintsRepository->makeFromFields($blueprintFields);
        }

        if (self::$testFieldValues == null) {
            self::$testFieldValues = YAML::parse(file_get_contents(__DIR__.'/../__fixtures__/content/1996-11-18-dance.md'));
        }
    }

    protected function parsePath($path)
    {
        $pathParser = new PathParser();

        return $pathParser->parse($path);
    }

    protected function parseNodes($text)
    {
        $documentParser = new DocumentParser();
        $documentParser->parse($text);

        return $documentParser->getNodes();
    }

    protected function runFieldTypeTest($handle, $testTemplate = null, $additionalValues = [])
    {
        if ($testTemplate == null) {
            $testTemplate = $handle;
        }

        $value = $this->getTestValue($handle);
        $template = file_get_contents(__DIR__.'/../__fixtures__/fieldtype_tests/'.$testTemplate.'/template.antlers.html');
        $expectedResults = file_get_contents(__DIR__.'/../__fixtures__/fieldtype_tests/'.$testTemplate.'/expected.txt');

        $testData = [
            $handle => $value,
        ];

        foreach ($additionalValues as $valueName) {
            $testData[$valueName] = $this->getTestValue($valueName);
        }

        $this->assertSame($this->normalize($expectedResults), $this->normalize(
            $this->renderString($template, $testData, true)
        ), 'Field Type Test: '.$handle);
    }

    protected function normalize($string)
    {
        return trim(StringUtilities::normalizeLineEndings($string));
    }

    protected function getTemplate($template)
    {
        $path = __DIR__.'/../__fixtures__/templates/'.$template.'.antlers.html';

        return file_get_contents($path);
    }

    protected function parseTemplate($template)
    {
        $contents = $this->getTemplate($template);

        $documentParser = new DocumentParser();
        $documentParser->parse($contents);

        return $documentParser->getRenderNodes();
    }

    protected function parser($data = [], $withCoreTagsAndModifiers = false)
    {
        GlobalRuntimeState::resetGlobalState();

        $documentParser = new DocumentParser();
        $loader = new Loader();
        $envDetails = new EnvironmentDetails();

        if ($withCoreTagsAndModifiers) {
            $envDetails->setTagNames(app()->make('statamic.tags')->keys()->all());
            $envDetails->setModifierNames(app()->make('statamic.modifiers')->keys()->all());

            NodeTypeAnalyzer::$environmentDetails = $envDetails;
        }

        $processor = new NodeProcessor($loader, $envDetails);
        $processor->setData($data);

        return new RuntimeParser($documentParser, $processor, new AntlersLexer(), new LanguageParser());
    }

    protected function renderStringWithConfiguration($text, RuntimeConfiguration $config, $data = [], $withCoreTagsAndModifiers = false)
    {
        GlobalRuntimeState::resetGlobalState();

        $documentParser = new DocumentParser();
        $loader = new Loader();
        $envDetails = new EnvironmentDetails();

        if ($withCoreTagsAndModifiers) {
            $envDetails->setTagNames(app()->make('statamic.tags')->keys()->all());
            $envDetails->setModifierNames(app()->make('statamic.modifiers')->keys()->all());

            NodeTypeAnalyzer::$environmentDetails = $envDetails;
        }

        $processor = new NodeProcessor($loader, $envDetails);
        $processor->setRuntimeConfiguration($config);
        $processor->setData($data);

        $runtimeParser = new RuntimeParser($documentParser, $processor, new AntlersLexer(), new LanguageParser());

        if ($withCoreTagsAndModifiers) {
            $runtimeParser->cascade(app(Cascade::class));
        }

        return (string) $runtimeParser->parse($text, $data);
    }

    protected function renderString($text, $data = [], $withCoreTagsAndModifiers = false)
    {
        ModifierManager::$statamicModifiers = null;
        GlobalRuntimeState::resetGlobalState();

        $documentParser = new DocumentParser();
        $loader = new Loader();
        $envDetails = new EnvironmentDetails();

        if ($withCoreTagsAndModifiers) {
            $envDetails->setTagNames(app()->make('statamic.tags')->keys()->all());
            $envDetails->setModifierNames(app()->make('statamic.modifiers')->keys()->all());

            NodeTypeAnalyzer::$environmentDetails = $envDetails;
        }

        $processor = new NodeProcessor($loader, $envDetails);
        $processor->setData($data);

        $runtimeParser = new RuntimeParser($documentParser, $processor, new AntlersLexer(), new LanguageParser());
        $processor->setAntlersParserInstance($runtimeParser);

        if ($withCoreTagsAndModifiers) {
            $runtimeParser->cascade(app(Cascade::class));
        }

        return StringUtilities::normalizeLineEndings((string) $runtimeParser->parse($text, $data));
    }

    protected function getParsedRuntimeNodes($text)
    {
        $documentParser = new DocumentParser();
        $documentParser->parse($text);
        $node = $documentParser->getNodes()[0];

        $antlersParser = new LanguageParser();

        return $antlersParser->parse($node->runtimeNodes);
    }

    protected function getBoolResult($text, $data)
    {
        // Create a wrapper region we can get a node from.
        $nodeText = '{{ '.$text.' }}';
        /** @var AntlersNode $antlersNode */
        $antlersNode = $this->parseNodes($nodeText)[0];

        $lexer = new AntlersLexer();
        $tokens = $lexer->tokenize($antlersNode, $text);

        $langParser = new LanguageParser();
        $nodes = $langParser->parse($tokens);

        $sandbox = new Environment();
        $sandbox->setData($data);

        return $sandbox->evaluateBool($nodes);
    }

    protected function evaluateRaw($text, $data = [])
    {
        $text = StringUtilities::normalizeLineEndings($text);

        // Create a wrapper region we can get a node from.
        $nodeText = '{{ '.$text.' }}';
        /** @var AntlersNode $antlersNode */
        $antlersNode = $this->parseNodes($nodeText)[0];

        $lexer = new AntlersLexer();
        $tokens = $lexer->tokenize($antlersNode, $text);

        $langParser = new LanguageParser();
        $nodes = $langParser->parse($tokens);

        $loader = new Loader();
        $envDetails = new EnvironmentDetails();

        $processor = new NodeProcessor($loader, $envDetails);
        $processor->setData($data);
        $sandbox = new Environment();
        $sandbox->setProcessor($processor);
        $sandbox->setData($data);

        return $sandbox->evaluate($nodes);
    }

    protected function evaluateBoth($text, $data = [])
    {
        // Create a wrapper region we can get a node from.
        $nodeText = '{{ '.$text.' }}';
        /** @var AntlersNode $antlersNode */
        $antlersNode = $this->parseNodes($nodeText)[0];

        $lexer = new AntlersLexer();
        $tokens = $lexer->tokenize($antlersNode, $text);

        $langParser = new LanguageParser();
        $nodes = $langParser->parse($tokens);

        $loader = new Loader();
        $envDetails = new EnvironmentDetails();

        $processor = new NodeProcessor($loader, $envDetails);
        $processor->setData($data);
        $sandbox = new Environment();
        $sandbox->setProcessor($processor);
        $sandbox->setData($data);

        return [$sandbox->evaluate($nodes), $sandbox->getData()];
    }

    protected function evaluate($text, $data = [])
    {
        // Create a wrapper region we can get a node from.
        $nodeText = '{{ '.$text.' }}';
        /** @var AntlersNode $antlersNode */
        $antlersNode = $this->parseNodes($nodeText)[0];

        $lexer = new AntlersLexer();
        $tokens = $lexer->tokenize($antlersNode, $text);

        $langParser = new LanguageParser();
        $nodes = $langParser->parse($tokens);

        $loader = new Loader();
        $envDetails = new EnvironmentDetails();
        $processor = new NodeProcessor($loader, $envDetails);
        $sandbox = new Environment();
        $sandbox->setData($data);
        $sandbox->setProcessor($processor);

        $sandbox->evaluate($nodes);

        return $sandbox->getData();
    }

    protected function assertIsCondition($node)
    {
        $this->assertInstanceOf(ConditionNode::class, $node);
    }

    protected function assertIsAntlersNode($node)
    {
        $this->assertInstanceOf(AntlersNode::class, $node);
    }

    protected function assertLiteralNodeContains($node, $text)
    {
        $this->assertInstanceOf(LiteralNode::class, $node);
        $this->assertStringContainsString($text, $node->content);
    }

    protected function assertConditionalChainContainsSteps(ConditionNode $node, $chain)
    {
        $last = null;

        for ($i = 0; $i < count($chain); $i++) {
            /** @var ExecutionBranch $branch */
            $branch = $node->logicBranches[$i];

            $this->assertInstanceOf(ExecutionBranch::class, $branch);

            if ($i > 0) {
                $this->assertNotNull($branch->head->isOpenedBy);
                $this->assertSame($last, $branch->head->isOpenedBy);
            }

            $this->assertNotNull($branch->head);
            $this->assertInstanceOf(AntlersNode::class, $branch->head);
            $this->assertNotNull($branch->head->isClosedBy);

            $this->assertStringContainsString($chain[$i], $branch->head->content);
            $last = $branch->head;
        }
    }

    protected function assertThrowsParserError($string)
    {
        $this->expectException(AntlersException::class);
        $this->renderString($string);
    }
}
