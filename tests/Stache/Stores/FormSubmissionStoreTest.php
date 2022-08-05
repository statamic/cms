<?php

namespace Tests\Stache\Stores;

use Statamic\Facades;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Stache\Stores\SubmissionsStore;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormSubmissionStoreTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $app = app();
        $app['config']->set('statamic.forms.submissions', __DIR__.'/../__fixtures__/content/submissions');

        $this->parent = (new SubmissionsStore)->directory(
            $this->directory = Path::tidy(__DIR__.'/../__fixtures__/content/submissions')
        );

        Stache::registerStore($this->parent);

        Stache::store('form-submissions')->directory($this->directory);
    }

    /** @test */
    public function it_saves_to_disk()
    {
        $form = tap(Facades\Form::make('test_form'))->save();
        $submission = Facades\FormSubmission::make()->form($form);
        $submission->set('title', 'Test');

        $this->parent->store('test_form')->save($submission);

        $this->assertStringEqualsFile($path = $this->directory.'/test_form/'.$submission->id().'.yaml', $submission->fileContents());
        @unlink($path);
        $this->assertFileNotExists($path);

        $this->assertEquals($path, $this->parent->store('test_form')->paths()->get($submission->id()));
    }
}
