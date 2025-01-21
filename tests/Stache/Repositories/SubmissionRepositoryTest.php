<?php

namespace Tests\Stache\Repositories;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission as SubmissionAPI;
use Statamic\Stache\Repositories\SubmissionRepository;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\SubmissionsStore;
use Tests\TestCase;

class SubmissionRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $this->app->instance(Stache::class, $stache);
        $this->directory = __DIR__.'/../__fixtures__/content/submissions';
        $stache->registerStores([
            (new SubmissionsStore($stache, app('files')))->directory($this->directory),
        ]);

        $this->repo = new SubmissionRepository($stache);

        $contact = Form::make('contact_form')->save();
        Form::make('sign_up')->save();
    }

    #[Test]
    public function it_gets_all_submissions()
    {
        $submissions = $this->repo->all();

        $this->assertInstanceOf(Collection::class, $submissions);
        $this->assertCount(4, $submissions);
        $this->assertEveryItemIsInstanceOf(Submission::class, $submissions);
    }

    #[Test]
    public function it_saves_a_submission_to_the_stache_and_to_a_file()
    {
        $submission = SubmissionAPI::make()->id('new');
        $submission->form(Form::find('contact_form'));
        $submission->data(['foo' => 'bar']);
        $this->assertNull($this->repo->find('new'));

        $this->repo->save($submission);

        $this->assertNotNull($item = $this->repo->find('new'));
        $this->assertEquals(['foo' => 'bar'], $item->data()->all());
        $this->assertTrue(file_exists($this->directory.'/contact_form/new.yaml'));
        @unlink($this->directory.'/contact_form/new.yaml');
    }
}
