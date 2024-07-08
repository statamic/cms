<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeleteFormTest extends TestCase
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
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test'))->save();
        $this->assertCount(1, Form::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->delete(cp_route('forms.destroy', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $this->assertCount(1, Form::all());
    }

    #[Test]
    public function it_deletes_the_form()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test'))->save();
        $this->assertCount(1, Form::all());

        $this
            ->actingAs($user)
            ->delete(cp_route('forms.destroy', $form->handle()))
            ->assertOk();

        $this->assertCount(0, Form::all());
    }
}
