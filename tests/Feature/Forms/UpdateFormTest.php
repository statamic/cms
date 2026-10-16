<?php

namespace Tests\Feature\Forms;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Fieldset;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateFormTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    protected function setUp(): void
    {
        parent::setUp();

        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false)->byDefault();
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $form = tap(Form::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->update($form)
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_updates_a_form()
    {
        $form = tap(Form::make('test')->title('Original title'))->save();
        $this->assertCount(1, Form::all());
        $this->assertEquals('Original title', $form->title());
        $this->assertEquals('honeypot', $form->honeypot());
        $this->assertTrue($form->store());

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, [
                'title' => 'Updated title',
                'honeypot' => 'color',
                'store' => false,
            ])
            ->assertOk();

        $this->assertCount(1, Form::all());
        $updated = Form::all()->first();
        $this->assertEquals('Updated title', $updated->title());
        $this->assertEquals('color', $updated->honeypot());
        $this->assertFalse($updated->store());
    }

    #[Test]
    public function it_updates_restrictions()
    {
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, [
                'submission_limit' => 5,
                'submission_limit_period' => 'day',
                'closed_message' => 'Sorry, we are isClosed.',
                'require_login' => true,
            ])
            ->assertOk();

        $updated = Form::all()->first();
        $this->assertEquals(5, $updated->get('submission_limit'));
        $this->assertEquals('day', $updated->get('submission_limit_period'));
        $this->assertEquals('Sorry, we are isClosed.', $updated->get('closed_message'));
        $this->assertTrue($updated->get('require_login'));
    }

    #[Test]
    public function it_updates_data()
    {
        $form = tap(Form::make('test'))->save();
        $this->assertNull($form->email());

        Form::appendConfigFields('*', 'Test Config', [
            'another_config' => [
                'handle' => 'another_config',
                'field' => [
                    'type' => 'text',
                ],
            ],
            'some_config' => [
                'handle' => 'some_config',
                'field' => [
                    'type' => 'text',
                ],
            ],
        ]);

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['some_config' => 'foo', 'another_config' => 'bar'])
            ->assertOk();

        $updated = Form::all()->first();
        $this->assertEquals([
            'another_config' => 'bar',
            'some_config' => 'foo',
        ], $updated->data()->all());
    }

    #[Test]
    public function it_cant_enable_unique_instances_when_the_form_has_an_entry_field()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'entry', 'field' => ['type' => 'short_answer']],
            ],
        ]))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['unique_instances' => true, 'unique_instances_collections' => ['events']])
            ->assertSessionHasErrors(['unique_instances' => __('statamic::validation.form_unique_instances_entry_field')]);

        $this->assertFalse(Form::find('test')->hasUniqueInstances());
    }

    #[Test]
    public function it_cant_enable_unique_instances_when_the_form_imports_an_entry_field()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $fieldset = Fieldset::make('contest')->setContents([
            'fields' => [
                ['handle' => 'try', 'field' => ['type' => 'short_answer']],
            ],
        ]);
        Fieldset::shouldReceive('find')->with('contest')->andReturn($fieldset);

        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['import' => 'contest', 'prefix' => 'en'],
            ],
        ]))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['unique_instances' => true, 'unique_instances_collections' => ['events']])
            ->assertSessionHasErrors(['unique_instances' => __('statamic::validation.form_unique_instances_entry_field')]);

        $this->assertFalse(Form::find('test')->hasUniqueInstances());
    }

    #[Test]
    public function it_reports_the_entry_field_and_missing_collections_together()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'entry', 'field' => ['type' => 'short_answer']],
            ],
        ]))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['unique_instances' => true, 'unique_instances_collections' => []])
            ->assertSessionHasErrors([
                'unique_instances' => __('statamic::validation.form_unique_instances_entry_field'),
                'unique_instances_collections' => 'This field is required when Unique Instances is true.',
            ]);

        $this->assertFalse(Form::find('test')->hasUniqueInstances());
    }

    #[Test]
    public function it_can_enable_unique_instances_when_the_form_has_no_entry_field()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'short_answer']],
            ],
        ]))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['unique_instances' => true, 'unique_instances_collections' => ['events']])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Form::find('test')->hasUniqueInstances());
    }

    #[Test]
    public function it_requires_collections_when_enabling_unique_instances()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['unique_instances' => true, 'unique_instances_collections' => []])
            ->assertSessionHasErrors(['unique_instances_collections' => 'This field is required when Unique Instances is true.']);

        $this->assertFalse(Form::find('test')->hasUniqueInstances());
    }

    #[Test]
    public function it_enables_unique_instances_with_a_collection()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['unique_instances' => true, 'unique_instances_collections' => ['events']])
            ->assertSessionHasNoErrors();

        $form = Form::find('test');
        $this->assertTrue($form->hasUniqueInstances());
        $this->assertEquals(['events'], $form->uniqueInstancesCollections());
    }

    #[Test]
    public function it_doesnt_require_collections_when_unique_instances_is_off()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['unique_instances' => false, 'unique_instances_collections' => []])
            ->assertSessionHasNoErrors();

        $this->assertFalse(Form::find('test')->hasUniqueInstances());
    }

    private function userWithoutPermission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        return tap(User::make()->assignRole('test'))->save();
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);

        return tap(User::make()->assignRole('test'))->save();
    }

    private function update($form, $params = [])
    {
        $params = array_merge([
            'title' => 'Updated title',
        ], $params);

        return $this->patch(cp_route('forms.update', $form->handle()), $params);
    }
}
