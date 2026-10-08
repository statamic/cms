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
        $connector = new class extends TestConnector
        {
            protected static $handle = 'example';
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
        $connector = new class extends TestConnector
        {
            protected static $title = 'Super Cool Example';
        };

        $this->assertEquals('Super Cool Example', $connector->title());
    }

    #[Test]
    public function it_gets_the_description_and_developer()
    {
        $connector = new class extends TestConnector
        {
            protected $description = 'Send submissions to Acme.';
            protected $developer = 'Acme Inc';
        };

        $this->assertEquals('Send submissions to Acme.', $connector->description());
        $this->assertEquals('Acme Inc', $connector->developer());
    }

    #[Test]
    public function the_small_icon_falls_back_to_the_icon()
    {
        $connector = new class extends TestConnector
        {
            protected $icon = 'globe-arrow';
        };

        $this->assertEquals(Statamic::svg('icons/globe-arrow'), $connector->icon());
        $this->assertEquals(Statamic::svg('icons/globe-arrow'), $connector->smallIcon());
    }

    #[Test]
    public function small_icon_can_be_defined_as_a_property()
    {
        $connector = new class extends TestConnector
        {
            protected $icon = 'globe-arrow';
            protected $smallIcon = '<svg><circle r="1" /></svg>';
        };

        $this->assertEquals(Statamic::svg('icons/globe-arrow'), $connector->icon());
        $this->assertEquals('<svg><circle r="1" /></svg>', $connector->smallIcon());
    }

    #[Test]
    public function it_gets_and_sets_the_form()
    {
        $form = Form::make('contact');
        $connector = new TestMultiWordConnector;

        $this->assertSame($connector, $connector->setForm($form));
        $this->assertSame($form, $connector->form());
    }

    #[Test]
    public function it_throws_when_getting_the_form_before_it_is_set()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No form has been set on the [test_multi_word] connector.');

        (new TestMultiWordConnector)->form();
    }

    #[Test]
    public function it_has_no_connections_by_default()
    {
        $this->assertSame([], (new TestMultiWordConnector)->connections());
    }

    #[Test]
    public function it_binds_a_form_and_its_stored_connections()
    {
        $form = Form::make('contact')->connections([
            'test_multi_word' => [['id' => 'one'], ['id' => 'two']],
            'other' => [['id' => 'three']],
        ]);

        $connector = (new TestMultiWordConnector)->forForm($form);

        $this->assertSame($form, $connector->form());
        $this->assertEquals([['id' => 'one'], ['id' => 'two']], $connector->connections());
        $this->assertSame([], (new TestMultiWordConnector)->forForm(Form::make('other'))->connections());
    }

    #[Test]
    public function count_can_be_overridden()
    {
        $connector = new class extends TestConnector
        {
            public function count(): ?int
            {
                return 3;
            }
        };

        $this->assertEquals(3, $connector->count());
    }

    #[Test]
    public function it_counts_the_bound_connections_by_default()
    {
        $form = Form::make('contact')->connections([
            'test_multi_word' => [['foo' => 'bar'], ['foo' => 'baz']],
        ]);

        $this->assertEquals(2, (new TestMultiWordConnector)->forForm($form)->count());
        $this->assertEquals(1, (new TestMultiWordConnector)->setForm($form)->setConnections([['foo' => 'qux']])->count());
        $this->assertEquals(0, (new TestMultiWordConnector)->setForm($form)->count());
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
            '*.foo' => ['nullable'],
            '*.enabled' => ['nullable', 'boolean'],
            '*.conditions' => ['nullable', 'array'],
            '*.conditions.*' => ['array'],
        ], (new TestMultiWordConnector)->rules());
    }

    #[Test]
    public function it_merges_connection_rules_into_the_default_rules()
    {
        $rules = (new TestHookedConnector)->rules();

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
        ]);

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
        ]);

        $this->assertSame([['id' => 'abc', 'token' => 'secret', 'enabled' => false]], $connections);
    }

    #[Test]
    public function it_pre_processes_the_config_by_default()
    {
        $config = [['id' => 'abc', 'foo' => 'bar']];

        $this->assertEquals([
            ['id' => 'abc', 'enabled' => true, 'conditions' => [], 'foo' => 'bar'],
        ], (new TestMultiWordConnector)->preProcess($config));
    }

    #[Test]
    public function it_mints_missing_ids_when_pre_processing()
    {
        $connections = (new TestMultiWordConnector)->preProcess([['foo' => 'bar']]);

        $this->assertNotEmpty($connections[0]['id']);
        $this->assertEquals('bar', $connections[0]['foo']);
    }

    #[Test]
    public function it_pre_processes_connections_through_the_connection_hook()
    {
        $connections = (new TestHookedConnector)->preProcess([['id' => 'abc', 'token' => 'secret', 'extra' => 'dropped']]);

        $this->assertEquals([['id' => 'abc', 'token' => 'SECRET', 'enabled' => true, 'conditions' => []]], $connections);
    }

    #[Test]
    public function it_normalizes_non_list_connections()
    {
        $connector = new TestMultiWordConnector;

        $this->assertEquals([['token' => 'secret']], $connector->setConnections(['token' => 'secret'])->connections());
        $this->assertEquals([['foo' => 'bar']], $connector->setConnections([['foo' => 'bar'], 'nope', null])->connections());
        $this->assertCount(1, $connector->preProcess(['token' => 'secret']));
        $this->assertCount(1, $connector->process(['token' => 'secret']));
    }

    #[Test]
    public function it_only_makes_jobs_for_enabled_connections_whose_conditions_pass()
    {
        $form = Form::make('contact')->formFields([
            'fields' => [['handle' => 'name', 'field' => ['type' => 'text']]],
        ]);
        $submission = $form->makeSubmission()->data(['name' => 'Foo']);

        $jobs = (new TestHookedConnector)->setConnections([
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
        ]);

        $this->assertFalse($connections[0]['enabled']);
        $this->assertTrue($connections[1]['enabled']);
        $this->assertCount(1, $connections[0]['conditions']);
        $this->assertNotEmpty($connections[0]['conditions'][0]['_id']);
        $this->assertSame([], $connections[1]['conditions']);
        $this->assertEquals('baz', $connections[1]['foo']);
    }

    #[Test]
    public function it_pre_processes_connections_through_the_blueprint()
    {
        $connections = (new TestBlueprintConnector)->preProcess([
            ['id' => 'abc', 'name' => 'Foo', 'count' => '5', 'extra' => 'dropped'],
        ]);

        $this->assertEquals([
            ['name' => 'Foo', 'active' => true, 'count' => '5', 'rows' => [], 'id' => 'abc', 'enabled' => true, 'conditions' => []],
        ], $connections);
    }

    #[Test]
    public function it_processes_connections_through_the_blueprint()
    {
        $connections = (new TestBlueprintConnector)->process([
            ['id' => 'abc', 'name' => 'Foo', 'active' => false, 'count' => '5', 'extra' => 'dropped'],
        ]);

        $this->assertSame([['id' => 'abc', 'name' => 'Foo', 'active' => false, 'count' => 5]], $connections);
    }

    #[Test]
    public function it_merges_top_level_blueprint_rules_with_connection_rules()
    {
        $rules = (new TestBlueprintConnector)->rules();

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
        $rules = (new TestBlueprintConnector)->rules();

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
            protected function connectionRules(): array
            {
                return [
                    'rows.*.label' => Rule::forEach(fn ($value, $attribute, $data, $row) => ($row['strict'] ?? false) ? ['required'] : []),
                ];
            }
        };

        $rules = $connector->rules();

        $this->assertEquals(['0.rows.0.label'], Validator::make([
            ['name' => 'Foo', 'rows' => [['strict' => true, 'label' => null], ['label' => null]]],
        ], $rules)->errors()->keys());
        $this->assertInstanceOf(NestedRules::class, $rules['*.rows.*.label']);
    }

    #[Test]
    public function it_builds_publish_props_from_the_blueprint_and_bound_connections()
    {
        $form = Form::make('contact')->connections(['test_blueprint' => [['id' => 'stored']]]);

        $props = (new TestBlueprintConnector)->setForm($form)->setConnections([
            ['id' => 'one', 'name' => 'Foo', 'rows' => [['id' => 'row-1', 'label' => 'Bar']]],
            ['id' => 'two'],
            ['name' => 'No id'],
        ])->props();

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
            protected function connectionMeta(array $connection): array
            {
                return [...parent::connectionMeta($connection), 'count' => ['options' => [$connection['id']]]];
            }
        };

        $props = $connector->setConnections([['id' => 'one']])->props();

        $this->assertEquals(['options' => ['one']], $props['meta']['one']['count']);
    }

    #[Test]
    public function connection_fields_can_be_overridden_for_values_and_meta()
    {
        $connector = new class extends TestBlueprintConnector
        {
            protected function connectionFields(array $connection): Fields
            {
                return parent::connectionFields([...$connection, 'rows' => [['id' => 'added', 'label' => 'Added']]]);
            }
        };

        $this->assertEquals('added', $connector->preProcess([['id' => 'one']])[0]['rows'][0]['_id']);
        $this->assertEquals(['added'], array_keys($connector->setConnections([['id' => 'one']])->props()['meta']['one']['rows']['existing']));
    }

    #[Test]
    public function it_renders_a_vue_component()
    {
        $connector = new class extends TestConnector
        {
            public function render(): VueComponent
            {
                return VueComponent::render('acme-connector', [
                    'foo' => 'bar',
                ]);
            }
        };

        $component = $connector->render();

        $this->assertInstanceOf(VueComponent::class, $component);
        $this->assertEquals([
            'name' => 'acme-connector',
            'props' => ['foo' => 'bar'],
        ], $component->toArray());
    }
}

