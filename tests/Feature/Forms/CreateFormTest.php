<?php

namespace Tests\Feature\Forms;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CreateFormTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_shows_the_create_page_if_you_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.create'))
            ->assertSuccessful();
    }

    #[Test]
    public function it_shows_the_create_page_with_the_create_forms_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'create forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.create'))
            ->assertSuccessful();
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('forms.create'))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_denies_access_with_only_the_edit_forms_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('forms.create'))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_allows_the_first_form_without_statamic_pro_or_forms_pro()
    {
        config(['statamic.editions.pro' => false]);
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false);

        $this
            ->actingAs($this->userWithPermission())
            ->get(cp_route('forms.create'))
            ->assertSuccessful();
    }

    #[Test]
    public function it_denies_access_to_a_second_form_without_statamic_pro_or_forms_pro()
    {
        config(['statamic.editions.pro' => false]);
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false);

        Form::make('contact')->save();

        $this
            ->from('/original')
            ->actingAs($this->userWithPermission())
            ->get(cp_route('forms.create'))
            ->assertRedirect('/original')
            ->assertSessionHas('error', 'Statamic Pro is required.');
    }

    #[Test]
    public function it_allows_a_second_form_with_statamic_pro()
    {
        config(['statamic.editions.pro' => true]);

        Form::make('contact')->save();

        $this
            ->actingAs($this->userWithPermission())
            ->get(cp_route('forms.create'))
            ->assertSuccessful();
    }

    #[Test]
    public function it_allows_a_second_form_with_forms_pro_installed()
    {
        config(['statamic.editions.pro' => false]);
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        Form::make('contact')->save();

        $this
            ->actingAs($this->userWithPermission())
            ->get(cp_route('forms.create'))
            ->assertSuccessful();
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);

        return tap(User::make()->assignRole('test'))->save();
    }
}
