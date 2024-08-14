<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditFormTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_shows_the_edit_page_if_you_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertSuccessful()
            ->assertViewHas('form', $form);
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function fields_can_be_added()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        Form::appendConfigFields('*', 'Fields', [
            'a' => ['type' => 'text', 'display' => 'First injected into fields section'],
            'b' => ['type' => 'text', 'display' => 'Second injected into fields section'],
        ]);
        Form::appendConfigFields('*', 'Additional Section', [
            'c' => ['type' => 'text', 'display' => 'First injected into additional section'],
            'd' => ['type' => 'text', 'display' => 'Second injected into additional section'],
        ]);

        $this
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertSuccessful()
            ->assertViewHas('form', $form)
            ->assertSeeInOrder([
                'Title',
                'Blueprint',
                'Honeypot',
                'First injected into fields section',
                'Second injected into fields section',
                'Store Submissions',
                'Additional Section',
                'First injected into additional section',
                'Second injected into additional section',
            ]);
    }
}
