<?php

namespace Tests\Antlers;

use Orchestra\Testbench\TestCase;
use Facade\Ignition\Exceptions\ViewException;
use Statamic\Facades\YAML;
use Statamic\Fields\Blueprint;
use Statamic\Fields\BlueprintRepository;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;
use Statamic\Tags\Loader;
use Statamic\View\Antlers\Language\LanguageServiceProvider;
use Statamic\View\Cascade;
use Statamic\View\Antlers\Language\Analyzers\NodeTypeAnalyzer;
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
use Statamic\View\Antlers\Language\Runtime\Libraries\LibraryManager;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Antlers\Language\Runtime\Sandbox\Environment;
use Statamic\View\Antlers\Language\Runtime\StackReplacementManager;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\PreventSavingStacheItemsToDisk;

class ParserTestCase extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function assertThrowsParserError($string)
    {
        $this->expectException(ViewException::class);
        $this->renderString($string);
    }

    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
            LanguageServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    /**
     * @var Blueprint|null
     */
    protected static $testBlueprint = null;

    protected static $testFieldValues = null;

    protected function setUp(): void
    {
        parent::setUp();
        GlobalRuntimeState::$environmentId = StringUtilities::uuidv4();

        $this->setupTestBlueprintAndFields();

        if (isset($uses[PreventSavingStacheItemsToDisk::class])) {
            $this->preventSavingStacheItemsToDisk();
        }

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
        $value = self::$testFieldValues[$handle];

        return new Value($value, $handle, $field->fieldtype());
    }

    public function tearDown(): void
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[PreventSavingStacheItemsToDisk::class])) {
            $this->deleteFakeStacheDirectory();
        }

        parent::tearDown();
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $configs = [
            'assets', 'cp', 'forms', 'routes', 'static_caching',
            'sites', 'stache', 'system', 'users',
        ];

        foreach ($configs as $config) {
            $app['config']->set("statamic.$config", require(__DIR__."/../../config/{$config}.php"));
        }
    }

    protected function getEnvironmentSetUp($app)
    {
        // We changed the default sites setup but the tests assume defaults like the following.
        $app['config']->set('statamic.sites', [
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://localhost/'],
            ],
        ]);
        $app['config']->set('auth.providers.users.driver', 'statamic');
        $app['config']->set('statamic.stache.watcher', false);
        $app['config']->set('statamic.users.repository', 'file');
        $app['config']->set('statamic.stache.stores.users', [
            'class' => \Statamic\Stache\Stores\UsersStore::class,
            'directory' => __DIR__.'/__fixtures__/users',
        ]);

        $app['config']->set('statamic.stache.stores.taxonomies.directory', __DIR__.'/../__fixtures__/content/taxonomies');
        $app['config']->set('statamic.stache.stores.terms.directory', __DIR__.'/../__fixtures__/content/taxonomies');
        $app['config']->set('statamic.stache.stores.collections.directory', __DIR__.'/../__fixtures__/content/collections');
        $app['config']->set('statamic.stache.stores.entries.directory', __DIR__.'/../__fixtures__/content/collections');
        $app['config']->set('statamic.stache.stores.navigation.directory', __DIR__.'/../__fixtures__/content/navigation');
        $app['config']->set('statamic.stache.stores.globals.directory', __DIR__.'/../__fixtures__/content/globals');
        $app['config']->set('statamic.stache.stores.asset-containers.directory', __DIR__.'/../__fixtures__/content/assets');
        $app['config']->set('statamic.stache.stores.nav-trees.directory', __DIR__.'/../__fixtures__/content/structures/navigation');
        $app['config']->set('statamic.stache.stores.collection-trees.directory', __DIR__.'/../__fixtures__/content/structures/collections');

        $app['config']->set('statamic.api.enabled', true);
        $app['config']->set('statamic.graphql.enabled', true);
        $app['config']->set('statamic.editions.pro', true);

        $app['config']->set('cache.stores.outpost', [
            'driver' => 'file',
            'path' => storage_path('framework/cache/outpost-data'),
        ]);

        $viewPaths = $app['config']->get('view.paths');
        $viewPaths[] = __DIR__.'/../__fixtures__/views/';

        $app['config']->set('view.paths', $viewPaths);
    }

    protected function setupTestBlueprintAndFields()
    {
        if (self::$testBlueprint == null) {
            $blueprintContents = YAML::parse(file_get_contents(__DIR__.'/../__fixtures__/blueprints/article.yaml'));
            $blueprintFields = collect($blueprintContents['sections']['main']['fields'])->keyBy(function ($item) {
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

    /**
     * Creates and returns a new LibraryManager instance.
     *
     * @return LibraryManager
     */
    protected function getLibraryManager()
    {
        $manager = new LibraryManager();
        $manager->loadCoreLibraries();

        return $manager;
    }

    protected function runFieldTypeTest($handle)
    {
        $value = $this->getTestValue($handle);
        $template = file_get_contents(__DIR__.'/../__fixtures__/fieldtype_tests/'.$handle.'/template.antlers.html');
        $expectedResults = file_get_contents(__DIR__.'/../__fixtures__/fieldtype_tests/'.$handle.'/expected.txt');

        $this->assertSame($this->normalize($expectedResults), $this->normalize(
            $this->renderString($template, [$handle => $value], true)
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

    protected function parser($data, $withCoreTagsAndModifiers = false)
    {
        GlobalRuntimeState::$yieldCount = 0;
        GlobalRuntimeState::$yieldStacks = [];
        StackReplacementManager::clearStackState();

        $documentParser = new DocumentParser();
        $loader = new Loader();
        $envDetails = new EnvironmentDetails();

        if ($withCoreTagsAndModifiers) {
            $envDetails->setTagNames(app()->make('statamic.tags')->keys()->all());
            $envDetails->setModifierNames(app()->make('statamic.modifiers')->keys()->all());

            NodeTypeAnalyzer::$environmentDetails = $envDetails;
        }

        $processor = new NodeProcessor($loader, $envDetails, $this->getLibraryManager());
        $processor->setData($data);

        $runtimeParser = new RuntimeParser($documentParser, $processor);

        return $runtimeParser;
    }

    protected function renderLibraryMethod($text, $data = [], $withCoreTagsAndModifiers = false)
    {
        $text = '{{ '.$text.' }}';

        return $this->renderString($text, $data, $withCoreTagsAndModifiers);
    }

    protected function renderStringWithConfiguration($text, RuntimeConfiguration $config, $data = [], $withCoreTagsAndModifiers = false)
    {
        GlobalRuntimeState::$yieldCount = 0;
        GlobalRuntimeState::$yieldStacks = [];
        StackReplacementManager::clearStackState();

        $documentParser = new DocumentParser();
        $loader = new Loader();
        $envDetails = new EnvironmentDetails();

        if ($withCoreTagsAndModifiers) {
            $envDetails->setTagNames(app()->make('statamic.tags')->keys()->all());
            $envDetails->setModifierNames(app()->make('statamic.modifiers')->keys()->all());

            NodeTypeAnalyzer::$environmentDetails = $envDetails;
        }

        $processor = new NodeProcessor($loader, $envDetails, $this->getLibraryManager());
        $processor->setRuntimeConfiguration($config);
        $processor->setData($data);

        $runtimeParser = new RuntimeParser($documentParser, $processor);

        if ($withCoreTagsAndModifiers) {
            $runtimeParser->cascade(app(Cascade::class));
        }

        return (string) $runtimeParser->parse($text, $data);
    }

    protected function renderString($text, $data = [], $withCoreTagsAndModifiers = false)
    {
        GlobalRuntimeState::$yieldCount = 0;
        GlobalRuntimeState::$yieldStacks = [];
        StackReplacementManager::clearStackState();

        $documentParser = new DocumentParser();
        $loader = new Loader();
        $envDetails = new EnvironmentDetails();

        if ($withCoreTagsAndModifiers) {
            $envDetails->setTagNames(app()->make('statamic.tags')->keys()->all());
            $envDetails->setModifierNames(app()->make('statamic.modifiers')->keys()->all());

            NodeTypeAnalyzer::$environmentDetails = $envDetails;
        }

        $processor = new NodeProcessor($loader, $envDetails, $this->getLibraryManager());
        $processor->setData($data);

        $runtimeParser = new RuntimeParser($documentParser, $processor);

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

        $sandbox = new Environment($this->getLibraryManager());
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

        $processor = new NodeProcessor($loader, $envDetails, $this->getLibraryManager());
        $processor->setData($data);
        $sandbox = new Environment($this->getLibraryManager());
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

        $processor = new NodeProcessor($loader, $envDetails, $this->getLibraryManager());
        $processor->setData($data);
        $sandbox = new Environment($this->getLibraryManager());
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

        $libs = $this->getLibraryManager();
        $loader = new Loader();
        $envDetails = new EnvironmentDetails();
        $processor = new NodeProcessor($loader, $envDetails, $libs);
        $sandbox = new Environment($libs);
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
}