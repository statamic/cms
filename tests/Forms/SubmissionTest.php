<?php

namespace Tests\Forms;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\SubmissionCreated;
use Statamic\Events\SubmissionCreating;
use Statamic\Events\SubmissionDeleted;
use Statamic\Events\SubmissionSaved;
use Statamic\Events\SubmissionSaving;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
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
    public function the_date_is_utc($tz, $expectedFormatted)
    {
        config(['app.timezone' => $tz]);

        Carbon::setTestNow(Carbon::parse('2025-03-12 02:13:25', 'UTC'));

        $submission = Form::make('test')->makeSubmission();

        $this->assertEquals('2025-03-12T02:13:25+00:00', $submission->date()->toIso8601String());
        $this->assertEquals($expectedFormatted, $submission->date()->setTimezone(config('app.timezone'))->format('Y-m-d H:i'));
    }

    public static function utcProvider()
    {
        return [
            'utc' => ['UTC', '2025-03-12 02:13'],
            'not utc' => ['America/New_York', '2025-03-11 22:13'],
        ];
    }

    #[Test]
    public function it_sets_and_gets_data()
    {
        $submission = Form::make('test')->makeSubmission();

        $blueprint = Blueprint::makeFromFields(['foo' => ['type' => 'text']]);
        Blueprint::shouldReceive('find')->with('forms.test')->andReturn($blueprint);

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
}
