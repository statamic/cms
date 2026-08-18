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
use Statamic\Events\SubmissionFinalized;
use Statamic\Events\SubmissionSaved;
use Statamic\Events\SubmissionSaving;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Forms\CreateAssetsFromFileUploads;
use Statamic\Forms\DeleteTemporaryFiles;
use Statamic\Forms\SendEmails;
use Tests\Factories\EntryFactory;
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
    public function setting_data_preserves_the_partial_and_site_keys()
    {
        $form = tap(Form::make('contact_us'))->save();

        $submission = $form->makeSubmission()->asPartial()->site('fr');

        $submission->data(['foo' => 'bar']);

        $this->assertEquals('bar', $submission->get('foo'));
        $this->assertTrue($submission->isPartial());
        $this->assertEquals('fr', $submission->get('site'));
    }

    #[Test]
    public function setting_data_with_partial_or_site_in_the_payload_overrides_them()
    {
        $form = tap(Form::make('contact_us'))->save();

        $submission = $form->makeSubmission()->asPartial()->site('fr');

        $submission->data(['foo' => 'bar', 'partial' => false, 'site' => 'de']);

        $this->assertFalse($submission->isPartial());
        $this->assertEquals('de', $submission->get('site'));
    }

    #[Test]
    public function setting_data_preserves_the_entry_key()
    {
        $form = tap(Form::make('contact_us'))->save();

        $submission = $form->makeSubmission()->set('entry', 'event-1');

        $submission->data(['foo' => 'bar']);

        $this->assertEquals('bar', $submission->get('foo'));
        $this->assertEquals('event-1', $submission->get('entry'));
    }

    #[Test]
    public function the_entry_is_included_in_to_array()
    {
        $form = tap(Form::make('contact_us')->formFields([
            'sections' => [['fields' => [
                ['handle' => 'name', 'field' => ['type' => 'text']],
            ]]],
        ]))->save();

        $submission = $form->makeSubmission()->data(['name' => 'San Holo']);

        $this->assertArrayNotHasKey('entry', $submission->toArray());

        $submission->set('entry', 'event-1');

        $this->assertEquals('event-1', $submission->toArray()['entry']);
    }

    #[Test]
    public function the_entry_is_augmented_to_the_entry_object()
    {
        $entry = (new EntryFactory)->collection('events')->id('event-1')->slug('event-one')->create();

        $form = tap(Form::make('contact_us')->formFields([
            'sections' => [['fields' => [
                ['handle' => 'name', 'field' => ['type' => 'text']],
            ]]],
        ]))->save();

        $submission = $form->makeSubmission()->data(['name' => 'San Holo'])->set('entry', 'event-1');

        $this->assertEquals($entry->id(), $submission->entry()->id());
        $this->assertEquals($entry->id(), $submission->augmentedArrayData()['entry']->id());
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
    public function deleting_dispatches_delete_temporary_files()
    {
        Bus::fake();

        $form = tap(Form::make('contact_us'))->save();
        $submission = tap($form->makeSubmission())->save();

        $submission->delete();

        Bus::assertDispatchedSync(DeleteTemporaryFiles::class);
    }

    #[Test]
    public function deleting_quietly_does_not_dispatch_delete_temporary_files()
    {
        Bus::fake();

        $form = tap(Form::make('contact_us'))->save();
        $submission = tap($form->makeSubmission())->save();

        $submission->deleteQuietly();

        Bus::assertNotDispatched(DeleteTemporaryFiles::class);
    }

    #[Test]
    public function it_determines_its_status()
    {
        $form = tap(Form::make('contact_us'))->save();

        $submitted = $form->makeSubmission();
        $this->assertFalse($submitted->isPartial());
        $this->assertEquals('finalized', $submitted->status());

        $partial = $form->makeSubmission()->asPartial();
        $this->assertTrue($partial->isPartial());
        $this->assertEquals('partial', $partial->status());
    }

    #[Test]
    public function the_status_is_not_queryable()
    {
        $form = tap(Form::make('contact_us'))->save();

        $partial = $form->makeSubmission()->asPartial();
        $finalized = $form->makeSubmission();

        // It's derived and display-only, so it resolves to null for query purposes.
        $this->assertEquals('partial', $partial->status());
        $this->assertNull($partial->getQueryableValue('status'));

        $this->assertEquals('finalized', $finalized->status());
        $this->assertNull($finalized->getQueryableValue('status'));
    }

    #[Test]
    public function it_gets_and_sets_the_site()
    {
        $this->setSites([
            'en' => ['url' => '/'],
            'fr' => ['url' => '/fr'],
        ]);

        $form = tap(Form::make('contact_us'))->save();
        $submission = $form->makeSubmission();

        // A missing site falls back to the default.
        $this->assertEquals('en', $submission->site()->handle());

        // It can be set by handle, storing the handle in the data.
        $return = $submission->site('fr');
        $this->assertSame($submission, $return);
        $this->assertEquals('fr', $submission->get('site'));
        $this->assertEquals('fr', $submission->site()->handle());

        // It can be set with a Site instance, which also stores the handle.
        $submission->site(Site::get('en'));
        $this->assertEquals('en', $submission->get('site'));
        $this->assertEquals('en', $submission->site()->handle());

        // An invalid handle falls back to the default.
        $submission->set('site', 'nonexistent');
        $this->assertEquals('en', $submission->site()->handle());
    }

    #[Test]
    public function saving_a_partial_submission_dispatches_the_same_events_as_any_other()
    {
        Event::fake();

        $form = tap(Form::make('contact_us'))->save();

        $submission = $form->makeSubmission()->set('partial', true);
        $submission->save();

        // A partial submission is still saved and created, so its events are never withheld.
        Event::assertDispatched(SubmissionCreating::class);
        Event::assertDispatched(SubmissionCreated::class);
        Event::assertDispatched(SubmissionSaving::class);
        Event::assertDispatched(SubmissionSaved::class);
    }

    #[Test]
    public function created_event_is_not_dispatched_again_when_removing_the_partial_key()
    {
        $form = tap(Form::make('contact_us'))->save();

        $submission = $form->makeSubmission()->set('partial', true);
        $submission->save();

        Event::fake();

        // The created event already fired when the partial was first saved. Removing the
        // partial key and saving again won't re-dispatch it, because the record exists.
        $submission->remove('partial');
        $submission->save();

        Event::assertNotDispatched(SubmissionCreating::class);
        Event::assertNotDispatched(SubmissionCreated::class);
        Event::assertDispatched(SubmissionSaved::class);
    }

    #[Test]
    public function finalizing_a_new_submission_dispatches_created_and_finalized_events_once()
    {
        Bus::fake();
        Event::fake([SubmissionCreated::class, SubmissionFinalized::class]);

        $form = tap(Form::make('contact_us'))->save();
        $submission = $form->makeSubmission()->asPartial();

        $submission->finalize();

        Event::assertDispatched(SubmissionCreated::class, 1);
        Event::assertDispatched(SubmissionFinalized::class, 1);
        Bus::assertDispatched(CreateAssetsFromFileUploads::class, 1);
        Bus::assertDispatched(SendEmails::class, 1);

        $this->assertNotNull($form->submission($submission->id()));
    }

    #[Test]
    public function finalizing_dispatches_asset_creation_synchronously_then_sends_emails()
    {
        Bus::fake();

        $form = tap(Form::make('contact_us'))->save();
        $submission = $form->makeSubmission()->asPartial();

        $submission->finalize();

        Bus::assertDispatchedSync(CreateAssetsFromFileUploads::class);
        Bus::assertDispatched(SendEmails::class);
    }

    #[Test]
    public function finalizing_a_partial_submission_removes_the_status_key_and_dispatches_events()
    {
        Bus::fake();
        Event::fake([SubmissionCreated::class, SubmissionFinalized::class]);

        $form = tap(Form::make('contact_us'))->save();
        $submission = tap($form->makeSubmission()->set('partial', true))->save();

        $submission->finalize();

        $this->assertFalse($submission->isPartial());

        // The created event fired once, when the partial was first saved. Finalizing an
        // existing submission won't dispatch it again, but it does finalize and email.
        Event::assertDispatched(SubmissionCreated::class, 1);
        Event::assertDispatched(SubmissionFinalized::class, 1);
        Bus::assertDispatched(CreateAssetsFromFileUploads::class, 1);
        Bus::assertDispatched(SendEmails::class, 1);
    }

    #[Test]
    public function finalizing_a_submission_for_a_non_storing_form_still_dispatches_the_created_event()
    {
        Bus::fake();
        Event::fake([SubmissionCreated::class, SubmissionFinalized::class]);

        $form = tap(Form::make('contact_us')->store(false))->save();
        $submission = $form->makeSubmission()->asPartial();

        $submission->finalize();

        Event::assertDispatched(SubmissionCreated::class, 1);
        Event::assertDispatched(SubmissionFinalized::class, 1);
        Bus::assertDispatched(CreateAssetsFromFileUploads::class, 1);
        Bus::assertDispatched(SendEmails::class, 1);
        $this->assertNull($form->submission($submission->id()));
    }

    #[Test]
    public function finalizing_a_submission_for_a_non_storing_form_deletes_it()
    {
        Bus::fake();
        Event::fake([SubmissionCreated::class, SubmissionFinalized::class, SubmissionDeleted::class]);

        $form = tap(Form::make('contact_us')->store(false))->save();

        $submission = tap($form->makeSubmission()->set('partial', true))->save();
        $this->assertNotNull($form->submission($submission->id()));

        $submission->finalize();

        $this->assertNull($form->submission($submission->id()));

        Event::assertDispatched(SubmissionCreated::class, 1);
        Event::assertDispatched(SubmissionFinalized::class, 1);
        Bus::assertDispatched(CreateAssetsFromFileUploads::class, 1);
        Bus::assertDispatched(SendEmails::class, 1);
        Event::assertNotDispatched(SubmissionDeleted::class);
    }

    #[Test]
    public function finalizing_is_idempotent()
    {
        Bus::fake();
        Event::fake([SubmissionFinalized::class]);

        $form = tap(Form::make('contact_us'))->save();
        $submission = $form->makeSubmission()->asPartial();

        $submission->finalize();
        $submission->finalize();

        // The second call is a no-op because the submission is no longer partial.
        Event::assertDispatched(SubmissionFinalized::class, 1);
        Bus::assertDispatched(CreateAssetsFromFileUploads::class, 1);
        Bus::assertDispatched(SendEmails::class, 1);
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
