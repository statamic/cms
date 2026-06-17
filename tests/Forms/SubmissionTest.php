<?php

namespace Tests\Forms;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\SubmissionCreated;
use Statamic\Events\SubmissionCreating;
use Statamic\Events\SubmissionDeleted;
use Statamic\Events\SubmissionSaved;
use Statamic\Events\SubmissionSaving;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Forms\SendEmails;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function the_id_is_generated_the_first_time_but_can_be_overridden()
    {
        $submission = Form::make('test')->makeSubmission();

        $this->assertNotNull($id = $submission->id());
        $this->assertEquals($id, $submission->id());
        $this->assertEquals($id, $submission->id());

        $submission->id('123');

        $this->assertEquals('123', $submission->id());
    }

    #[Test]
    public function generated_ids_dont_have_commas()
    {
        // this test becomes unnecessary if we ever move away from using microtime for ids.

        // Set the locale and reset it after.
        $originalLocale = setlocale(LC_TIME, 0);
        setlocale(LC_TIME, 'de_DE');

        $submission = Form::make('test')->makeSubmission();

        $this->assertStringNotContainsString(',', $submission->id());

        setlocale(LC_TIME, $originalLocale);
    }

    #[Test]
    #[DataProvider('utcProvider')]
    public function the_date_is_utc($tz)
    {
        config(['app.timezone' => $tz]);

        Carbon::setTestNow(Carbon::parse('2025-03-12 02:13:25', 'UTC'));

        $submission = Form::make('test')->makeSubmission();

        $this->assertEquals(0, $submission->date()->offset);
        $this->assertEquals('2025-03-12T02:13:25+00:00', $submission->date()->toIso8601String());
    }

    public static function utcProvider()
    {
        return [
            'utc' => ['UTC'],
            'not utc' => ['America/New_York'],
        ];
    }

    #[Test]
    public function it_sets_and_gets_data()
    {
        $form = Form::make('test')
            ->formFields([
                'sections' => [
                    [
                        'fields' => [
                            ['handle' => 'foo', 'field' => ['type' => 'short_answer']],
                        ],
                    ],
                ],
            ]);

        $submission = $form->makeSubmission();

        $this->assertInstanceOf(Collection::class, $data = $submission->data());
        $this->assertEquals([], $data->all());
        $this->assertFalse($submission->has('foo'));
        $this->assertNull($submission->get('foo'));
        $this->assertNull($submission->foo);
        $this->assertFalse($submission->has('hello'));
        $this->assertNull($submission->get('hello'));
        $this->assertNull($submission->hello);

        $return = $submission->set('hello', 'world');

        $this->assertInstanceOf(Collection::class, $data = $submission->data());
        $this->assertEquals(['hello' => 'world'], $data->all());
        $this->assertEquals($submission, $return);
        $this->assertFalse($submission->has('foo'));
        $this->assertNull($submission->get('foo'));
        $this->assertNull($submission->foo);
        $this->assertTrue($submission->has('hello'));
        $this->assertEquals('world', $submission->get('hello'));
        $this->assertEquals('world', $submission->hello);

        $return = $submission->data(['foo' => 'bar', 'baz' => 'qux']);

        $this->assertEquals($submission, $return);
        $this->assertInstanceOf(Collection::class, $data = $submission->data());
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $data->all());
        $this->assertTrue($submission->has('foo'));
        $this->assertEquals('bar', $submission->get('foo'));
        $this->assertEquals('bar', $submission->foo);
        $this->assertFalse($submission->has('hello'));
        $this->assertNull($submission->get('hello'));
        $this->assertNull($submission->hello);
    }

    #[Test]
    public function it_saves_a_submission()
    {
        Event::fake();

        $form = Form::make('contact_us');
        $form->save();

        $submission = $form->makeSubmission();
        $submission->save();

        $this->assertEquals('contact_us', $submission->form()->handle());

        Event::assertDispatched(SubmissionSaving::class, function ($event) use ($submission) {
            return $event->submission === $submission;
        });

        Event::assertDispatched(SubmissionCreating::class, function ($event) use ($submission) {
            return $event->submission === $submission;
        });

        Event::assertDispatched(SubmissionCreated::class, function ($event) use ($submission) {
            return $event->submission === $submission;
        });

        Event::assertDispatched(SubmissionSaved::class, function ($event) use ($submission) {
            return $event->submission === $submission;
        });
    }

    #[Test]
    public function it_dispatches_submission_created_only_once()
    {
        Event::fake();

        $form = Form::make('contact_us');
        $form->save();

        $submission = $form->makeSubmission();

        $submission->save();
        $submission->save();
        $submission->save();

        Event::assertDispatched(SubmissionSaving::class, 3);
        Event::assertDispatched(SubmissionCreated::class, 1);
    }

    #[Test]
    public function it_saves_quietly()
    {
        Event::fake();

        $form = Form::make('contact_us');
        $form->save();

        $submission = $form->makeSubmission();
        $submission->saveQuietly();

        Event::assertNotDispatched(SubmissionSaving::class);
        Event::assertNotDispatched(SubmissionSaved::class);
        Event::assertNotDispatched(SubmissionCreated::class);
        Event::assertNotDispatched(SubmissionCreating::class);
    }

    #[Test]
    public function if_creating_event_returns_false_the_submission_doesnt_save()
    {
        Event::fake([SubmissionCreated::class]);

        Event::listen(SubmissionCreating::class, function () {
            return false;
        });

        $form = Form::make('contact_us');
        $form->save();

        $submission = $form->makeSubmission();
        $return = $submission->save();

        $this->assertFalse($return);
        Event::assertNotDispatched(SubmissionCreated::class);
    }

    #[Test]
    public function if_saving_event_returns_false_the_submission_doesnt_save()
    {
        Event::fake([SubmissionSaved::class]);

        Event::listen(SubmissionSaving::class, function () {
            return false;
        });

        $form = Form::make('contact_us');
        $form->save();

        $submission = $form->makeSubmission();
        $submission->save();

        Event::assertNotDispatched(SubmissionSaved::class);
    }

    #[Test]
    public function it_deletes_quietly()
    {
        Event::fake();

        $form = Form::make('contact_us');
        $form->save();

        $submission = $form->makeSubmission();
        $return = $submission->deleteQuietly();

        Event::assertNotDispatched(SubmissionDeleted::class);

        $this->assertTrue($return);
    }

    #[Test]
    public function it_determines_its_status()
    {
        $form = tap(Form::make('contact_us'))->save();

        $submitted = $form->makeSubmission();
        $this->assertFalse($submitted->isIncomplete());
        $this->assertFalse($submitted->isSpam());
        $this->assertEquals('complete', $submitted->status());

        $incomplete = $form->makeSubmission()->set('incomplete', true);
        $this->assertTrue($incomplete->isIncomplete());
        $this->assertFalse($incomplete->isSpam());
        $this->assertEquals('incomplete', $incomplete->status());

        $spam = $form->makeSubmission()->set('spam', true);
        $this->assertTrue($spam->isSpam());
        $this->assertFalse($spam->isIncomplete());
        $this->assertEquals('spam', $spam->status());
    }

    #[Test]
    #[DataProvider('withheldStatusProvider')]
    public function it_does_not_dispatch_creation_events_when_saving_a_withheld_submission(string $status)
    {
        Event::fake();

        $form = tap(Form::make('contact_us'))->save();

        $submission = $form->makeSubmission()->set($status, true);
        $submission->save();

        // Creation events shouldn't be dispatched.
        Event::assertNotDispatched(SubmissionCreating::class);
        Event::assertNotDispatched(SubmissionCreated::class);

        // But, saving events should.
        Event::assertDispatched(SubmissionSaving::class);
        Event::assertDispatched(SubmissionSaved::class);
    }

    public static function withheldStatusProvider(): array
    {
        return [
            'incomplete' => ['incomplete'],
            'spam' => ['spam'],
        ];
    }

    #[Test]
    public function created_event_is_not_automatically_dispatched_when_removing_the_incomplete_key()
    {
        $form = tap(Form::make('contact_us'))->save();

        $submission = $form->makeSubmission()->set('incomplete', true);
        $submission->save();

        Event::fake();

        // Removing the incomplete key turns it into a "real" submission, but because
        // the record already exists, save() alone won't dispatch Created. This is why
        // complete() dispatches it explicitly (covered by the test below).
        $submission->remove('incomplete');
        $submission->save();

        Event::assertNotDispatched(SubmissionCreating::class);
        Event::assertNotDispatched(SubmissionCreated::class);
        Event::assertDispatched(SubmissionSaved::class);
    }

    #[Test]
    public function completing_a_new_submission_dispatches_created_event_once()
    {
        Bus::fake();
        Event::fake([SubmissionCreated::class]);

        $form = tap(Form::make('contact_us'))->save();
        $submission = $form->makeSubmission();

        $submission->complete(Site::default());

        Event::assertDispatched(SubmissionCreated::class, 1);
        Bus::assertDispatched(SendEmails::class, 1);

        $this->assertNotNull($form->submission($submission->id()));
    }

    #[Test]
    public function completing_an_incomplete_or_spam_submission_removes_the_status_key_and_dispatches_events()
    {
        $form = tap(Form::make('contact_us'))->save();
        $submission = tap($form->makeSubmission()->set('incomplete', true)->set('spam', true))->save();

        Bus::fake();
        Event::fake([SubmissionCreated::class, SubmissionCreating::class]);

        $submission->complete(Site::default());

        $this->assertFalse($submission->isIncomplete());
        $this->assertFalse($submission->isSpam());

        // Submission already exists, so save() won't dispatch the Created event, complete() will.
        Event::assertDispatched(SubmissionCreated::class, 1);
        Event::assertNotDispatched(SubmissionCreating::class);
        Bus::assertDispatched(SendEmails::class, 1);
    }

    #[Test]
    public function completing_a_submission_for_a_non_storing_form_still_dispatches_the_created_event()
    {
        Bus::fake();
        Event::fake([SubmissionCreated::class]);

        $form = tap(Form::make('contact_us')->store(false))->save();
        $submission = $form->makeSubmission();

        $submission->complete(Site::default());

        Event::assertDispatched(SubmissionCreated::class, 1);
        Bus::assertDispatched(SendEmails::class, 1);
        $this->assertNull($form->submission($submission->id()));
    }

    #[Test]
    public function it_clones_internal_collections()
    {
        $form = Form::make('contact_us');
        $form->save();
        $submission = $form->makeSubmission();
        $submission->set('foo', 'A');
        $submission->setSupplement('bar', 'A');

        $clone = clone $submission;
        $clone->set('foo', 'B');
        $clone->setSupplement('bar', 'B');

        $this->assertEquals('A', $submission->get('foo'));
        $this->assertEquals('B', $clone->get('foo'));

        $this->assertEquals('A', $submission->getSupplement('bar'));
        $this->assertEquals('B', $clone->getSupplement('bar'));
    }
}
