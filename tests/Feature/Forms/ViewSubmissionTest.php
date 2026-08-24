<?php

namespace Tests\Feature\Forms;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\User;
use Tests\Factories\EntryFactory;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewSubmissionTest extends TestCase
{
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
    public function it_shows_a_submission()
    {
        $user = tap(User::make()->makeSuper())->save();
        $form = tap(Form::make('test'))->save();
        $submission = tap(FormSubmission::make()->form($form)->data(['foo' => 'bar']))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.submissions.show', [$form->handle(), $submission->id()]))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Submission')
                ->where('entry', null));
    }

    #[Test]
    public function it_shows_the_associated_entry()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $entry = (new EntryFactory)->collection('events')->id('event-1')->slug('event-one')->data(['title' => 'Event One'])->create();

        $user = tap(User::make()->makeSuper())->save();
        $form = tap(Form::make('test')->set('unique_instances', true))->save();
        $submission = tap(FormSubmission::make()->form($form)->data(['entry' => 'event-1']))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.submissions.show', [$form->handle(), $submission->id()]))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Submission')
                ->where('entry.id', 'event-1')
                ->where('entry.title', 'Event One')
                ->where('entry.edit_url', $entry->editUrl())
                ->where('entry.status', 'published'));
    }

    #[Test]
    public function it_doesnt_show_the_entry_without_forms_pro()
    {
        (new EntryFactory)->collection('events')->id('event-1')->slug('event-one')->create();

        $user = tap(User::make()->makeSuper())->save();
        $form = tap(Form::make('test')->set('unique_instances', true))->save();
        $submission = tap(FormSubmission::make()->form($form)->data(['entry' => 'event-1']))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.submissions.show', [$form->handle(), $submission->id()]))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Submission')
                ->where('entry', null));
    }

    #[Test]
    public function it_shows_a_submission_as_json()
    {
        $user = tap(User::make()->makeSuper())->save();
        $form = tap(Form::make('test'))->save();
        $submission = tap(FormSubmission::make()->form($form)->data(['foo' => 'bar']))->save();

        $this
            ->actingAs($user)
            ->getJson(cp_route('forms.submissions.show', [$form->handle(), $submission->id()]))
            ->assertSuccessful()
            ->assertJsonStructure(['id', 'status', 'date', 'blueprint', 'values', 'meta'])
            ->assertJsonPath('id', $submission->id())
            ->assertJsonPath('status', 'finalized');
    }
}
