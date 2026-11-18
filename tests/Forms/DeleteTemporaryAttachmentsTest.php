<?php

namespace Tests\Forms;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Forms\DeleteTemporaryAttachments;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeleteTemporaryAttachmentsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function a_job_queued_before_upgrading_can_be_unserialized_and_run()
    {
        Storage::fake('local');

        $blueprint = Blueprint::makeFromFields([
            'name' => ['type' => 'text'],
            'attachments' => ['type' => 'files'],
        ])->setHandle('contact')->setNamespace('forms');
        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('find')->with('forms.contact')->andReturn($blueprint);

        Storage::disk('local')->put('statamic/file-uploads/abc/resume.pdf', 'contents');

        // Serialized by 6.x for a legacy form with a `files` field and `store: false`.
        $payload = json_decode(file_get_contents(__DIR__.'/../__fixtures__/forms/6x-delete-temporary-attachments-job.json'));

        $job = unserialize($payload);

        $this->assertInstanceOf(DeleteTemporaryAttachments::class, $job);
        $this->assertEquals(['abc/resume.pdf'], $job->submission->get('attachments'));

        $job->handle();

        Storage::disk('local')->assertMissing('statamic/file-uploads/abc/resume.pdf');
        $this->assertNull($job->submission->get('attachments'));
    }
}
