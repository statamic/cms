<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewSubmissionsListingTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
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
}
