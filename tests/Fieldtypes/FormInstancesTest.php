<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormInstancesTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    public function setUp(): void
    {
        parent::setUp();

        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true)->byDefault();

        Form::make('contact')->set('unique_instances', true)->set('unique_instances_collections', ['pages'])->set('submission_limit', 100)->save();
        Form::make('feedback')->set('unique_instances', true)->set('unique_instances_collections', ['pages'])->save();
        Form::make('survey')->set('unique_instances', true)->set('unique_instances_collections', ['events'])->save();
        Form::make('newsletter')->save();
    }

    private function field(): Field
    {
        return new Field('form_instances', ['type' => 'form_instances']);
    }

    private function fieldtype()
    {
        return $this->field()->fieldtype();
    }

    private function entry(array $data = [])
    {
        return Entry::make()->id('entry-id')->collection(tap(Collection::make('pages'))->save())->data($data);
    }

    private function withoutFormsPro(): void
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false);
        Blink::forget('forms-pro-installed');
    }

    #[Test]
    public function it_pre_processes_the_map_to_ordered_forms_and_their_config()
    {
        $value = $this->fieldtype()->preProcess([
            'feedback' => [],
            'contact' => ['submission_limit' => 5, 'connections' => []],
        ]);

        $this->assertSame(['feedback', 'contact'], $value['forms']);
        $this->assertSame([], $value['config']['feedback']);
        $this->assertSame(['submission_limit' => 5, 'connections' => []], $value['config']['contact']);
    }

    #[Test]
    public function it_pre_processes_an_empty_value()
    {
        $this->assertSame(['forms' => [], 'config' => []], $this->fieldtype()->preProcess(null));
    }

    #[Test]
    public function it_doesnt_pre_process_the_stored_config_for_users_who_cant_edit_the_form()
    {
        $this->setTestRoles(['editor' => ['access cp', 'edit feedback form']]);
        $this->actingAs(tap(User::make()->assignRole('editor'))->save());

        $value = $this->fieldtype()->preProcess([
            'contact' => ['submission_limit' => 5],
            'feedback' => ['submission_limit' => 10],
        ]);

        $this->assertSame([], $value['config']['contact']);
        $this->assertSame(['submission_limit' => 10], $value['config']['feedback']);
    }

    #[Test]
    public function it_doesnt_pre_process_the_stored_config_without_forms_pro()
    {
        $this->withoutFormsPro();

        $value = $this->fieldtype()->preProcess(['contact' => ['submission_limit' => 5]]);

        $this->assertSame(['contact'], $value['forms']);
        $this->assertSame([], $value['config']['contact']);
    }

    #[Test]
    public function it_processes_to_a_map_keyed_by_form_in_order()
    {
        $value = $this->fieldtype()->process([
            'forms' => ['feedback', 'contact'],
            'config' => [
                'contact' => ['submission_limit' => 5, 'submission_limit_period' => 'day', 'closed_message' => null],
            ],
        ]);

        $this->assertSame(['feedback', 'contact'], array_keys($value));
        $this->assertSame([], $value['feedback']);
        $this->assertEquals(['submission_limit' => 5, 'submission_limit_period' => 'day'], $value['contact']);
    }

    #[Test]
    public function it_keeps_falsy_overrides_and_drops_blank_ones()
    {
        $value = $this->fieldtype()->process([
            'forms' => ['contact'],
            'config' => ['contact' => ['require_login' => false, 'close_date' => null, 'closed_message' => '']],
        ]);

        $this->assertSame(['contact' => ['require_login' => false]], $value);
    }

    #[Test]
    public function it_keeps_an_empty_connections_override()
    {
        $value = $this->fieldtype()->process(['forms' => ['contact'], 'config' => ['contact' => ['connections' => []]]]);

        $this->assertSame(['contact' => ['connections' => []]], $value);
    }

    #[Test]
    public function it_processes_to_null_without_forms()
    {
        $this->assertNull($this->fieldtype()->process(['forms' => [], 'config' => ['contact' => ['submission_limit' => 5]]]));
    }

    #[Test]
    public function it_drops_config_for_forms_that_were_removed()
    {
        $value = $this->fieldtype()->process([
            'forms' => ['feedback'],
            'config' => ['contact' => ['submission_limit' => 5]],
        ]);

        $this->assertSame(['feedback' => []], $value);
    }

    #[Test]
    public function it_keeps_each_rows_stored_config_for_users_who_cant_edit_the_form()
    {
        Form::make('poll')->set('unique_instances', true)->set('unique_instances_collections', ['pages'])->save();

        $this->setTestRoles(['editor' => ['access cp', 'edit feedback form', 'view poll form submissions']]);
        $this->actingAs(tap(User::make()->assignRole('editor'))->save());

        $entry = $this->entry(['form_instances' => [
            'contact' => ['submission_limit' => 5],
            'feedback' => ['submission_limit' => 10],
        ]]);

        $value = $this->field()->setParent($entry)->fieldtype()->process([
            'forms' => ['contact', 'feedback', 'poll'],
            'config' => [
                'contact' => ['submission_limit' => 99],
                'feedback' => ['submission_limit' => 20],
                'poll' => ['submission_limit' => 1],
            ],
        ]);

        $this->assertEquals([
            'contact' => ['submission_limit' => 5],
            'feedback' => ['submission_limit' => 20],
            'poll' => [],
        ], $value);
    }

    #[Test]
    public function it_ignores_incoming_config_for_users_who_cant_edit_the_form_without_a_parent_entry()
    {
        $this->setTestRoles(['editor' => ['access cp', 'view contact form submissions']]);
        $this->actingAs(tap(User::make()->assignRole('editor'))->save());

        $fieldtype = (new Field('form_instances', ['type' => 'form_instances', 'collection' => 'pages']))->fieldtype();

        $this->assertSame(['contact' => []], $fieldtype->process(['forms' => ['contact'], 'config' => ['contact' => ['submission_limit' => 5]]]));
    }

    #[Test]
    public function it_doesnt_add_forms_the_user_cant_view()
    {
        $this->setTestRoles(['editor' => ['access cp', 'view contact form submissions']]);
        $this->actingAs(tap(User::make()->assignRole('editor'))->save());

        $value = $this->field()->setParent($this->entry())->fieldtype()->process(['forms' => ['contact', 'feedback']]);

        $this->assertSame(['contact' => []], $value);
    }

    #[Test]
    public function it_doesnt_add_forms_that_dont_target_the_collection()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $value = $this->field()->setParent($this->entry())->fieldtype()->process(['forms' => ['contact', 'survey', 'newsletter', 'missing']]);

        $this->assertSame(['contact' => []], $value);
    }

    #[Test]
    public function it_keeps_stored_forms_the_user_couldnt_add()
    {
        $this->setTestRoles(['editor' => ['access cp']]);
        $this->actingAs(tap(User::make()->assignRole('editor'))->save());

        $entry = $this->entry(['form_instances' => [
            'survey' => ['submission_limit' => 5],
            'feedback' => [],
        ]]);

        $value = $this->field()->setParent($entry)->fieldtype()->process(['forms' => ['survey', 'feedback']]);

        $this->assertSame(['survey' => ['submission_limit' => 5], 'feedback' => []], $value);
    }

    #[Test]
    public function it_adds_any_form_without_a_current_user()
    {
        $value = $this->field()->setParent($this->entry())->fieldtype()->process(['forms' => ['survey', 'newsletter']]);

        $this->assertSame(['survey' => [], 'newsletter' => []], $value);
    }

    #[Test]
    public function it_keeps_the_stored_config_without_forms_pro()
    {
        $this->withoutFormsPro();

        $entry = $this->entry(['form_instances' => ['contact' => ['submission_limit' => 5], 'feedback' => ['submission_limit' => 10]]]);

        $value = $this->field()->setParent($entry)->fieldtype()->process([
            'forms' => ['contact', 'newsletter'],
            'config' => ['contact' => [], 'newsletter' => ['submission_limit' => 1]],
        ]);

        $this->assertSame(['contact' => ['submission_limit' => 5], 'newsletter' => []], $value);
    }

    #[Test]
    public function it_validates_against_the_form_handles()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame(['contact'], $fieldtype->preProcessValidatable(['forms' => ['contact'], 'config' => []]));
        $this->assertSame([], $fieldtype->preProcessValidatable(null));
        $this->assertEquals(['array'], $fieldtype->rules());
    }

    #[Test]
    public function it_preloads_configure_meta_per_form()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        Form::find('contact')->connections(['webhook' => [['id' => 'abc', 'url' => 'https://example.com/webhook']]])->save();

        $preload = $this->field()
            ->setParent($this->entry())
            ->setValue(['forms' => ['contact', 'feedback'], 'config' => ['contact' => ['submission_limit' => 25]]])
            ->fieldtype()
            ->preload();

        $this->assertEquals(['contact', 'feedback'], array_keys($preload['configureMeta']));
        $this->assertEquals(['contact', 'feedback'], array_keys($preload['submissions']));

        $contact = $preload['configureMeta']['contact'];

        $this->assertEquals(100, $contact['originValues']['submission_limit']);
        $this->assertEquals('https://example.com/webhook', $contact['originValues']['connections']['webhook'][0]['url']);

        $fields = collect($contact['blueprint']['tabs'][0]['sections'][0]['fields']);
        $this->assertTrue($fields->every(fn ($field) => $field['localizable']));

        $this->assertEquals('contact', $preload['submissions']['contact']['form']);
    }

    #[Test]
    public function it_preloads_meta_for_forms_that_can_still_be_picked()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $preload = $this->field()
            ->setParent($this->entry())
            ->setValue(['forms' => [], 'config' => []])
            ->fieldtype()
            ->preload();

        $this->assertEquals(['contact', 'feedback'], array_keys($preload['configureMeta']));
        $this->assertEquals(['contact', 'feedback'], array_keys($preload['submissions']));
    }

    #[Test]
    public function it_only_preloads_configure_meta_for_users_who_can_edit_the_form()
    {
        $this->setTestRoles(['editor' => ['access cp', 'edit feedback form']]);
        $this->actingAs(tap(User::make()->assignRole('editor'))->save());

        $preload = $this->field()
            ->setParent($this->entry())
            ->setValue(['forms' => ['contact', 'feedback'], 'config' => []])
            ->fieldtype()
            ->preload();

        $this->assertEquals(['feedback'], array_keys($preload['configureMeta']));
    }

    #[Test]
    public function it_only_preloads_configure_meta_with_forms_pro_and_a_parent_entry()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $preload = fn ($parent) => $this->field()
            ->setParent($parent)
            ->setValue(['forms' => ['contact'], 'config' => []])
            ->fieldtype()
            ->preload();

        $this->assertEmpty($preload(null)['configureMeta']);
        $this->assertNotEmpty($preload($this->entry())['configureMeta']);

        $this->withoutFormsPro();

        $this->assertEmpty($preload($this->entry())['configureMeta']);
    }

    #[Test]
    public function it_only_preloads_submissions_for_users_who_can_view_submissions()
    {
        $this->setTestRoles(['viewer' => ['access cp', 'view contact form submissions']]);
        $this->actingAs(tap(User::make()->assignRole('viewer'))->save());

        $preload = $this->field()
            ->setParent($this->entry())
            ->setValue(['forms' => ['contact', 'feedback'], 'config' => []])
            ->fieldtype()
            ->preload();

        $this->assertEquals(['contact'], array_keys($preload['submissions']));
    }

    #[Test]
    public function it_only_preloads_submissions_for_unique_instances_forms_on_an_entry()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $preload = fn ($parent) => $this->field()
            ->setParent($parent)
            ->setValue(['forms' => ['contact', 'newsletter'], 'config' => []])
            ->fieldtype()
            ->preload();

        $this->assertEquals(['contact', 'feedback'], array_keys($preload($this->entry())['submissions']));
        $this->assertEmpty($preload(null)['submissions']);
    }

    #[Test]
    public function it_preloads_item_data_for_the_forms()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $preload = $this->field()->setValue(['forms' => ['feedback', 'contact'], 'config' => []])->fieldtype()->preload();

        $this->assertEquals(['feedback', 'contact'], collect($preload['data'])->pluck('id')->all());
        $this->assertEquals(['contact'], $this->fieldtype()->getItemData(['contact'])->pluck('id')->all());
    }

    #[Test]
    public function it_only_offers_forms_targeting_the_collection_that_the_user_can_view()
    {
        $this->setTestRoles(['editor' => ['access cp', 'edit contact form', 'edit survey form', 'edit newsletter form']]);
        $this->actingAs(tap(User::make()->assignRole('editor'))->save());

        $fieldtype = (new Field('form_instances', ['type' => 'form_instances', 'collection' => 'pages']))->fieldtype();

        $items = $fieldtype->getIndexItems(new Request(['paginate' => false]));

        $this->assertEquals(['contact'], $items->pluck('id')->all());
    }

    #[Test]
    public function it_offers_forms_targeting_the_parent_entrys_collection()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $fieldtype = $this->field()->setParent($this->entry())->fieldtype();

        $this->assertEquals(['collection' => 'pages'], $fieldtype->preload()['getBaseSelectionsUrlParameters']);
        $this->assertEquals(['contact', 'feedback'], $fieldtype->getIndexItems(new Request(['paginate' => false]))->pluck('id')->all());
    }

    #[Test]
    public function it_offers_forms_targeting_the_requested_collection()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        request()->merge(['collection' => 'events']);

        $items = $this->fieldtype()->getIndexItems(new Request(['paginate' => false]));

        $this->assertEquals(['survey'], $items->pluck('id')->all());
    }

    #[Test]
    public function it_offers_no_forms_without_a_collection()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $this->assertEquals([], $this->fieldtype()->getIndexItems(new Request(['paginate' => false]))->pluck('id')->all());
    }

    #[Test]
    public function it_is_not_selectable()
    {
        $this->assertFalse($this->fieldtype()->selectable());
    }

    #[Test]
    public function it_augments_to_a_collection_of_forms()
    {
        $augmented = $this->fieldtype()->augment(['feedback' => [], 'contact' => ['submission_limit' => 5]]);

        $this->assertEquals(['feedback', 'contact'], $augmented->map->handle()->all());
        $this->assertEquals(['contact'], $this->fieldtype()->shallowAugment(['contact' => []])->map(fn ($form) => $form['handle']->value())->all());
    }

    #[Test]
    public function it_queries_by_form_handles()
    {
        $this->assertEquals(['feedback', 'contact'], $this->fieldtype()->toQueryableValue(['feedback' => [], 'contact' => ['submission_limit' => 5]]));
        $this->assertNull($this->fieldtype()->toQueryableValue(null));
    }

    #[Test]
    public function it_adds_a_submissions_url_to_index_items()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $index = $this->field()->setParent($this->entry())->fieldtype()->preProcessIndex(['contact' => [], 'feedback' => [], 'newsletter' => []]);

        $this->assertEquals(['contact', 'feedback', 'newsletter'], $index->pluck('id')->all());
        $this->assertStringStartsWith(Form::find('contact')->submissionsUrl().'?filters=', $index[0]['submissions_url']);
        $this->assertArrayNotHasKey('submissions_url', $index[2]);
    }

    #[Test]
    public function it_only_adds_a_submissions_url_to_index_items_for_users_who_can_view_submissions()
    {
        $this->setTestRoles(['editor' => ['access cp', 'edit contact form'], 'viewer' => ['access cp', 'view contact form submissions']]);

        $index = fn () => $this->field()->setParent($this->entry())->fieldtype()->preProcessIndex(['contact' => [], 'feedback' => []])->keyBy('id');

        $this->actingAs(tap(User::make()->assignRole('editor'))->save());
        $this->assertArrayNotHasKey('submissions_url', $index()['contact']);

        $this->actingAs(tap(User::make()->assignRole('viewer'))->save());
        $this->assertArrayHasKey('submissions_url', $index()['contact']);
        $this->assertArrayNotHasKey('submissions_url', $index()['feedback']);
    }
}
