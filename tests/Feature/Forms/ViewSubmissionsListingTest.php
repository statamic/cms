<?php

namespace Tests\Feature\Forms;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\User;
use Tests\Factories\EntryFactory;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewSubmissionsListingTest extends TestCase
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
    public function it_shows_the_listing_with_the_view_form_submissions_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.submissions.index', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('forms/submissions/Index'));
    }

    #[Test]
    public function it_shows_the_listing_with_the_per_form_view_submissions_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.submissions.index', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('forms/submissions/Index'));
    }

    #[Test]
    public function it_denies_access_with_only_the_edit_forms_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('forms.submissions.index', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_does_not_eager_load_actions_in_submissions_listing()
    {
        $user = tap(User::make()->makeSuper())->save();
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['foo' => 'bar'])->save();

        $this
            ->actingAs($user)
            ->getJson(cp_route('forms.submissions.index', $form->handle()))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonMissingPath('data.0.actions');
    }

    #[Test]
    public function it_includes_the_entry_column_when_unique_instances_is_enabled()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $entry = (new EntryFactory)->collection('events')->id('event-1')->slug('event-one')->data(['title' => 'Event One'])->create();

        $user = tap(User::make()->makeSuper())->save();
        $form = tap(Form::make('test')->set('unique_instances', true))->save();
        FormSubmission::make()->form($form)->data(['entry' => 'event-1'])->save();

        $response = $this
            ->actingAs($user)
            ->getJson(cp_route('forms.submissions.index', $form->handle()))
            ->assertSuccessful()
            ->assertJsonPath('data.0.entry.0.id', 'event-1')
            ->assertJsonPath('data.0.entry.0.title', 'Event One')
            ->assertJsonPath('data.0.entry.0.status', 'published')
            ->assertJsonPath('data.0.entry.0.edit_url', $entry->editUrl());

        $this->assertContains('entry', collect($response->json('meta.columns'))->pluck('field')->all());
    }

    #[Test]
    public function it_doesnt_include_the_entry_column_when_unique_instances_is_disabled()
    {
        $user = tap(User::make()->makeSuper())->save();
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['foo' => 'bar'])->save();

        $response = $this
            ->actingAs($user)
            ->getJson(cp_route('forms.submissions.index', $form->handle()))
            ->assertSuccessful();

        $this->assertNotContains('entry', collect($response->json('meta.columns'))->pluck('field')->all());
    }

    #[Test]
    public function it_filters_submissions_by_entry()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        (new EntryFactory)->collection('events')->id('event-1')->slug('event-one')->create();
        (new EntryFactory)->collection('events')->id('event-2')->slug('event-two')->create();

        $user = tap(User::make()->makeSuper())->save();
        $form = tap(Form::make('test')->set('unique_instances', true))->save();
        FormSubmission::make()->form($form)->data(['entry' => 'event-1'])->save();
        FormSubmission::make()->form($form)->data(['entry' => 'event-2'])->save();

        $filters = base64_encode(json_encode(['submission_entry' => ['entry' => 'event-2']]));

        $this
            ->actingAs($user)
            ->getJson(cp_route('forms.submissions.index', $form->handle()).'?filters='.$filters)
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.entry.0.id', 'event-2');
    }
}
