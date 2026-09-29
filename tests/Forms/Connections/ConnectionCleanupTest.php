<?php

namespace Tests\Forms\Connections;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Form;
use Statamic\Forms\Connections\Connection;
use Statamic\Forms\Connections\Webhooks\SendWebhook;
use Statamic\Forms\SendEmail;
use Statamic\Forms\Uploaders\FormFileUpload;
use Statamic\Support\VueComponent;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ConnectionCleanupTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        CountdownConnection::register();
        CountdownConnection::$jobs = [];
        CountdownJob::$outcomes = [];
        CountdownJob::$runs = [];
    }

    #[Test]
    public function a_failing_connection_doesnt_stop_the_others_or_the_visitor()
    {
        Exceptions::fake();

        tap(Form::make('contact')->connections(['countdown' => [
            ['id' => 'a'],
            ['id' => 'b'],
            ['id' => 'c'],
        ]])->formFields(['sections' => [['fields' => [
            ['handle' => 'email', 'field' => ['type' => 'text']],
        ]]]]))->save();

        CountdownJob::$outcomes = ['a' => 'throw'];

        $this->post('/!/forms/contact', ['email' => 'foo@bar.com'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertEquals(['a', 'b', 'c'], CountdownJob::$runs);
        Exceptions::assertReported(fn (Exception $e) => $e->getMessage() === 'Job [a] failed.');
    }

    #[Test]
    public function temporary_files_are_deleted_once_every_job_has_succeeded()
    {
        [$submission, $path] = $this->submissionWithUpload([['id' => 'a'], ['id' => 'b']]);

        $submission->finalize();

        $this->assertEquals(['a', 'b'], CountdownJob::$runs);
        Storage::disk('local')->assertMissing('statamic/form-uploads/'.$path);
        $this->assertNull($submission->form()->submission($submission->id())->get('document'));
    }

    #[Test]
    public function a_failed_job_leaves_the_files_until_it_succeeds_on_a_later_run()
    {
        [$submission, $path] = $this->submissionWithUpload([['id' => 'a'], ['id' => 'b']]);

        CountdownJob::$outcomes = ['a' => 'throw'];

        $submission->finalize();

        $this->assertEquals(['a', 'b'], CountdownJob::$runs);
        Storage::disk('local')->assertExists('statamic/form-uploads/'.$path);
        $this->assertSame(1, Cache::get($this->counterKey($submission)));

        CountdownJob::$outcomes = [];
        $this->retry('a');

        Storage::disk('local')->assertMissing('statamic/form-uploads/'.$path);
    }

    #[Test]
    public function running_the_same_job_twice_only_counts_it_once()
    {
        [$submission, $path] = $this->submissionWithUpload([['id' => 'a'], ['id' => 'b']]);

        CountdownJob::$outcomes = ['b' => 'throw'];

        $submission->finalize();
        $this->retry('a');

        $this->assertEquals(['a', 'b', 'a'], CountdownJob::$runs);
        Storage::disk('local')->assertExists('statamic/form-uploads/'.$path);
        $this->assertSame(1, Cache::get($this->counterKey($submission)));

        CountdownJob::$outcomes = [];
        $this->retry('b');

        Storage::disk('local')->assertMissing('statamic/form-uploads/'.$path);
    }

    #[Test]
    public function a_released_job_doesnt_count_down()
    {
        [$submission, $path] = $this->submissionWithUpload([['id' => 'a']]);

        CountdownJob::$outcomes = ['a' => 'release'];

        $submission->finalize();

        $this->assertEquals(['a'], CountdownJob::$runs);
        Storage::disk('local')->assertExists('statamic/form-uploads/'.$path);
        $this->assertSame(1, Cache::get($this->counterKey($submission)));
    }

    #[Test]
    public function a_job_failed_without_throwing_doesnt_count_down()
    {
        Exceptions::fake();

        [$submission, $path] = $this->submissionWithUpload([['id' => 'a']]);

        CountdownJob::$outcomes = ['a' => 'fail'];

        $submission->finalize();

        $this->assertEquals(['a'], CountdownJob::$runs);
        Storage::disk('local')->assertExists('statamic/form-uploads/'.$path);
        $this->assertSame(1, Cache::get($this->counterKey($submission)));
    }

    #[Test]
    public function a_missing_counter_doesnt_trigger_cleanup_or_leave_a_negative_key_behind()
    {
        [$submission, $path] = $this->submissionWithUpload([['id' => 'a']]);

        CountdownJob::$outcomes = ['a' => 'throw'];

        $submission->finalize();

        Cache::forget($this->counterKey($submission));

        CountdownJob::$outcomes = [];
        $this->retry('a');

        Storage::disk('local')->assertExists('statamic/form-uploads/'.$path);
        $this->assertFalse(Cache::has($this->counterKey($submission)));
    }

    #[Test]
    public function the_counter_uses_the_configured_cache_store()
    {
        config(['cache.stores.connections' => ['driver' => 'array']]);
        config(['statamic.forms.connections_cache_store' => 'connections']);

        [$submission, $path] = $this->submissionWithUpload([['id' => 'a'], ['id' => 'b']]);

        CountdownJob::$outcomes = ['a' => 'throw'];

        $submission->finalize();

        $this->assertSame(1, Cache::store('connections')->get($this->counterKey($submission)));
        $this->assertFalse(Cache::has($this->counterKey($submission)));

        CountdownJob::$outcomes = [];
        $this->retry('a');

        Storage::disk('local')->assertMissing('statamic/form-uploads/'.$path);
    }

    #[Test]
    public function a_job_without_the_queueable_trait_throws()
    {
        [$submission] = $this->submissionWithUpload([['id' => 'a', 'job' => 'without-queueable']]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Form connection job ['.JobWithoutQueueableTrait::class.'] must implement '.ShouldQueue::class.' and use the '.Queueable::class.' trait.');

        $submission->finalize();
    }

    #[Test]
    public function a_job_that_doesnt_implement_should_queue_throws()
    {
        [$submission] = $this->submissionWithUpload([['id' => 'a', 'job' => 'without-should-queue']]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Form connection job ['.JobWithoutShouldQueue::class.'] must implement '.ShouldQueue::class.' and use the '.Queueable::class.' trait.');

        $submission->finalize();
    }

    #[Test]
    public function attachments_survive_until_every_email_has_been_sent()
    {
        config([
            'mail.default' => 'array',
            'mail.from' => ['address' => 'from@example.com', 'name' => 'From'],
        ]);
        Http::fake();

        [$submission, $path] = $this->submissionWithUpload([], [
            'email' => [
                ['to' => 'first@example.com', 'attachments' => true],
                ['to' => 'second@example.com', 'attachments' => true],
            ],
            'webhook' => [['url' => 'https://example.com/hook']],
        ]);

        // Capture the jobs instead of running them, so they can be run in the
        // order a real queue might finish them: the webhook first.
        $bus = Bus::getFacadeRoot();
        Bus::fake();
        $submission->finalize();
        $webhooks = Bus::dispatched(SendWebhook::class);
        $emails = Bus::dispatched(SendEmail::class);
        Bus::swap($bus);

        $this->assertCount(1, $webhooks);
        $this->assertCount(2, $emails);

        Bus::dispatch($webhooks->first());
        Bus::dispatch($emails->first());
        Storage::disk('local')->assertExists('statamic/form-uploads/'.$path);

        Bus::dispatch($emails->last());
        Storage::disk('local')->assertMissing('statamic/form-uploads/'.$path);

        $messages = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(2, $messages);
        $messages->each(fn ($message) => $this->assertCount(1, $message->getOriginalMessage()->getAttachments()));
    }

    private function submissionWithUpload(array $jobs, array $connections = []): array
    {
        $form = tap(Form::make('test')->connections([
            ...($jobs ? ['countdown' => $jobs] : []),
            ...$connections,
        ])->formFields([
            'fields' => [
                ['handle' => 'document', 'field' => ['type' => 'form_upload', 'store' => false, 'max_files' => 1]],
            ],
        ]))->save();

        $submission = $form->makeSubmission()->asPartial();

        $path = FormFileUpload::field(['handle' => 'document', 'max_files' => 1], $submission->id())
            ->upload([UploadedFile::fake()->create('resume.pdf', 10)]);

        $submission->set('document', $path)->save();

        return [$submission, $path];
    }

    private function retry(string $id): void
    {
        Bus::dispatch(CountdownConnection::$jobs[$id]);
    }

    private function counterKey(Submission $submission): string
    {
        return "statamic.form-connections.{$submission->form()->handle()}.{$submission->id()}";
    }
}

class CountdownConnection extends Connection
{
    public static array $jobs = [];

    public function finalized(Submission $submission): object|array
    {
        return collect($this->config())
            ->mapWithKeys(fn ($config) => [$config['id'] => match ($config['job'] ?? null) {
                'without-queueable' => new JobWithoutQueueableTrait,
                'without-should-queue' => new JobWithoutShouldQueue,
                default => new CountdownJob($config['id']),
            }])
            ->each(fn ($job, $id) => static::$jobs[$id] = $job)
            ->values()
            ->all();
    }

    public function render(FormContract $form): VueComponent
    {
        return VueComponent::render('countdown-connection');
    }
}

class CountdownJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public static array $outcomes = [];
    public static array $runs = [];

    public function __construct(public string $id)
    {
    }

    public function handle(): void
    {
        static::$runs[] = $this->id;

        match (static::$outcomes[$this->id] ?? null) {
            'throw' => throw new Exception("Job [{$this->id}] failed."),
            'release' => $this->release(),
            'fail' => $this->fail(new Exception("Job [{$this->id}] failed.")),
            default => null,
        };
    }
}

class JobWithoutQueueableTrait implements ShouldQueue
{
    public function handle(): void
    {
    }
}

class JobWithoutShouldQueue
{
    use Dispatchable, Queueable;

    public function handle(): void
    {
    }
}
