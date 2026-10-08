<?php

namespace Tests\Forms\Connectors;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\NestedRules;
use Illuminate\Validation\Rule;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Fields\Fields;
use Statamic\Forms\Connectors\Connector;
use Statamic\Statamic;
use Statamic\Support\VueComponent;
use Tests\TestCase;

class ConnectorTest extends TestCase
{
    #[Test]
    public function the_handle_is_snake_cased_from_the_class_by_default()
    {
        $this->assertEquals('test_multi_word', (new TestMultiWordConnector)->handle());
    }

    #[Test]
    public function handle_can_be_defined_as_a_property()
    {
        $connector = new class extends Connector
        {
            protected static $handle = 'example';

            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('nothing');
            }
        };

        $this->assertEquals('example', $connector->handle());
    }

    #[Test]
    public function title_is_the_humanized_handle_by_default()
    {
        $this->assertEquals('Test Multi Word', (new TestMultiWordConnector)->title());
    }

    #[Test]
    public function title_can_be_defined_as_a_property()
    {
        $connector = new class extends Connector
        {
            protected static $title = 'Super Cool Example';

            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('nothing');
            }
        };

        $this->assertEquals('Super Cool Example', $connector->title());
    }

    #[Test]
    public function it_gets_the_description_and_developer()
    {
        $connector = new class extends Connector
        {
            protected $description = 'Send submissions to Acme.';
            protected $developer = 'Acme Inc';

            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('nothing');
            }
        };

        $this->assertEquals('Send submissions to Acme.', $connector->description());
        $this->assertEquals('Acme Inc', $connector->developer());
    }

    #[Test]
    public function the_small_icon_falls_back_to_the_icon()
    {
        $connector = new class extends Connector
        {
            protected $icon = 'globe-arrow';

            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('nothing');
            }
        };

        $this->assertEquals(Statamic::svg('icons/globe-arrow'), $connector->icon());
        $this->assertEquals(Statamic::svg('icons/globe-arrow'), $connector->smallIcon());
    }

    #[Test]
    public function small_icon_can_be_defined_as_a_property()
    {
        $connector = new class extends Connector
        {
            protected $icon = 'globe-arrow';
            protected $smallIcon = '<svg><circle r="1" /></svg>';

            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('nothing');
            }
        };

        $this->assertEquals(Statamic::svg('icons/globe-arrow'), $connector->icon());
        $this->assertEquals('<svg><circle r="1" /></svg>', $connector->smallIcon());
    }

    #[Test]
    public function it_counts_configured_instances_for_a_form()
    {
        $form = Form::make('contact');

        $connector = new class extends Connector
        {
            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('nothing');
            }

            public function count(\Statamic\Contracts\Forms\Form $form): ?int
            {
                return 3;
            }
        };

        $this->assertEquals(3, $connector->count($form));
    }

    #[Test]
    public function it_counts_connections_by_default()
    {
        $form = Form::make('contact')->connections([
            'test_multi_word' => [['foo' => 'bar'], ['foo' => 'baz']],
        ]);

        $this->assertEquals(2, (new TestMultiWordConnector)->count($form));
        $this->assertEquals(0, (new TestMultiWordConnector)->count(Form::make('other')));
    }

    #[Test]
    public function it_is_configured_by_default()
    {
        $this->assertTrue((new TestMultiWordConnector)->isConfigured());
    }

    #[Test]
    public function it_has_connection_validation_rules_by_default()
    {
        $this->assertEquals([
            '*' => ['array'],
            '*.enabled' => ['nullable', 'boolean'],
            '*.conditions' => ['nullable', 'array'],
            '*.conditions.*' => ['array'],
        ], (new TestMultiWordConnector)->rules(Form::make('contact')));
    }

    #[Test]
    public function it_merges_connection_rules_into_the_default_rules()
    {
        $rules = (new TestHookedConnector)->rules(Form::make('contact'));

        $this->assertEquals([
            '*' => ['array'],
            '*.token' => ['required', 'string'],
            '*.enabled' => ['nullable', 'boolean'],
            '*.conditions' => ['nullable', 'array'],
            '*.conditions.*' => ['array'],
        ], $rules);
    }

    #[Test]
    public function it_processes_connections_by_default()
    {
        $connections = (new TestMultiWordConnector)->process([
            ['id' => 'abc', 'foo' => 'bar', 'empty' => null, 'enabled' => true, 'conditions' => []],
            ['foo' => 'baz', 'enabled' => false, 'conditions' => [
                ['_id' => 'one', 'field' => 'name', 'operator' => 'equals', 'value' => 'Foo', 'join' => 'and'],
                ['_id' => 'two', 'field' => null, 'operator' => 'equals', 'value' => 'Foo'],
            ]],
        ], Form::make('contact'));

        $this->assertSame(['id' => 'abc', 'foo' => 'bar'], $connections[0]);
        $this->assertNotEmpty($connections[1]['id']);
        $this->assertSame([
            'id' => $connections[1]['id'],
            'foo' => 'baz',
            'enabled' => false,
            'conditions' => [['field' => 'name', 'operator' => 'equals', 'value' => 'Foo', 'join' => 'and']],
        ], $connections[1]);
    }

    #[Test]
    public function it_processes_connections_through_the_connection_hook()
    {
        $connections = (new TestHookedConnector)->process([
            ['id' => 'abc', 'token' => ' secret ', 'extra' => 'dropped', 'enabled' => false],
        ], Form::make('contact'));

        $this->assertSame([['id' => 'abc', 'token' => 'secret', 'enabled' => false]], $connections);
    }

    #[Test]
    public function it_pre_processes_the_config_by_default()
    {
        $config = [['id' => 'abc', 'foo' => 'bar']];

        $this->assertEquals([
            ['id' => 'abc', 'enabled' => true, 'conditions' => [], 'foo' => 'bar'],
        ], (new TestMultiWordConnector)->preProcess($config, Form::make('contact')));
    }

    #[Test]
    public function it_mints_missing_ids_when_pre_processing()
    {
        $connections = (new TestMultiWordConnector)->preProcess([['foo' => 'bar']], Form::make('contact'));

        $this->assertNotEmpty($connections[0]['id']);
        $this->assertEquals('bar', $connections[0]['foo']);
    }

    #[Test]
    public function it_pre_processes_connections_through_the_connection_hook()
    {
        $connections = (new TestHookedConnector)->preProcess([['id' => 'abc', 'token' => 'secret', 'extra' => 'dropped']], Form::make('contact'));

        $this->assertEquals([['id' => 'abc', 'token' => 'SECRET', 'enabled' => true, 'conditions' => []]], $connections);
    }

    #[Test]
    public function it_normalizes_non_list_configs_into_connections()
    {
        $connector = new TestMultiWordConnector;

        $this->assertEquals([['token' => 'secret']], $connector->setConfig(['token' => 'secret'])->config());
        $this->assertEquals([['foo' => 'bar']], $connector->setConfig([['foo' => 'bar'], 'nope', null])->config());
        $this->assertCount(1, $connector->preProcess(['token' => 'secret'], Form::make('contact')));
        $this->assertCount(1, $connector->process(['token' => 'secret'], Form::make('contact')));
    }

    #[Test]
    public function it_returns_no_jobs_by_default()
    {
        $form = Form::make('contact');

        $jobs = (new TestMultiWordConnector)->setConfig([['id' => 'abc']])->finalized($form->makeSubmission());

        $this->assertSame([], $jobs);
    }

    #[Test]
    public function it_only_makes_jobs_for_enabled_connections_whose_conditions_pass()
    {
        $form = Form::make('contact')->formFields([
            'fields' => [['handle' => 'name', 'field' => ['type' => 'text']]],
        ]);
        $submission = $form->makeSubmission()->data(['name' => 'Foo']);

        $jobs = (new TestHookedConnector)->setConfig([
            ['id' => 'one'],
            ['id' => 'two', 'enabled' => false],
            ['id' => 'three', 'conditions' => [['field' => 'name', 'operator' => 'equals', 'value' => 'Foo']]],
            ['id' => 'four', 'conditions' => [['field' => 'name', 'operator' => 'equals', 'value' => 'Bar']]],
            ['id' => 'skip'],
        ])->finalized($submission);

        $this->assertEquals(['one', 'three'], array_map(fn ($job) => $job->connection['id'], $jobs));
        $this->assertSame($submission, $jobs[0]->submission);
    }

    #[Test]
    public function it_adds_enabled_and_conditions_when_pre_processing_by_default()
    {
        $connections = (new TestMultiWordConnector)->preProcess([
            ['id' => 'one', 'foo' => 'bar', 'enabled' => false, 'conditions' => [['field' => 'a', 'operator' => 'equals', 'value' => 'b', 'join' => 'and']]],
            ['id' => 'two', 'foo' => 'baz'],
        ], Form::make('contact'));

        $this->assertFalse($connections[0]['enabled']);
        $this->assertTrue($connections[1]['enabled']);
        $this->assertCount(1, $connections[0]['conditions']);
        $this->assertNotEmpty($connections[0]['conditions'][0]['_id']);
        $this->assertSame([], $connections[1]['conditions']);
        $this->assertEquals('baz', $connections[1]['foo']);
    }

    #[Test]
    public function it_has_no_blueprint_by_default()
    {
        $this->assertNull((new TestMultiWordConnector)->blueprint(Form::make('contact')));
    }

    #[Test]
    public function it_pre_processes_connections_through_the_blueprint()
    {
        $connections = (new TestBlueprintConnector)->preProcess([
            ['id' => 'abc', 'name' => 'Foo', 'count' => '5', 'extra' => 'dropped'],
        ], Form::make('contact'));

        $this->assertEquals([
            ['name' => 'Foo', 'active' => true, 'count' => '5', 'rows' => [], 'id' => 'abc', 'enabled' => true, 'conditions' => []],
        ], $connections);
    }

    #[Test]
    public function it_processes_connections_through_the_blueprint()
    {
        $connections = (new TestBlueprintConnector)->process([
            ['id' => 'abc', 'name' => 'Foo', 'active' => false, 'count' => '5', 'extra' => 'dropped'],
        ], Form::make('contact'));

        $this->assertSame([['id' => 'abc', 'name' => 'Foo', 'active' => false, 'count' => 5]], $connections);
    }

    #[Test]
    public function it_merges_top_level_blueprint_rules_with_connection_rules()
    {
        $rules = (new TestBlueprintConnector)->rules(Form::make('contact'));

        $this->assertEquals(['required', 'string', 'max:10'], $rules['*.name']);
        $this->assertEquals(['integer', 'nullable'], $rules['*.count']);
        $this->assertEquals(['nullable'], $rules['*.active']);
        $this->assertEquals(['required'], $rules['*.rows.*.label']);
        $this->assertEquals(['nullable', 'boolean'], $rules['*.enabled']);
        $this->assertEquals([
            '*', '*.name', '*.active', '*.count', '*.rows', '*.rows.*.label', '*.enabled', '*.conditions', '*.conditions.*',
        ], array_keys($rules));
    }

    #[Test]
    public function it_validates_connections_with_blueprint_rules()
    {
        $form = Form::make('contact');
        $rules = (new TestBlueprintConnector)->rules($form);

        $this->assertTrue(Validator::make([['name' => 'Foo']], $rules)->passes());
        $this->assertEquals(['0.name'], Validator::make([['count' => 1]], $rules)->errors()->keys());
        $this->assertEquals(['0.name'], Validator::make([['name' => 'Far too long']], $rules)->errors()->keys());
        $this->assertEquals(['0.rows.0.label'], Validator::make([['name' => 'Foo', 'rows' => [['label' => null]]]], $rules)->errors()->keys());
    }

    #[Test]
    public function it_compiles_for_each_connection_rules_against_each_item()
    {
        $connector = new class extends TestBlueprintConnector
        {
            protected function connectionRules(\Statamic\Contracts\Forms\Form $form): array
            {
                return [
                    'rows.*.label' => Rule::forEach(fn ($value, $attribute, $data, $row) => ($row['strict'] ?? false) ? ['required'] : []),
                ];
            }
        };

        $rules = $connector->rules(Form::make('contact'));

        $this->assertEquals(['0.rows.0.label'], Validator::make([
            ['name' => 'Foo', 'rows' => [['strict' => true, 'label' => null], ['label' => null]]],
        ], $rules)->errors()->keys());
        $this->assertInstanceOf(NestedRules::class, $rules['*.rows.*.label']);
    }

    #[Test]
    public function it_builds_publish_props_from_the_blueprint_and_given_connections()
    {
        $form = Form::make('contact')->connections(['test_blueprint' => [['id' => 'stored']]]);

        $props = (new TestBlueprintConnector)->props($form, [
            ['id' => 'one', 'name' => 'Foo', 'rows' => [['id' => 'row-1', 'label' => 'Bar']]],
            ['id' => 'two'],
            ['name' => 'No id'],
        ]);

        $this->assertEquals(['blueprint', 'meta', 'defaults'], array_keys($props));
        $this->assertEquals(['name', 'active', 'count', 'rows'], collect($props['blueprint']['tabs'][0]['sections'][0]['fields'])->pluck('handle')->all());
        $this->assertEquals(['one', 'two'], array_keys($props['meta']));
        $this->assertEquals(['name', 'active', 'count', 'rows'], array_keys($props['meta']['one']));
        $this->assertEquals(['row-1'], array_keys($props['meta']['one']['rows']['existing']));
        $this->assertEquals([], $props['meta']['two']['rows']['existing']);
        $this->assertEquals(['name' => null, 'active' => true, 'count' => null, 'rows' => []], $props['defaults']['values']);
        $this->assertEquals(['name', 'active', 'count', 'rows'], array_keys($props['defaults']['meta']));
    }

    #[Test]
    public function connection_meta_can_be_overridden()
    {
        $connector = new class extends TestBlueprintConnector
        {
            protected function connectionMeta(array $connection, \Statamic\Contracts\Forms\Form $form): array
            {
                return [...parent::connectionMeta($connection, $form), 'count' => ['options' => [$connection['id']]]];
            }
        };

        $props = $connector->props(Form::make('contact'), [['id' => 'one']]);

        $this->assertEquals(['options' => ['one']], $props['meta']['one']['count']);
    }

    #[Test]
    public function connection_fields_can_be_overridden_for_values_and_meta()
    {
        $connector = new class extends TestBlueprintConnector
        {
            protected function connectionFields(array $connection, \Statamic\Contracts\Forms\Form $form): Fields
            {
                return parent::connectionFields([...$connection, 'rows' => [['id' => 'added', 'label' => 'Added']]], $form);
            }
        };

        $form = Form::make('contact');

        $this->assertEquals('added', $connector->preProcess([['id' => 'one']], $form)[0]['rows'][0]['_id']);
        $this->assertEquals(['added'], array_keys($connector->props($form, [['id' => 'one']])['meta']['one']['rows']['existing']));
    }

    #[Test]
    public function it_renders_a_vue_component()
    {
        $form = Form::make('contact');

        $connector = new class extends Connector
        {
            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('acme-connector', [
                    'foo' => 'bar',
                ]);
            }
        };

        $component = $connector->render($form);

        $this->assertInstanceOf(VueComponent::class, $component);
        $this->assertEquals([
            'name' => 'acme-connector',
            'props' => ['foo' => 'bar'],
        ], $component->toArray());
    }
}