abstract class TestConnector extends Connector
{
    public function render(): VueComponent
    {
        return VueComponent::render('nothing');
    }

    public function blueprint(): \Statamic\Fields\Blueprint
    {
        return Blueprint::make();
    }

    protected function job(Submission $submission, array $connection): ?object
    {
        return null;
    }
}

class TestMultiWordConnector extends TestConnector
{
    public function blueprint(): \Statamic\Fields\Blueprint
    {
        return Blueprint::make()->setContents(['tabs' => ['main' => ['sections' => [['fields' => [
            ['handle' => 'foo', 'field' => ['type' => 'text']],
        ]]]]]]);
    }
}

class TestHookedConnector extends TestConnector
{
    protected function job(Submission $submission, array $connection): ?object
    {
        if ($connection['id'] === 'skip') {
            return null;
        }

        return (object) ['submission' => $submission, 'connection' => $connection];
    }

    protected function preProcessConnection(array $connection): array
    {
        return ['token' => strtoupper($connection['token'] ?? '')];
    }

    protected function connectionRules(): array
    {
        return ['token' => ['required', 'string']];
    }

    protected function processConnection(array $connection): array
    {
        return ['token' => trim($connection['token'] ?? '')];
    }
}

class TestBlueprintConnector extends TestConnector
{
    public function props(): array
    {
        return $this->blueprintProps();
    }

    public function blueprint(): \Statamic\Fields\Blueprint
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

    protected function connectionRules(): array
    {
        return [
            'name' => 'string|max:10',
            'rows.*.label' => ['required'],
        ];
    }
}
