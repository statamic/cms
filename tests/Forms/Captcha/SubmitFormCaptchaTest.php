<?php

namespace Tests\Forms\Captcha;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\FormSubmitted;
use Statamic\Events\SubmissionFinalized;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\Form;
use Statamic\Forms\SendEmails;
use Statamic\Forms\SubmitForm;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SubmitFormCaptchaTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    const SITEVERIFY = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    private $form;

    public function setUp(): void
    {
        parent::setUp();

        Composer::shouldReceive('isInstalled')->andReturnFalse()->byDefault();
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturnTrue()->byDefault();

        config(['statamic.forms.captcha' => [
            'driver' => 'turnstile',
            'site_key' => 'site-key',
            'secret_key' => 'secret-key',
        ]]);

        $this->form = tap(Form::make('contact')->honeypot('winnie')->data(['captcha' => true])->formFields([
            'pages' => [
                [
                    'id' => 'main',
                    'sections' => [
                        ['fields' => [['handle' => 'email', 'field' => ['type' => 'email', 'validate' => 'required']]]],
                    ],
                ],
            ],
        ]))->save();
    }

    public function tearDown(): void
    {
        Form::all()->each(fn ($form) => $form->submissions()->each->delete());

        parent::tearDown();
    }

    private function action($form = null): SubmitForm
    {
        $form ??= $this->form;

        return app(SubmitForm::class)->form($form)->page($form->formFields()->pages()->first()['id']);
    }

    private function passCaptcha(): void
    {
        Http::fake([self::SITEVERIFY => Http::response(['success' => true])]);
    }

    private function failCaptcha(): void
    {
        Http::fake([self::SITEVERIFY => Http::response(['success' => false])]);
    }

    private function assertCaptchaError(callable $callback): void
    {
        try {
            $callback();

            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertEquals(['captcha' => ['The captcha could not be verified. Please try again.']], $e->errors());
        }
    }

    #[Test]
    public function it_submits_when_the_captcha_passes()
    {
        Event::fake([FormSubmitted::class]);
        $this->passCaptcha();

        $result = $this->action()->submit(['email' => 'test@example.com', 'cf-turnstile-response' => 'the-token']);

        $this->assertTrue($result->isFinalized());
        $this->assertNull($result->submission->get('cf-turnstile-response'));
        Event::assertDispatched(FormSubmitted::class);

        Http::assertSent(fn (Request $request) => $request['response'] === 'the-token' && $request['remoteip'] === '127.0.0.1');
    }

    #[Test]
    public function it_rejects_the_submission_when_the_captcha_fails()
    {
        Event::fake([FormSubmitted::class]);
        $this->failCaptcha();

        $this->assertCaptchaError(fn () => $this->action()->submit(['email' => 'test@example.com', 'cf-turnstile-response' => 'bad']));

        $this->assertCount(0, $this->form->submissions());
        Event::assertNotDispatched(FormSubmitted::class);
    }

    #[Test]
    public function it_rejects_the_submission_when_the_token_is_missing_without_calling_the_provider()
    {
        Http::fake();

        $this->assertCaptchaError(fn () => $this->action()->submit(['email' => 'test@example.com']));

        Http::assertNothingSent();
    }

    #[Test]
    public function it_ignores_a_token_that_isnt_a_string()
    {
        Http::fake();

        $this->assertCaptchaError(fn () => $this->action()->submit(['email' => 'test@example.com', 'cf-turnstile-response' => ['nope']]));

        Http::assertNothingSent();
    }

    #[Test]
    public function it_saves_the_submission_as_spam_when_the_captcha_fails_and_configured_to_do_so()
    {
        Bus::fake();
        Event::fake([FormSubmitted::class, SubmissionFinalized::class]);
        $this->failCaptcha();

        $this->form->merge(['captcha_behavior' => 'mark_as_spam'])->save();

        try {
            $this->action()->submit(['email' => 'test@example.com', 'cf-turnstile-response' => 'bad']);

            $this->fail('Expected SilentFormFailureException was not thrown');
        } catch (SilentFormFailureException $e) {
            // Expected
        }

        $submission = $this->form->submissions()->first();

        $this->assertNotNull($submission);
        $this->assertTrue($submission->isSpam());
        $this->assertEquals('test@example.com', $submission->get('email'));

        Event::assertNotDispatched(FormSubmitted::class);
        Event::assertNotDispatched(SubmissionFinalized::class);
        Bus::assertNotDispatched(SendEmails::class);
    }

    #[Test]
    public function it_rejects_instead_of_saving_as_spam_when_the_form_does_not_store_submissions()
    {
        $this->failCaptcha();

        $this->form->store(false)->merge(['captcha_behavior' => 'mark_as_spam'])->save();

        $this->assertCaptchaError(fn () => $this->action()->submit(['email' => 'test@example.com', 'cf-turnstile-response' => 'bad']));

        $this->assertCount(0, $this->form->submissions());
    }

    #[Test]
    public function the_honeypot_is_checked_before_the_captcha()
    {
        Http::fake();

        $this->expectException(SilentFormFailureException::class);

        try {
            $this->action()->submit(['email' => 'test@example.com', 'winnie' => 'the pooh']);
        } finally {
            Http::assertNothingSent();
        }
    }

    #[Test]
    public function fields_are_validated_before_the_captcha()
    {
        Http::fake();

        try {
            $this->action()->submit(['cf-turnstile-response' => 'the-token']);

            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
        }

        Http::assertNothingSent();
    }

    #[Test]
    public function it_doesnt_check_the_captcha_when_the_form_doesnt_use_one()
    {
        Http::fake();

        $this->form->merge(['captcha' => false])->save();

        $this->assertTrue($this->action()->submit(['email' => 'test@example.com'])->isFinalized());

        Http::assertNothingSent();
    }

    #[Test]
    public function it_doesnt_check_the_captcha_when_captchas_arent_set_up()
    {
        Http::fake();

        config(['statamic.forms.captcha.secret_key' => null]);

        $this->assertTrue($this->action()->submit(['email' => 'test@example.com'])->isFinalized());

        Http::assertNothingSent();
    }

    #[Test]
    public function it_only_checks_the_captcha_on_the_final_page()
    {
        Http::fake([self::SITEVERIFY => Http::response(['success' => true])]);

        $form = tap(Form::make('signup')->data(['captcha' => true])->formFields([
            'pages' => [
                ['id' => 'one', 'sections' => [['fields' => [['handle' => 'name', 'field' => ['type' => 'short_answer']]]]]],
                ['id' => 'two', 'sections' => [['fields' => [['handle' => 'email', 'field' => ['type' => 'email']]]]]],
            ],
        ]))->save();

        $first = $this->action($form)->submit(['name' => 'Bob']);

        $this->assertFalse($first->isFinalized());
        Http::assertNothingSent();

        $this->assertCaptchaError(fn () => app(SubmitForm::class)->form($form)->page('two')->resume($first->submission)->submit(['email' => 'bob@example.com']));

        // The partial survives a failed captcha, so the visitor can try again.
        $this->assertTrue($form->submission($first->submission->id())->isPartial());

        $result = app(SubmitForm::class)->form($form)->page('two')->resume($first->submission)
            ->submit(['email' => 'bob@example.com', 'cf-turnstile-response' => 'the-token']);

        $this->assertTrue($result->isFinalized());
        Http::assertSentCount(1);
    }
}