class TestMultiWordConnector extends Connector
{
    public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
    {
        return VueComponent::render('nothing');
    }
}

class TestHookedConnector extends Connector
{
    public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
    {
        return VueComponent::render('nothing');
    }

    protected function job(Submission $submission, array $connection): ?object
    {
        if ($connection['id'] === 'skip') {
            return null;
        }

        return (object) ['submission' => $submission, 'connection' => $connection];
    }

    protected function preProcessConnection(array $connection, \Statamic\Contracts\Forms\Form $form): array
    {
        return ['token' => strtoupper($connection['token'] ?? '')];
    }

    protected function connectionRules(\Statamic\Contracts\Forms\Form $form): array
    {
        return ['token' => ['required', 'string']];
    }

    protected function processConnection(array $connection, \Statamic\Contracts\Forms\Form $form): array
    {
        return ['token' => trim($connection['token'] ?? '')];
    }
}

class TestBlueprintConnector extends Connector
{
    public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
    {
        return VueComponent::render('nothing');
    }

    public function props(\Statamic\Contracts\Forms\Form $form, array $connections): array
    {
        return $this->blueprintProps($form, $connections);
    }

    public function blueprint(\Statamic\Contracts\Forms\Form $form): \Statamic\Fields\Blueprint
    {
        return Blueprint::make()->setContents(['tabs' => ['main' => ['sections' => [['fields' => [
            ['handle' => 'name', 'field' => ['type' => 'text', 'validate' => ['required']]],
            ['handle' => 'active', 'field' => ['type' => 'toggle', 'default' => true]],
            ['handle' => 'count', 'field' => ['type' => 'integer']],
            ['handle' => 'rows', 'field' => ['type' => 'grid', 'fields' => [
                ['handle' => 'label', 'field' => ['type' => 'text', 'validate' => ['required']]],
            ]]],
        ]]]]]]);
    }

    protected function connectionRules(\Statamic\Contracts\Forms\Form $form): array
    {
        return [
            'name' => 'string|max:10',
            'rows.*.label' => ['required'],
        ];
    }
}
