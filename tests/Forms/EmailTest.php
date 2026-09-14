<?php

namespace Tests\Forms;

use Facades\Statamic\Fields\BlueprintRepository;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Forms\Email;
use Statamic\Forms\Submission;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EmailTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    #[DataProvider('multipleAddressProvider')]
    public function it_adds_recipient_from_the_config($address, $expected)
    {
        $email = $this->makeEmailWithConfig(['to' => $address]);

        $this->assertEquals($expected, $email->to);
    }

    #[Test]
    #[DataProvider('singleAddressProvider')]
    public function it_adds_sender_from_the_config($address, $expected)
    {
        $email = $this->makeEmailWithConfig(['from' => $address]);

        $this->assertEquals($expected, $email->from);
    }

    #[Test]
    #[DataProvider('multipleAddressProvider')]
    public function it_adds_reply_to_from_the_config($address, $expected)
    {
        $email = $this->makeEmailWithConfig(['reply_to' => $address]);

        $this->assertEquals($expected, $email->replyTo);
    }

    #[Test]
    #[DataProvider('multipleAddressProvider')]
    public function it_adds_cc_from_the_config($address, $expected)
    {
        $email = $this->makeEmailWithConfig(['cc' => $address]);

        $this->assertEquals($expected, $email->cc);
    }

    #[Test]
    #[DataProvider('multipleAddressProvider')]
    public function it_adds_bcc_from_the_config($address, $expected)
    {
        $email = $this->makeEmailWithConfig(['bcc' => $address]);

        $this->assertEquals($expected, $email->bcc);
    }

    public static function singleAddressProvider()
    {
        return [
            'single email' => ['foo@bar.com', [
                ['address' => 'foo@bar.com', 'name' => null],
            ]],
            'single email with name' => ['Foo Bar <foo@bar.com>', [
                ['address' => 'foo@bar.com', 'name' => 'Foo Bar'],
            ]],
            'single email using antlers' => ['{{ email }}', [
                ['address' => 'foo@bar.com', 'name' => null],
            ]],
            'single email with name using antlers' => ['{{ name }} <{{ email }}>', [
                ['address' => 'foo@bar.com', 'name' => 'Foo Bar'],
            ]],
            'single email from global set using antlers' => ['{{ company_information:email }}', [
                ['address' => 'info@example.com', 'name' => null],
            ]],
            'single email with name from global set using antlers' => ['{{ company_information:name }} <{{ company_information:email }}>', [
                ['address' => 'info@example.com', 'name' => 'Example Company'],
            ]],
        ];
    }

    public static function multipleAddressProvider()
    {
        return array_merge(static::singleAddressProvider(), [
            'multiple emails' => ['foo@bar.com, baz@qux.com', [
                ['address' => 'foo@bar.com', 'name' => null],
                ['address' => 'baz@qux.com', 'name' => null],
            ]],
            'multiple emails with name' => ['Foo Bar <foo@bar.com>, Baz Qux <baz@qux.com>', [
                ['address' => 'foo@bar.com', 'name' => 'Foo Bar'],
                ['address' => 'baz@qux.com', 'name' => 'Baz Qux'],
            ]],
            'multiple emails using antlers' => ['{{ email }}, baz@qux.com', [
                ['address' => 'foo@bar.com', 'name' => null],
                ['address' => 'baz@qux.com', 'name' => null],
            ]],
            'multiple emails with name using antlers' => ['{{ name }} <{{ email }}>, Baz Qux <baz@qux.com>', [
                ['address' => 'foo@bar.com', 'name' => 'Foo Bar'],
                ['address' => 'baz@qux.com', 'name' => 'Baz Qux'],
            ]],
        ]);
    }

    #[Test]
    public function it_adds_subject_from_the_config()
    {
        $email = $this->makeEmailWithConfig(['subject' => 'A nice form subject']);

        $this->assertEquals('A nice form subject', $email->subject);
    }

    #[Test]
    public function it_adds_data_to_the_view()
    {
        $social = Blueprint::makeFromFields(['twitter' => ['type' => 'text']])->setHandle('social')->setNamespace('globals');
        $company = Blueprint::makeFromFields(['company_name' => ['type' => 'text']])->setHandle('company')->setNamespace('globals');
        $formBlueprint = Blueprint::makeFromFields(['foo' => ['type' => 'text']]);

        BlueprintRepository::shouldReceive('find')->with('globals.social')->andReturn($social);
        BlueprintRepository::shouldReceive('find')->with('globals.company')->andReturn($company);
        BlueprintRepository::shouldReceive('find')->with('forms.test')->andReturn($formBlueprint);

        $social = tap(GlobalSet::make('social'))->save();
        $social->inDefaultSite()->data(['twitter' => '@statamic'])->save();
        $company = tap(GlobalSet::make('company'))->save();
        $company->inDefaultSite()->data(['company_name' => 'Statamic'])->save();

        $form = tap(Form::make('test'))->save();
        $submission = $form->makeSubmission()->data(['foo' => 'bar']);

        $email = $this->makeEmailWithSubmission($submission);

        $this->assertEquals(collect([
            // from the submission
            'id',
            'foo',
            'form',

            // globals
            'company',
            'social',

            // manual "system" vars added to email
            'form_config',
            'email_config',
            'config',
            'date',
            'fields',
            'locale',
            'now',
            'site',
            'site_url',
            'today',
        ])->sort()->values()->all(), collect($email->viewData)->sortKeys()->keys()->all());

        $this->assertEquals('bar', $email->viewData['foo']);
        $this->assertEquals($submission->id(), $email->viewData['id']);
        $this->assertEquals($form, $email->viewData['form']->value());
        $this->assertEquals('Statamic', (string) $email->viewData['company']['company_name']);
    }

    #[Test]
    public function it_escapes_submitted_values_in_the_automagic_email()
    {
        $formBlueprint = Blueprint::makeFromFields([
            'name' => ['type' => 'text'],
            'message' => ['type' => 'textarea'],

            // The select/radio/checkboxes branches emit `label ?? value`, and the label
            // falls back to the raw value when there's no matching option. The raw value
            // is attacker-controlled, so it must be escaped. The option label is author
            // controlled (it lives in the blueprint), so it's not really exploitable, but
            // we escape it too for consistency. Both situations are asserted below.
            'select_labelled' => ['type' => 'select', 'options' => ['a' => '<script>select-label</script>']],
            'select_raw' => ['type' => 'select'],
            'radio_labelled' => ['type' => 'radio', 'options' => ['b' => '<script>radio-label</script>']],
            'radio_raw' => ['type' => 'radio'],
            'checkboxes' => ['type' => 'checkboxes', 'options' => ['c' => '<script>checkbox-label</script>']],
        ]);

        BlueprintRepository::shouldReceive('find')->with('forms.test')->andReturn($formBlueprint);

        $form = tap(Form::make('test'))->save();

        $submission = $form->makeSubmission()->data([
            'name' => '<img src=x onerror=alert(1)>',
            'message' => "line one\n<script>alert(2)</script>",
            'select_labelled' => 'a',
            'select_raw' => '"><svg onload=alert(3)>',
            'radio_labelled' => 'b',
            'radio_raw' => '"><svg onload=alert(4)>',
            'checkboxes' => ['c', '<b>raw-checkbox</b>'],
        ]);

        $email = new Email($submission, ['to' => 'test@test.com'], Site::default());

        $body = $email->render();

        // Attacker-controlled values are escaped, not emitted as live markup.
        $assertEscaped = function ($raw, $escaped) use ($body) {
            $this->assertStringNotContainsString($raw, $body);
            $this->assertStringContainsString($escaped, $body);
        };

        // text / textarea
        $assertEscaped('<img src=x onerror=alert(1)>', '&lt;img src=x onerror=alert(1)&gt;');
        $assertEscaped('<script>alert(2)</script>', '&lt;script&gt;alert(2)&lt;/script&gt;');

        // select — the author-controlled option label (sanitized for consistency) and
        // the attacker-controlled raw fallback value.
        $assertEscaped('<script>select-label</script>', '&lt;script&gt;select-label&lt;/script&gt;');
        $assertEscaped('<svg onload=alert(3)>', '&lt;svg onload=alert(3)&gt;');

        // radio — same, option label and raw fallback value.
        $assertEscaped('<script>radio-label</script>', '&lt;script&gt;radio-label&lt;/script&gt;');
        $assertEscaped('<svg onload=alert(4)>', '&lt;svg onload=alert(4)&gt;');

        // checkboxes — same, option label and raw fallback value.
        $assertEscaped('<script>checkbox-label</script>', '&lt;script&gt;checkbox-label&lt;/script&gt;');
        $assertEscaped('<b>raw-checkbox</b>', '&lt;b&gt;raw-checkbox&lt;/b&gt;');

        // Legitimate multiline text still renders line breaks.
        $this->assertStringContainsString('line one<br', $body);
    }

    #[Test]
    public function attachments_are_added()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_adds_renderable_fields()
    {
        $this->markTestIncomplete();
    }

    private function makeEmailWithSubmission(Submission $submission)
    {
        return tap(new Email($submission, ['to' => 'test@test.com'], Site::default()))->build();
    }

    private function makeEmailWithConfig(array $config)
    {
        $globalSet = GlobalSet::make()->handle('company_information')->save();
        $globalSet->in('en')->data([
            'name' => 'Example Company',
            'email' => 'info@example.com',
        ])->save();

        $formBlueprint = Blueprint::makeFromFields([
            'name' => ['type' => 'text'],
            'email' => ['type' => 'text'],
        ]);

        $companyInformationBlueprint = Blueprint::makeFromFields([
            'name' => ['type' => 'text'],
            'email' => ['type' => 'text'],
        ]);

        BlueprintRepository::shouldReceive('find')->with('forms.test')->andReturn($formBlueprint);
        BlueprintRepository::shouldReceive('find')->with('globals.company_information')->andReturn($companyInformationBlueprint);

        $form = tap(Form::make('test'))->save();

        $submission = $form->makeSubmission()->data([
            'name' => 'Foo Bar',
            'email' => 'foo@bar.com',
        ]);

        return tap(new Email(
            $submission,
            array_merge(['to' => 'test@test.com'], $config),
            Site::default()
        ))->build();
    }

    #[Test]
    public function the_sites_locale_gets_used_on_the_mailable()
    {
        $this->setSites([
            'one' => ['locale' => 'en_US', 'url' => '/one'],
            'two' => ['locale' => 'fr_Fr', 'url' => '/two'],
            'three' => ['locale' => 'de_CH', 'lang' => 'de_CH', 'url' => '/three'],
        ]);

        $makeEmail = function ($site) {
            $submission = Mockery::mock(Submission::class);

            return new Email($submission, ['to' => 'test@test.com'], $site);
        };

        $this->assertEquals('en', $makeEmail(Site::get('one'))->locale);
        $this->assertEquals('fr', $makeEmail(Site::get('two'))->locale);
        $this->assertEquals('de_CH', $makeEmail(Site::get('three'))->locale);
    }
}
