<?php

namespace Tests\Forms;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Form;
use Statamic\Jobs\DeletePartialFormSubmissions;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeletePartialFormSubmissionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_deletes_partial_submissions_older_than_the_configured_threshold()
    {
        config(['statamic.forms.delete_partial_submissions_after' => 7]);

        $form = tap(Form::make('contact'))->save();

        Carbon::setTestNow('2025-06-01 12:00:00');
        $oldPartial = tap($form->makeSubmission()->set('partial', true))->save();

        Carbon::setTestNow('2025-06-02 12:00:00');
        $oldFinalized = tap($form->makeSubmission())->save();

        Carbon::setTestNow('2025-06-14 12:00:00');
        $recentPartial = tap($form->makeSubmission()->set('partial', true))->save();

        Carbon::setTestNow('2025-06-15 12:00:00');

        (new DeletePartialFormSubmissions)->handle();

        $this->assertNull($form->submission($oldPartial->id()));
        $this->assertNotNull($form->submission($recentPartial->id()));
        $this->assertNotNull($form->submission($oldFinalized->id()));
    }

    #[Test]
    public function it_only_deletes_partial_submissions_never_finalized()
    {
        config(['statamic.forms.delete_partial_submissions_after' => 7]);

        $form = tap(Form::make('contact'))->save();

        Carbon::setTestNow('2025-06-01 12:00:00');
        $partial = tap($form->makeSubmission()->set('partial', true))->save();

        Carbon::setTestNow('2025-06-02 12:00:00');
        $finalized = tap($form->makeSubmission())->save();

        Carbon::setTestNow('2025-06-30 12:00:00');

        (new DeletePartialFormSubmissions)->handle();

        $this->assertNull($form->submission($partial->id()));
        $this->assertNotNull($form->submission($finalized->id()));
    }

    #[Test]
    public function it_does_not_delete_anything_when_disabled()
    {
        config(['statamic.forms.delete_partial_submissions_after' => null]);

        $form = tap(Form::make('contact'))->save();

        Carbon::setTestNow('2025-06-01 12:00:00');
        $partial = tap($form->makeSubmission()->set('partial', true))->save();

        Carbon::setTestNow('2025-06-30 12:00:00');

        (new DeletePartialFormSubmissions)->handle();

        $this->assertNotNull($form->submission($partial->id()));
    }

    #[Test]
    public function it_deletes_attached_assets_when_garbage_collection_is_enabled()
    {
        config([
            'statamic.forms.delete_partial_submissions_after' => 7,
            'statamic.forms.garbage_collect_assets' => true,
        ]);

        $form = $this->formWithUploadField();
        $partial = $this->partialWithAsset($form);

        Storage::disk('avatars')->assertExists('avatar.jpg');

        (new DeletePartialFormSubmissions)->handle();

        $this->assertNull($form->submission($partial->id()));
        Storage::disk('avatars')->assertMissing('avatar.jpg');
    }

    #[Test]
    public function it_leaves_attached_assets_when_garbage_collection_is_disabled()
    {
        config([
            'statamic.forms.delete_partial_submissions_after' => 7,
            'statamic.forms.garbage_collect_assets' => false,
        ]);

        $form = $this->formWithUploadField();
        $partial = $this->partialWithAsset($form);

        (new DeletePartialFormSubmissions)->handle();

        // The partial is still deleted, but its asset is left untouched.
        $this->assertNull($form->submission($partial->id()));
        Storage::disk('avatars')->assertExists('avatar.jpg');
    }

    private function formWithUploadField()
    {
        Storage::fake('avatars');
        tap(AssetContainer::make('avatars')->disk('avatars'))->save();

        return tap(Form::make('contact')->formFields([
            'sections' => [
                ['fields' => [
                    ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars']],
                ]],
            ],
        ]))->save();
    }

    private function partialWithAsset($form)
    {
        $asset = tap(AssetContainer::find('avatars')->makeAsset('avatar.jpg'))
            ->upload(UploadedFile::fake()->image('avatar.jpg'));

        Carbon::setTestNow('2025-06-01 12:00:00');
        $partial = tap($form->makeSubmission()->set('partial', true)->set('avatar', [$asset->path()]))->save();

        Carbon::setTestNow('2025-06-30 12:00:00');

        return $partial;
    }
}
