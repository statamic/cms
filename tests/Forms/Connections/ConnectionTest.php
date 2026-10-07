<?php

namespace Tests\Forms\Connections;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Form;
use Statamic\Forms\Connections\Connection;
use Statamic\Statamic;
use Statamic\Support\VueComponent;
use Tests\TestCase;

class ConnectionTest extends TestCase
{
    #[Test]
    public function the_handle_is_snake_cased_from_the_class_by_default()
    {
        $this->assertEquals('test_multi_word', (new TestMultiWordConnection)->handle());
    }

    #[Test]
    public function handle_can_be_defined_as_a_property()
    {
        $connection = new class extends Connection
        {
            protected static $handle = 'example';

            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('nothing');
            }
        };

        $this->assertEquals('example', $connection->handle());
    }

    #[Test]
    public function title_is_the_humanized_handle_by_default()
    {
        $this->assertEquals('Test Multi Word', (new TestMultiWordConnection)->title());
    }

    #[Test]
    public function title_can_be_defined_as_a_property()
    {
        $connection = new class extends Connection
        {
            protected static $title = 'Super Cool Example';

            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('nothing');
            }
        };

        $this->assertEquals('Super Cool Example', $connection->title());
    }

    #[Test]
    public function it_gets_the_description_and_developer()
    {
        $connection = new class extends Connection
        {
            protected $description = 'Send submissions to Acme.';
            protected $developer = 'Acme Inc';

            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('nothing');
            }
        };

        $this->assertEquals('Send submissions to Acme.', $connection->description());
        $this->assertEquals('Acme Inc', $connection->developer());
    }

    #[Test]
    public function the_small_icon_falls_back_to_the_icon()
    {
        $connection = new class extends Connection
        {
            protected $icon = 'globe-arrow';

            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('nothing');
            }
        };

        $this->assertEquals(Statamic::svg('icons/globe-arrow'), $connection->icon());
        $this->assertEquals(Statamic::svg('icons/globe-arrow'), $connection->smallIcon());
    }

    #[Test]
    public function small_icon_can_be_defined_as_a_property()
    {
        $connection = new class extends Connection
        {
            protected $icon = 'globe-arrow';
            protected $smallIcon = '<svg><circle r="1" /></svg>';

            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('nothing');
            }
        };

        $this->assertEquals(Statamic::svg('icons/globe-arrow'), $connection->icon());
        $this->assertEquals('<svg><circle r="1" /></svg>', $connection->smallIcon());
    }

    #[Test]
    public function it_counts_configured_instances_for_a_form()
    {
        $form = Form::make('contact');

        $connection = new class extends Connection
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

        $this->assertEquals(3, $connection->count($form));
    }

    #[Test]
    public function it_counts_rows_by_default()
    {
        $form = Form::make('contact')->connections([
            'test_multi_word' => [['foo' => 'bar'], ['foo' => 'baz']],
        ]);

        $this->assertEquals(2, (new TestMultiWordConnection)->count($form));
        $this->assertEquals(0, (new TestMultiWordConnection)->count(Form::make('other')));
    }

    #[Test]
    public function it_is_configured_by_default()
    {
        $this->assertTrue((new TestMultiWordConnection)->isConfigured());
    }

    #[Test]
    public function it_has_row_validation_rules_by_default()
    {
        $this->assertEquals([
            '*' => ['array'],
            '*.enabled' => ['nullable', 'boolean'],
            '*.conditions' => ['nullable', 'array'],
            '*.conditions.*' => ['array'],
        ], (new TestMultiWordConnection)->rules(Form::make('contact')));
    }

    #[Test]
    public function it_merges_row_rules_into_the_default_rules()
    {
        $rules = (new TestRowConnection)->rules(Form::make('contact'));

        $this->assertEquals([
            '*' => ['array'],
            '*.token' => ['required', 'string'],
            '*.enabled' => ['nullable', 'boolean'],
            '*.conditions' => ['nullable', 'array'],
            '*.conditions.*' => ['array'],
        ], $rules);
    }

    #[Test]
    public function it_processes_rows_by_default()
    {
        $rows = (new TestMultiWordConnection)->process([
            ['id' => 'abc', 'foo' => 'bar', 'empty' => null, 'enabled' => true, 'conditions' => []],
            ['foo' => 'baz', 'enabled' => false, 'conditions' => [
                ['_id' => 'one', 'field' => 'name', 'operator' => 'equals', 'value' => 'Foo', 'join' => 'and'],
                ['_id' => 'two', 'field' => null, 'operator' => 'equals', 'value' => 'Foo'],
            ]],
        ], Form::make('contact'));

        $this->assertSame(['id' => 'abc', 'foo' => 'bar'], $rows[0]);
        $this->assertNotEmpty($rows[1]['id']);
        $this->assertSame([
            'id' => $rows[1]['id'],
            'foo' => 'baz',
            'enabled' => false,
            'conditions' => [['field' => 'name', 'operator' => 'equals', 'value' => 'Foo', 'join' => 'and']],
        ], $rows[1]);
    }

    #[Test]
    public function it_processes_rows_through_the_row_hook()
    {
        $rows = (new TestRowConnection)->process([
            ['id' => 'abc', 'token' => ' secret ', 'extra' => 'dropped', 'enabled' => false],
        ], Form::make('contact'));

        $this->assertSame([['id' => 'abc', 'token' => 'secret', 'enabled' => false]], $rows);
    }

    #[Test]
    public function it_pre_processes_the_config_by_default()
    {
        $config = [['id' => 'abc', 'foo' => 'bar']];

        $this->assertEquals([
            ['id' => 'abc', 'enabled' => true, 'conditions' => [], 'foo' => 'bar'],
        ], (new TestMultiWordConnection)->preProcess($config, Form::make('contact')));
    }

    #[Test]
    public function it_mints_missing_ids_when_pre_processing()
    {
        $rows = (new TestMultiWordConnection)->preProcess([['foo' => 'bar']], Form::make('contact'));

        $this->assertNotEmpty($rows[0]['id']);
        $this->assertEquals('bar', $rows[0]['foo']);
    }

    #[Test]
    public function it_pre_processes_rows_through_the_row_hook()
    {
        $rows = (new TestRowConnection)->preProcess([['id' => 'abc', 'token' => 'secret', 'extra' => 'dropped']], Form::make('contact'));

        $this->assertEquals([['id' => 'abc', 'token' => 'SECRET', 'enabled' => true, 'conditions' => []]], $rows);
    }

    #[Test]
    public function it_normalizes_non_list_configs_into_rows()
    {
        $connection = new TestMultiWordConnection;

        $this->assertEquals([['token' => 'secret']], $connection->setConfig(['token' => 'secret'])->config());
        $this->assertEquals([['foo' => 'bar']], $connection->setConfig([['foo' => 'bar'], 'nope', null])->config());
        $this->assertCount(1, $connection->preProcess(['token' => 'secret'], Form::make('contact')));
        $this->assertCount(1, $connection->process(['token' => 'secret'], Form::make('contact')));
    }

    #[Test]
    public function it_returns_no_jobs_by_default()
    {
        $form = Form::make('contact');

        $jobs = (new TestMultiWordConnection)->setConfig([['id' => 'abc']])->finalized($form->makeSubmission());

        $this->assertSame([], $jobs);
    }

    #[Test]
    public function it_only_makes_jobs_for_enabled_rows_whose_conditions_pass()
    {
        $form = Form::make('contact')->formFields([
            'fields' => [['handle' => 'name', 'field' => ['type' => 'text']]],
        ]);
        $submission = $form->makeSubmission()->data(['name' => 'Foo']);

        $jobs = (new TestRowConnection)->setConfig([
            ['id' => 'one'],
            ['id' => 'two', 'enabled' => false],
            ['id' => 'three', 'conditions' => [['field' => 'name', 'operator' => 'equals', 'value' => 'Foo']]],
            ['id' => 'four', 'conditions' => [['field' => 'name', 'operator' => 'equals', 'value' => 'Bar']]],
            ['id' => 'skip'],
        ])->finalized($submission);

        $this->assertEquals(['one', 'three'], array_map(fn ($job) => $job->row['id'], $jobs));
        $this->assertSame($submission, $jobs[0]->submission);
    }

    #[Test]
    public function it_adds_enabled_and_conditions_when_pre_processing_by_default()
    {
        $rows = (new TestMultiWordConnection)->preProcess([
            ['id' => 'one', 'foo' => 'bar', 'enabled' => false, 'conditions' => [['field' => 'a', 'operator' => 'equals', 'value' => 'b', 'join' => 'and']]],
            ['id' => 'two', 'foo' => 'baz'],
        ], Form::make('contact'));

        $this->assertFalse($rows[0]['enabled']);
        $this->assertTrue($rows[1]['enabled']);
        $this->assertCount(1, $rows[0]['conditions']);
        $this->assertNotEmpty($rows[0]['conditions'][0]['_id']);
        $this->assertSame([], $rows[1]['conditions']);
        $this->assertEquals('baz', $rows[1]['foo']);
    }

    #[Test]
    public function it_renders_a_vue_component()
    {
        $form = Form::make('contact');

        $connection = new class extends Connection
        {
            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('acme-connection', [
                    'foo' => 'bar',
                ]);
            }
        };

        $component = $connection->render($form);

        $this->assertInstanceOf(VueComponent::class, $component);
        $this->assertEquals([
            'name' => 'acme-connection',
            'props' => ['foo' => 'bar'],
        ], $component->toArray());
    }
}

class TestMultiWordConnection extends Connection
{
    public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
    {
        return VueComponent::render('nothing');
    }
}

class TestRowConnection extends Connection
{
    public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
    {
        return VueComponent::render('nothing');
    }

    protected function job(Submission $submission, array $row): ?object
    {
        if ($row['id'] === 'skip') {
            return null;
        }

        return (object) ['submission' => $submission, 'row' => $row];
    }

    protected function preProcessRow(array $row, \Statamic\Contracts\Forms\Form $form): array
    {
        return ['token' => strtoupper($row['token'] ?? '')];
    }

    protected function rowRules(\Statamic\Contracts\Forms\Form $form): array
    {
        return ['token' => ['required', 'string']];
    }

    protected function processRow(array $row, \Statamic\Contracts\Forms\Form $form): array
    {
        return ['token' => trim($row['token'] ?? '')];
    }
}
