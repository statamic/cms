<?php

namespace Tests\Forms;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Form;
use Statamic\Fieldtypes\FormInstances;
use Statamic\Forms\Instance;
use Statamic\Support\Arr;
use Tests\Factories\EntryFactory;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class InstanceTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    protected function setUp(): void
    {
        parent::setUp();

        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true)->byDefault();
    }

    private function makeForm(array $data = [])
    {
        return tap(Form::make('contact')->data(array_merge(['unique_instances' => true, 'unique_instances_collections' => ['events']], $data)))->save();
    }

    private function makeEntry(string $id, array $formInstances): void
    {
        Blueprint::make('event')->setNamespace('collections.events')->setContents(['fields' => [
            ['handle' => 'title', 'field' => ['type' => 'text']],
            ['handle' => 'form_instances', 'field' => ['type' => 'form_instances']],
        ]])->save();

        (new EntryFactory)->collection('events')->id($id)->slug($id)->data(['form_instances' => $formInstances])->create();
    }

    #[Test]
    public function a_form_makes_instances()
    {
        $form = $this->makeForm();

        $instance = $form->instance('event-1');

        $this->assertInstanceOf(Instance::class, $instance);
        $this->assertEquals($form->handle(), $instance->form()->handle());
        $this->assertEquals('event-1', $instance->entry());

        $this->assertNull($form->instance()->entry());
    }

    #[Test]
    public function the_entry_is_dropped_without_unique_instances()
    {
        $form = $this->makeForm(['unique_instances' => false]);

        $this->assertNull($form->instance('event-1')->entry());
    }

    #[Test]
    public function the_entry_is_dropped_without_forms_pro()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false);

        $form = $this->makeForm();

        $this->assertNull($form->instance('event-1')->entry());
    }

    #[Test]
    public function the_default_instance_reads_the_forms_config()
    {
        $form = $this->makeForm(['submission_limit' => 5]);

        $this->assertEquals(5, $form->instance()->config('submission_limit'));
        $this->assertNull($form->instance()->config('close_date'));
    }

    #[Test]
    public function an_entry_instance_prefers_the_entrys_overrides()
    {
        $form = $this->makeForm(['submission_limit' => 5, 'closed_message' => 'Closed.']);

        $this->makeEntry('event-1', ['contact' => ['submission_limit' => 1]]);

        $instance = $form->instance('event-1');

        $this->assertEquals(1, $instance->config('submission_limit'));
        $this->assertEquals('Closed.', $instance->config('closed_message'));
    }

    #[Test]
    public function a_localization_uses_its_roots_forms_and_overrides()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
            'fr_ca' => ['url' => '/fr-ca/', 'locale' => 'fr_CA'],
        ]);

        $this->makeForm(['submission_limit' => 5]);

        $this->makeEntry('event-1', ['contact' => ['submission_limit' => 1]]);

        Collection::findByHandle('events')->sites(['en', 'fr', 'fr_ca'])->save();

        tap(Entry::find('event-1')->makeLocalization('fr')->id('event-1-fr'))->save();
        tap(Entry::find('event-1-fr')->makeLocalization('fr_ca')->id('event-1-fr-ca'))->save();

        $this->assertEquals(['contact' => ['submission_limit' => 1]], FormInstances::forEntry(Entry::find('event-1-fr')));
        $this->assertEquals(['contact' => ['submission_limit' => 1]], FormInstances::forEntry(Entry::find('event-1-fr-ca')));
    }

    #[Test]
    public function a_localizations_own_form_instances_value_is_ignored()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        $this->makeForm(['submission_limit' => 5]);
        Form::make('feedback')->data(['unique_instances' => true, 'unique_instances_collections' => ['events']])->save();

        $this->makeEntry('event-1', ['contact' => ['submission_limit' => 1]]);

        Collection::findByHandle('events')->sites(['en', 'fr'])->save();

        tap(Entry::find('event-1')->makeLocalization('fr')->id('event-1-fr')->data(['form_instances' => [
            'contact' => ['submission_limit' => 10],
            'feedback' => [],
        ]]))->save();

        $this->assertEquals(['contact' => ['submission_limit' => 1]], FormInstances::forEntry(Entry::find('event-1-fr')));
    }

    #[Test]
    public function overrides_in_a_nested_form_instances_field_are_ignored()
    {
        $form = $this->makeForm(['submission_limit' => 5]);

        Blueprint::make('event')->setNamespace('collections.events')->setContents(['fields' => [
            ['handle' => 'blocks', 'field' => ['type' => 'replicator', 'sets' => [
                'rsvp' => ['fields' => [
                    ['handle' => 'form_instances', 'field' => ['type' => 'form_instances']],
                ]],
            ]]],
        ]])->save();

        (new EntryFactory)->collection('events')->id('event-1')->slug('event-1')->data(['blocks' => [
            ['type' => 'rsvp', 'form_instances' => ['contact' => ['submission_limit' => 1]]],
        ]])->create();

        $this->assertEquals(5, $form->instance('event-1')->config('submission_limit'));
    }

    #[Test]
    public function overrides_are_only_read_from_the_form_instances_handle()
    {
        $form = $this->makeForm(['submission_limit' => 5]);

        Blueprint::make('event')->setNamespace('collections.events')->setContents(['fields' => [
            ['handle' => 'other', 'field' => ['type' => 'form_instances']],
        ]])->save();

        (new EntryFactory)->collection('events')->id('event-1')->slug('event-1')->data([
            'other' => ['contact' => ['submission_limit' => 1]],
        ])->create();

        $this->assertEquals(5, $form->instance('event-1')->config('submission_limit'));
    }

    #[Test]
    public function a_form_instances_handle_with_another_type_is_ignored()
    {
        $form = $this->makeForm(['submission_limit' => 5]);

        Blueprint::make('event')->setNamespace('collections.events')->setContents(['fields' => [
            ['handle' => 'form_instances', 'field' => ['type' => 'yaml']],
        ]])->save();

        (new EntryFactory)->collection('events')->id('event-1')->slug('event-1')->data([
            'form_instances' => ['contact' => ['submission_limit' => 1]],
        ])->create();

        $this->assertEquals(5, $form->instance('event-1')->config('submission_limit'));
    }

    #[Test]
    public function overrides_are_ignored_once_the_form_stops_targeting_the_entrys_collection()
    {
        $form = $this->makeForm(['submission_limit' => 5]);

        $this->makeEntry('event-1', ['contact' => ['submission_limit' => 1]]);

        $form->set('unique_instances_collections', ['pages'])->save();

        $this->assertEquals(5, $form->instance('event-1')->config('submission_limit'));
    }

    #[Test]
    public function each_form_in_the_field_has_its_own_overrides()
    {
        $form = $this->makeForm(['submission_limit' => 5]);
        $feedback = tap(Form::make('feedback')->data(['unique_instances' => true, 'unique_instances_collections' => ['events'], 'submission_limit' => 5]))->save();

        $this->makeEntry('event-1', [
            'contact' => ['submission_limit' => 1],
            'feedback' => ['close_date' => '2020-01-01 09:00'],
        ]);

        $this->assertEquals(1, $form->instance('event-1')->config('submission_limit'));
        $this->assertNull($form->instance('event-1')->config('close_date'));
        $this->assertEquals('open', $form->instance('event-1')->status());

        $this->assertEquals(5, $feedback->instance('event-1')->config('submission_limit'));
        $this->assertEquals('closed', $feedback->instance('event-1')->status());
    }

    #[Test]
    public function overrides_are_not_read_from_a_form_field()
    {
        $form = $this->makeForm(['submission_limit' => 5]);

        Blueprint::make('event')->setNamespace('collections.events')->setContents(['fields' => [
            ['handle' => 'rsvp_form', 'field' => ['type' => 'form', 'max_items' => 1]],
        ]])->save();

        (new EntryFactory)->collection('events')->id('event-1')->slug('event-1')->data([
            'rsvp_form' => ['form' => 'contact', 'config' => ['submission_limit' => 1]],
        ])->create();

        $this->assertEquals(5, $form->instance('event-1')->config('submission_limit'));
    }

    #[Test]
    public function overrides_from_an_entry_using_a_different_form_are_ignored()
    {
        $form = $this->makeForm(['submission_limit' => 5]);

        $this->makeEntry('event-1', ['another_form' => ['submission_limit' => 1]]);

        $this->assertEquals(5, $form->instance('event-1')->config('submission_limit'));
    }

    #[Test]
    public function an_unconfigured_entry_falls_back_to_the_forms_config()
    {
        $form = $this->makeForm(['submission_limit' => 5]);

        $this->makeEntry('event-1', ['contact' => []]);

        $this->assertEquals(5, $form->instance('event-1')->config('submission_limit'));
    }

    #[Test]
    public function an_entry_instance_prefers_the_entrys_connection_overrides()
    {
        $form = tap($this->makeForm()->connections([
            'webhook' => [['url' => 'https://example.com/form']],
        ]))->save();

        $this->makeEntry('event-1', ['contact' => [
            'connections' => ['webhook' => [['url' => 'https://example.com/entry']]],
        ]]);

        $this->assertEquals(
            ['webhook' => [['url' => 'https://example.com/entry']]],
            $form->instance('event-1')->connections()->all()
        );
    }

    #[Test]
    public function an_unconfigured_entry_falls_back_to_the_forms_connections()
    {
        $form = tap($this->makeForm()->connections([
            'webhook' => [['url' => 'https://example.com/form']],
        ]))->save();

        $this->makeEntry('event-1', ['contact' => []]);

        $this->assertEquals(
            ['webhook' => [['url' => 'https://example.com/form']]],
            $form->instance('event-1')->connections()->map(fn ($configs) => array_map(fn ($config) => Arr::except($config, 'id'), $configs))->all()
        );
    }

    #[Test]
    public function an_entry_can_override_the_connections_to_none()
    {
        $form = tap($this->makeForm()->connections([
            'webhook' => [['url' => 'https://example.com/form']],
        ]))->save();

        $this->makeEntry('event-1', ['contact' => ['connections' => []]]);

        $this->assertEquals([], $form->instance('event-1')->connections()->all());
    }

    #[Test]
    public function the_form_delegates_to_its_default_instance()
    {
        $form = $this->makeForm(['close_date' => '2020-01-01 09:00']);

        $this->assertEquals('closed', $form->status());
        $this->assertTrue($form->restricted());
        $this->assertEquals('This form is no longer accepting submissions.', $form->restrictionMessage());
    }
}
