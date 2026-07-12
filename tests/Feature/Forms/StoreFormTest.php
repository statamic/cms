<?php

namespace Tests\Feature\Forms;

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
    public function it_stores_a_form()
    {
        $this->assertCount(0, Form::all());

        $this
            ->actingAs($this->userWithPermission())
            ->post(cp_route('forms.store'), $this->validParams())
            ->assertJson(['redirect' => cp_route('forms.edit', 'test')])
            ->assertSessionHas('success');

        $this->assertCount(1, Form::all());
        $form = Form::all()->first();
        $this->assertEquals('test', $form->handle());
        $this->assertEquals('Test Form', $form->title());
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
