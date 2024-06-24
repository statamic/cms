<?php

namespace Tests\Stache\Stores;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Forms\Submission;
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

        $this->parent = (new SubmissionsStore)->directory(
            $this->directory = Path::tidy(__DIR__.'/../__fixtures__/content/submissions')
        );

        Stache::registerStore($this->parent);

        Stache::store('form-submissions')->directory($this->directory);
    }

    #[Test]
    public function it_makes_entry_instances_from_files()
    {
        $item = $this->parent->store('contact_form')->makeItemFromFile(
            Path::tidy($this->directory).'/contact_form/1631083591.2832.yaml',
            "name: John Smith\nmessage: Hello"
        );

        $this->assertInstanceOf(Submission::class, $item);
        $this->assertEquals('1631083591.2832', $item->id());
        $this->assertEquals('John Smith', $item->get('name'));
        $this->assertEquals(['name' => 'John Smith', 'message' => 'Hello'], $item->data()->all());
        $this->assertTrue(Carbon::createFromFormat('Y-m-d H:i:s', '2021-09-08 06:46:31')->eq($item->date()->startOfSecond()));
    }

    #[Test]
    public function it_saves_to_disk()
    {
        $form = tap(Facades\Form::make('test_form'))->save();
        $submission = Facades\FormSubmission::make()->form($form);
        $submission->set('title', 'Test');

        $this->parent->store('test_form')->save($submission);

        $this->assertStringEqualsFile($path = $this->directory.'/test_form/'.$submission->id().'.yaml', $submission->fileContents());
        @unlink($path);
        $this->assertFileDoesNotExist($path);

        $this->assertEquals($path, $this->parent->store('test_form')->paths()->get($submission->id()));
    }
}
