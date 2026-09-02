<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewFormListingTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_shows_a_list_of_forms()
    {
        Form::make('foo')->title('Foo')->save();
        Form::make('bar')->title('Bar')->save();

        $user = tap(User::make()->makeSuper())->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.index'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Index')
                ->has('forms', 2)
                ->where('forms.0.id', 'bar')
                ->where('forms.1.id', 'foo')
            );
    }

    #[Test]
    public function it_shows_no_results_when_there_are_no_forms()
    {
        $user = tap(User::make()->makeSuper())->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.index'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Index')
                ->has('forms', 0)
            );
    }

    #[Test]
    public function it_filters_out_forms_the_user_cannot_access()
    {
        Form::make('foo')->title('Foo')->save();
        Form::make('bar')->title('Bar')->save();

        $this->setTestRoles(['test' => ['access cp', 'edit bar form']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.index'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Index')
                ->has('forms', 1)
                ->where('forms.0.id', 'bar')
            );
    }

    #[Test]
    public function it_doesnt_filter_out_forms_if_they_have_permission_to_configure()
    {
        Form::make('foo')->title('Foo')->save();
        Form::make('bar')->title('Bar')->save();

        $this->setTestRoles(['test' => ['access cp', 'configure forms', 'edit bar form']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.index'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Index')
                ->has('forms', 2)
            );
    }

    #[Test]
    public function it_denies_access_when_there_are_no_permitted_forms()
    {
        Form::make('foo')->title('Foo')->save();
        Form::make('bar')->title('Bar')->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->from('/cp/original')
            ->actingAs($user)
            ->get(cp_route('forms.index'))
            ->assertRedirect('/cp/original');
    }

    #[Test]
    public function it_shows_the_submissions_column_when_the_user_can_view_submissions()
    {
        $this->setTestRoles(['test' => ['access cp', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        Form::make('test')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.index'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Index')
                ->count('initialColumns', 2)
            );
    }

    #[Test]
    public function it_hides_the_submissions_column_when_the_user_cannot_view_any_submissions()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        Form::make('test')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.index'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Index')
                ->count('initialColumns', 1)
            );
    }

    #[Test]
    public function it_includes_the_submission_count_when_the_user_can_view_submissions()
    {
        $this->setTestRoles(['test' => ['access cp', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();
        $this->makeSubmission($form);
        $this->makeSubmission($form);
        $this->makeSubmission($form);

        $this
            ->actingAs($user)
            ->get(cp_route('forms.index'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Index')
                ->where('forms.0.can_view_submissions', true)
                ->where('forms.0.submissions', 3)
            );
    }

    #[Test]
    public function it_only_counts_submissions_in_the_selected_site()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);
        Site::setSelected('fr');

        $this->setTestRoles(['test' => ['access cp', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();
        $this->makeSubmission($form, 'en');
        $this->makeSubmission($form, 'en');
        $this->makeSubmission($form, 'fr');

        $this
            ->actingAs($user)
            ->get(cp_route('forms.index'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Index')
                ->where('forms.0.submissions', 1)
            );
    }

    #[Test]
    public function it_excludes_the_submission_count_when_the_user_cannot_view_submissions()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();
        $this->makeSubmission($form);
        $this->makeSubmission($form);
        $this->makeSubmission($form);

        $this
            ->actingAs($user)
            ->get(cp_route('forms.index'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Index')
                ->where('forms.0.can_view_submissions', false)
                ->where('forms.0.submissions', null)
            );
    }

    private function makeSubmission($form, $site = null)
    {
        $submission = $form->makeSubmission()->site($site);
        $submission->data(['name' => 'John Doe']);
        $submission->save();
    }
}
