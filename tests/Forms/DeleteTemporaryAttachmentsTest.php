<?php

namespace Tests\Forms;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Forms\DeleteTemporaryAttachments;
use Statamic\Forms\DeleteTemporaryFiles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeleteTemporaryAttachmentsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function a_job_queued_before_upgrading_can_be_unserialized_and_run()
    {
        Storage::fake('local');

        $blueprint = Blueprint::makeFromFields(['attachments' => ['type' => 'files']])->setHandle('contact')->setNamespace('forms');
        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('find')->with('forms.contact')->andReturn($blueprint);

        $form = tap(Form::make('contact')->store(false))->save();

        $submission = $form->makeSubmission()->data(['attachments' => ['abc/resume.pdf']]);

        Storage::disk('local')->put('statamic/file-uploads/abc/resume.pdf', 'contents');

        // 6.x's DeleteTemporaryAttachments had the same properties and traits as DeleteTemporaryFiles.
        $payload = str_replace(
            'O:35:"Statamic\Forms\DeleteTemporaryFiles"',
            'O:41:"Statamic\Forms\DeleteTemporaryAttachments"',
            serialize(new DeleteTemporaryFiles($submission))
        );

        $this->assertStringStartsWith('O:41:"Statamic\Forms\DeleteTemporaryAttachments"', $payload);

        $job = unserialize($payload);

        $this->assertInstanceOf(DeleteTemporaryAttachments::class, $job);

        $job->handle();

        Storage::disk('local')->assertMissing('statamic/file-uploads/abc/resume.pdf');
        $this->assertNull($job->submission->get('attachments'));
    }
}
