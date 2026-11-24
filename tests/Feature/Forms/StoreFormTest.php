<?php

namespace Tests\Feature\Forms;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreFormTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->post(cp_route('forms.store'))
            ->assertRedirect('/original')
            ->assertSessionHas('error', 'You are not authorized to create forms.');
    }

    #[Test]
    public function it_denies_access_with_only_the_edit_forms_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->post(cp_route('forms.store'))
            ->assertRedirect('/original')
            ->assertSessionHas('error', 'You are not authorized to create forms.');
    }

    #[Test]
    public function it_stores_a_form()
    {
        $this->assertCount(0, Form::all());

        $this
            ->actingAs($this->userWithPermission())
            ->post(cp_route('forms.store'), $this->validParams())
            ->assertJson(['redirect' => cp_route('forms.show', 'test')])
            ->assertSessionHas('success');

        $this->assertCount(1, Form::all());
        $form = Form::all()->first();
        $this->assertEquals('test', $form->handle());
        $this->assertEquals('Test Form', $form->title());
    }

    #[Test]
    public function it_stores_a_form_with_the_create_forms_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'create forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->post(cp_route('forms.store'), $this->validParams())
            ->assertJson(['redirect' => cp_route('forms.show', 'test')])
            ->assertSessionHas('success');

        $this->assertCount(1, Form::all());
    }

    #[Test]
    public function title_is_required()
    {
        $this->assertCount(0, Form::all());

        $this
            ->from('/original')
            ->actingAs($this->userWithPermission())
            ->post(cp_route('forms.store'), $this->validParams([
                'title' => '',
            ]))
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');

        $this->assertCount(0, Form::all());
    }

    #[Test]
    public function handle_must_be_alpha_dash()
    {
        $this->assertCount(0, Form::all());

        $this
            ->from('/original')
            ->actingAs($this->userWithPermission())
            ->post(cp_route('forms.store'), $this->validParams([
                'handle' => 'there are spaces in here',
            ]))
            ->assertRedirect('/original')
            ->assertSessionHasErrors('handle');

        $this->assertCount(0, Form::all());
    }

    #[Test]
    public function it_stores_the_first_form_without_statamic_pro_or_forms_pro()
    {
        config(['statamic.editions.pro' => false]);
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false);

        $this
            ->actingAs($this->userWithPermission())
            ->post(cp_route('forms.store'), $this->validParams())
            ->assertJson(['redirect' => cp_route('forms.show', 'test')]);

        $this->assertCount(1, Form::all());
    }

    #[Test]
    public function it_denies_storing_a_second_form_without_statamic_pro_or_forms_pro()
    {
        config(['statamic.editions.pro' => false]);
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false);

        Form::make('contact')->save();

        $this
            ->from('/original')
            ->actingAs($this->userWithPermission())
            ->post(cp_route('forms.store'), $this->validParams())
            ->assertRedirect('/original')
            ->assertSessionHas('error', 'Statamic Pro is required.');

        $this->assertCount(1, Form::all());
    }

    #[Test]
    public function it_stores_a_second_form_with_statamic_pro()
    {
        config(['statamic.editions.pro' => true]);

        Form::make('contact')->save();

        $this
            ->actingAs($this->userWithPermission())
            ->post(cp_route('forms.store'), $this->validParams())
            ->assertJson(['redirect' => cp_route('forms.show', 'test')]);

        $this->assertCount(2, Form::all());
    }

    #[Test]
    public function it_stores_a_second_form_with_forms_pro_installed()
    {
        config(['statamic.editions.pro' => false]);
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        Form::make('contact')->save();

        $this
            ->actingAs($this->userWithPermission())
            ->post(cp_route('forms.store'), $this->validParams())
            ->assertJson(['redirect' => cp_route('forms.show', 'test')]);

        $this->assertCount(2, Form::all());
    }

    #[Test]
    public function handle_is_a_slugified_title_if_not_provided()
    {
        $this->assertCount(0, Form::all());

        $this
            ->from('/original')
            ->actingAs($this->userWithPermission())
            ->post(cp_route('forms.store'), $this->validParams([
                'title' => 'An Example Form',
                'handle' => '',
            ]));

        $this->assertCount(1, Form::all());
        $form = Form::all()->first();
        $this->assertEquals('an_example_form', $form->handle());
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'Test Form',
            'handle' => 'test',
        ], $overrides);
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
}
