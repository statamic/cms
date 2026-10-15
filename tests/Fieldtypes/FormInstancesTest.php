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

        Form::make('contact')->set('unique_instances', true)->set('submission_limit', 100)->save();
        Form::make('feedback')->set('unique_instances', true)->save();
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
        $this->setTestRoles(['editor' => ['access cp', 'edit feedback form']]);
        $this->actingAs(tap(User::make()->assignRole('editor'))->save());

        $entry = $this->entry(['form_instances' => [
            'contact' => ['submission_limit' => 5],
            'feedback' => ['submission_limit' => 10],
        ]]);

        $value = $this->field()->setParent($entry)->fieldtype()->process([
            'forms' => ['contact', 'feedback', 'newsletter'],
            'config' => [
                'contact' => ['submission_limit' => 99],
                'feedback' => ['submission_limit' => 20],
                'newsletter' => ['submission_limit' => 1],
            ],
        ]);

        $this->assertEquals([
            'contact' => ['submission_limit' => 5],
            'feedback' => ['submission_limit' => 20],
            'newsletter' => [],
        ], $value);
    }

    #[Test]
    public function it_ignores_incoming_config_for_users_who_cant_edit_the_form_without_a_parent_entry()
    {
        $this->setTestRoles(['editor' => ['access cp']]);
        $this->actingAs(tap(User::make()->assignRole('editor'))->save());

        $this->assertSame(['contact' => []], $this->fieldtype()->process(['forms' => ['contact'], 'config' => ['contact' => ['submission_limit' => 5]]]));
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
    public function it_preloads_item_data_for_the_forms()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $preload = $this->field()->setValue(['forms' => ['feedback', 'contact'], 'config' => []])->fieldtype()->preload();

        $this->assertEquals(['feedback', 'contact'], collect($preload['data'])->pluck('id')->all());
        $this->assertEquals(['contact'], $this->fieldtype()->getItemData(['contact'])->pluck('id')->all());
    }

    #[Test]
    public function it_only_offers_unique_instances_forms_the_user_can_view()
    {
        $this->setTestRoles(['editor' => ['access cp', 'edit contact form', 'edit newsletter form']]);
        $this->actingAs(tap(User::make()->assignRole('editor'))->save());

        $items = $this->fieldtype()->getIndexItems(new Request(['paginate' => false]));

        $this->assertEquals(['contact'], $items->pluck('id')->all());
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

        $index = $this->field()->setParent($this->entry())->fieldtype()->preProcessIndex(['contact' => [], 'feedback' => []]);

        $this->assertEquals(['contact', 'feedback'], $index->pluck('id')->all());
        $this->assertStringStartsWith(Form::find('contact')->submissionsUrl().'?filters=', $index[0]['submissions_url']);
    }
}
