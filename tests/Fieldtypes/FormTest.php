<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormTest extends TestCase
{
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

        Form::make('contact')->save();
    }

    private function fieldtype(array $config = [])
    {
        return (new Field('rsvp_form', array_merge(['type' => 'form', 'max_items' => 1], $config)))->fieldtype();
    }

    #[Test]
    public function it_processes_to_a_plain_handle_when_nothing_is_configured()
    {
        $this->assertEquals('contact', $this->fieldtype()->process(['form' => ['contact'], 'config' => []]));
    }

    #[Test]
    public function it_processes_to_an_array_when_config_is_present()
    {
        $value = $this->fieldtype()->process(['form' => ['contact'], 'config' => [
            'submission_limit' => 5,
            'submission_limit_period' => 'day',
            'closed_message' => null,
        ]]);

        $this->assertEquals(['form' => 'contact', 'config' => [
            'submission_limit' => 5,
            'submission_limit_period' => 'day',
        ]], $value);
    }

    #[Test]
    public function it_keeps_falsy_overrides()
    {
        $value = $this->fieldtype()->process(['form' => ['contact'], 'config' => [
            'require_login' => false,
        ]]);

        $this->assertEquals(['form' => 'contact', 'config' => ['require_login' => false]], $value);
    }

    #[Test]
    public function it_drops_blank_overrides()
    {
        $value = $this->fieldtype()->process(['form' => ['contact'], 'config' => [
            'close_date' => null,
            'closed_message' => '',
        ]]);

        $this->assertEquals('contact', $value);
    }

    #[Test]
    public function it_processes_to_null_without_a_form()
    {
        $this->assertNull($this->fieldtype()->process(['form' => [], 'config' => ['submission_limit' => 5]]));
    }

    #[Test]
    public function it_processes_handles_normally_when_multiple_forms_are_allowed()
    {
        $this->assertEquals(['contact', 'other'], $this->fieldtype(['max_items' => 2])->process(['contact', 'other']));
    }

    #[Test]
    public function it_processes_to_a_plain_handle_without_forms_pro()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false);

        $value = $this->fieldtype()->process(['form' => ['contact'], 'config' => ['submission_limit' => 5]]);

        $this->assertEquals('contact', $value);
    }

    #[Test]
    public function it_validates_against_the_form_handles()
    {
        $fieldtype = $this->fieldtype();

        $this->assertEquals([], $fieldtype->preProcessValidatable(['form' => [], 'config' => ['closed_message' => 'Closed.']]));
        $this->assertEquals(['contact'], $fieldtype->preProcessValidatable(['form' => ['contact'], 'config' => []]));
        $this->assertEquals(['array', 'max:1'], $fieldtype->rules());
    }

    #[Test]
    public function it_pre_processes_both_stored_shapes()
    {
        $fromString = $this->fieldtype()->preProcess('contact');
        $fromArray = $this->fieldtype()->preProcess(['form' => 'contact', 'config' => ['submission_limit' => 5]]);

        $this->assertEquals(['contact'], $fromString['form']);
        $this->assertEquals([], $fromString['config']);

        $this->assertEquals(['contact'], $fromArray['form']);
        $this->assertEquals(['submission_limit' => 5], $fromArray['config']);
    }

    #[Test]
    public function it_pre_processes_to_handles_without_forms_pro()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false);

        $this->assertEquals(['contact'], $this->fieldtype()->preProcess(['form' => 'contact', 'config' => ['submission_limit' => 5]]));
    }

    #[Test]
    public function it_preloads_item_data_for_both_stored_shapes()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $field = new Field('rsvp_form', ['type' => 'form', 'max_items' => 1]);

        $fromString = $field->setValue('contact')->fieldtype()->preload();
        $fromArray = $field->setValue(['form' => 'contact', 'config' => ['submission_limit' => 1]])->fieldtype()->preload();

        $this->assertEquals('contact', $fromString['data'][0]['id']);
        $this->assertEquals('contact', $fromArray['data'][0]['id']);
    }

    #[Test]
    public function it_preloads_the_forms_values_as_origin_values()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->actingAs(tap(User::make()->makeSuper())->save());

        Form::find('contact')->set('unique_instances', true)->set('submission_limit', 100)->save();

        $preload = (new Field('rsvp_form', ['type' => 'form', 'max_items' => 1]))
            ->setValue(['form' => 'contact', 'config' => ['submission_limit' => 25]])
            ->fieldtype()
            ->preload();

        $configuration = $preload['configuration'];

        $this->assertEquals(100, $configuration['originValues']['submission_limit']);
        $this->assertArrayNotHasKey('values', $configuration);

        $fields = collect($configuration['blueprint']['tabs'][0]['sections'][0]['fields']);

        $this->assertTrue($fields->every(fn ($field) => $field['localizable']));
    }

    #[Test]
    public function it_augments_both_stored_shapes_to_the_form()
    {
        $this->assertEquals('contact', $this->fieldtype()->augment('contact')->handle());
        $this->assertEquals('contact', $this->fieldtype()->augment(['form' => 'contact', 'config' => []])->handle());
    }

    #[Test]
    public function it_queries_both_stored_shapes_by_handle()
    {
        $this->assertEquals('contact', $this->fieldtype()->toQueryableValue('contact'));
        $this->assertEquals('contact', $this->fieldtype()->toQueryableValue(['form' => 'contact', 'config' => []]));
    }
}
