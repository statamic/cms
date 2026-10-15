<?php

namespace Tests\Forms;

use Facades\Statamic\Fields\BlueprintRepository;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Fields\LabeledValue;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Relationship;
use Statamic\Forms\Email;
use Statamic\Forms\SubmissionMentions;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SubmissionMentionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_lists_form_fields_first_then_the_rest_alphabetized_by_label()
    {
        $this->makeGlobalSet('company', 'Company', [
            'name' => ['type' => 'text', 'display' => 'Name'],
        ]);
        Form::addMention('shop.owner', 'Owner', fn () => 'x');

        $form = $this->makeForm();

        $this->assertSame([
            'zebra' => 'Zebra',
            'apple' => 'Apple',
            'config.app.name' => 'App Name',
            'globals.company.name' => 'Company: Name',
            'form.title' => 'Form Title',
            'shop.owner' => 'Owner',
            'site.name' => 'Site Name',
            'site.url' => 'Site URL',
        ], SubmissionMentions::options($form));
    }

    #[Test]
    public function it_excludes_form_fields_that_do_not_collect_a_value()
    {
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'short_answer']],
                ['handle' => 'notice', 'field' => ['type' => 'banner']],
            ],
        ]))->save();

        $options = SubmissionMentions::options($form);

        $this->assertArrayHasKey('name', $options);
        $this->assertArrayNotHasKey('notice', $options);
    }

    #[Test]
    public function it_excludes_unsuitable_global_fieldtypes()
    {
        TestRelationshipFieldtype::register();

        $this->makeGlobalSet('company', 'Company', [
            'name' => ['type' => 'text'],
            'photos' => ['type' => 'assets'],
            'body' => ['type' => 'bard'],
            'items' => ['type' => 'grid', 'fields' => []],
            'secret' => ['type' => 'hidden'],
            'related' => ['type' => 'entries'],
            'custom' => ['type' => 'test_relationship'],
            'logo' => ['type' => 'icon'],
        ]);

        $handles = array_keys(SubmissionMentions::options($this->makeForm()));

        $this->assertContains('globals.company.name', $handles);
        $this->assertContains('globals.company.photos', $handles);
        $this->assertNotContains('globals.company.body', $handles);
        $this->assertNotContains('globals.company.items', $handles);
        $this->assertNotContains('globals.company.secret', $handles);
        $this->assertNotContains('globals.company.related', $handles);
        $this->assertNotContains('globals.company.custom', $handles);
        $this->assertNotContains('globals.company.logo', $handles);
    }

    #[Test]
    public function it_skips_global_sets_without_a_blueprint()
    {
        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('find')->with('globals.company')->andReturnNull();
        tap(GlobalSet::make('company')->title('Company'))->save();

        $this->assertArrayNotHasKey('globals.company.name', SubmissionMentions::options($this->makeForm()));
        $this->assertCount(6, SubmissionMentions::options($this->makeForm()));
    }

    #[Test]
    public function it_does_not_call_added_closures_when_building_options()
    {
        $called = false;
        Form::addMention('shop.owner', 'Owner', function () use (&$called) {
            $called = true;
        });

        SubmissionMentions::options($this->makeForm());

        $this->assertFalse($called);
    }

    #[Test]
    public function it_only_calls_added_closures_that_are_referenced()
    {
        $calls = [];
        Form::addMention('shop.owner', 'Owner', function () use (&$calls) {
            $calls[] = 'owner';

            return 'Jason';
        });
        Form::addMention('shop.other', 'Other', function () use (&$calls) {
            $calls[] = 'other';

            return 'Nope';
        });

        $values = SubmissionMentions::values($this->makeSubmission(), ['shop.owner']);

        $this->assertSame(['shop.owner' => 'Jason'], $values);
        $this->assertSame(['owner'], $calls);
    }

    #[Test]
    public function it_throws_for_invalid_added_handles()
    {
        foreach (['owner', 'shop owner.name', 'shop.na!me', 'site.custom', 'form.custom', 'globals.company.name', 'config.custom'] as $handle) {
            try {
                Form::addMention($handle, 'Label', fn () => 'x');
                $this->fail("Handle [{$handle}] should have been rejected.");
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString($handle, $e->getMessage());
            }
        }
    }

    #[Test]
    public function it_has_no_additional_mentions_by_default()
    {
        $this->assertSame([], Form::additionalMentions());
    }

    #[Test]
    public function it_ignores_escaped_literals_when_extracting_handles()
    {
        $this->assertSame(
            ['name'],
            SubmissionMentions::handlesIn('\\[[ shop.name ]] [[ name ]]')
        );
        $this->assertSame([], SubmissionMentions::handlesIn('\\[\\[ shop.owner \\]\\]', markdown: true));
    }

    #[Test]
    public function it_does_not_resolve_escaped_literals_in_replace()
    {
        $calls = 0;
        Form::addMention('shop.name', 'Shop', function () use (&$calls) {
            $calls++;

            return 'Acme';
        });

        $this->assertSame(
            '[[ shop.name ]]',
            SubmissionMentions::replace($this->makeSubmission(), '\\[[ shop.name ]]')
        );
        $this->assertSame(0, $calls);

        $this->assertSame(
            '[[ shop.name ]] Granny Smith',
            SubmissionMentions::replace($this->makeSubmission(), '\\[[ shop.name ]] [[ apple ]]')
        );
        $this->assertSame(0, $calls);
    }

    #[Test]
    public function it_extracts_handles_from_content()
    {
        $this->assertSame(
            ['name', 'site.url'],
            SubmissionMentions::handlesIn('Hi [[ name ]] at [[site.url]]')
        );
        $this->assertSame(
            ['name', 'globals.a.b'],
            SubmissionMentions::handlesIn('[[name]] [[ globals.a.b ]] [ nope ]', markdown: true)
        );
    }

    #[Test]
    public function it_resolves_only_the_requested_form_values()
    {
        $values = SubmissionMentions::values($this->makeSubmission(), ['apple', 'id', 'date', 'form', 'missing']);

        $this->assertSame(['apple' => 'Granny Smith'], $values);
    }

    #[Test]
    public function it_uses_provided_submission_data()
    {
        $values = SubmissionMentions::values($this->makeSubmission(), ['apple'], ['apple' => 'Provided']);

        $this->assertSame(['apple' => 'Provided'], $values);
    }

    #[Test]
    public function it_replaces_mentions_in_content()
    {
        $this->assertSame(
            'Hi Granny Smith, from Stripes',
            SubmissionMentions::replace($this->makeSubmission(), 'Hi [[ apple ]], from [[zebra]]')
        );
    }

    #[Test]
    public function it_decodes_escaped_brackets_when_replacing_without_mentions()
    {
        $this->assertSame('Not [[a mention]]', SubmissionMentions::replace($this->makeSubmission(), 'Not \\[[a mention]]'));
    }

    #[Test]
    public function it_runs_the_replace_callback_without_seeing_submitted_values()
    {
        $seen = null;

        $result = SubmissionMentions::replace($this->makeSubmission(), '[[apple]] {{ x }}', function ($text) use (&$seen) {
            $seen = $text;

            return str_replace('{{ x }}', 'X', $text);
        });

        $this->assertStringNotContainsString('Granny Smith', $seen);
        $this->assertStringContainsString('{{ x }}', $seen);
        $this->assertStringContainsString('Granny Smith', $result);
        $this->assertStringContainsString(' X', $result);
    }

    #[Test]
    public function it_uses_provided_submission_data_when_replacing()
    {
        $this->assertSame(
            'Provided',
            SubmissionMentions::replace($this->makeSubmission(), '[[apple]]', submissionData: ['apple' => 'Provided'])
        );
    }

    #[Test]
    public function it_resolves_site_and_added_values()
    {
        config(['app.name' => 'My App']);
        $this->setSites(['en' => ['url' => 'http://localhost/', 'locale' => 'en_US', 'name' => 'Test Site']]);

        $values = SubmissionMentions::values($this->makeSubmission(), ['site.name', 'site.url', 'config.app.name']);

        $this->assertSame([
            'site.name' => 'Test Site',
            'site.url' => 'http://localhost',
            'config.app.name' => 'My App',
        ], $values);
    }

    #[Test]
    public function it_resolves_the_form_title()
    {
        $submission = $this->makeSubmission();
        $submission->form()->title('Contact Us')->save();

        $this->assertSame(['form.title' => 'Contact Us'], SubmissionMentions::values($submission, ['form.title']));
    }

    #[Test]
    public function it_passes_the_submission_to_added_closures()
    {
        $received = null;
        Form::addMention('shop.owner', 'Owner', function ($submission) use (&$received) {
            $received = $submission;

            return 'Jason';
        });

        $submission = $this->makeSubmission();

        $this->assertSame(['shop.owner' => 'Jason'], SubmissionMentions::values($submission, ['shop.owner']));
        $this->assertSame($submission, $received);
    }

    #[Test]
    public function it_makes_the_site_url_absolute()
    {
        $this->setSites(['en' => ['url' => '/', 'locale' => 'en_US', 'name' => 'English']]);

        $values = SubmissionMentions::values($this->makeSubmission(), ['site.url']);

        $this->assertStringStartsWith('http', $values['site.url']);
    }

    #[Test]
    public function it_resolves_globals_localized_to_the_submission_site()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en_US', 'name' => 'English'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr_FR', 'name' => 'French'],
        ]);

        $set = $this->makeGlobalSet('company', 'Company', ['name' => ['type' => 'text']], ['en', 'fr']);
        $set->makeLocalization('en')->data(['name' => 'Company EN'])->save();
        $set->makeLocalization('fr')->data(['name' => 'Company FR'])->save();

        $submission = $this->makeSubmission()->site('fr');

        $this->assertSame(
            ['globals.company.name' => 'Company FR'],
            SubmissionMentions::values($submission, ['globals.company.name'])
        );
    }

    #[Test]
    public function it_resolves_markdown_globals_to_their_raw_text()
    {
        $this->makeGlobalSet('company', 'Company', ['bio' => ['type' => 'markdown']])
            ->makeLocalization('en')->data(['bio' => 'Some *emphasis* & more'])->save();

        $this->assertArrayHasKey('globals.company.bio', SubmissionMentions::options($this->makeForm()));

        $this->assertSame(
            ['globals.company.bio' => 'Some *emphasis* & more'],
            SubmissionMentions::values($this->makeSubmission(), ['globals.company.bio'])
        );
    }

    #[Test]
    public function it_resolves_unknown_or_unlocalized_globals_to_empty_strings()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en_US', 'name' => 'English'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr_FR', 'name' => 'French'],
        ]);

        $set = $this->makeGlobalSet('company', 'Company', ['name' => ['type' => 'text']]);
        $set->makeLocalization('en')->data(['name' => 'Company EN'])->save();

        $values = SubmissionMentions::values($this->makeSubmission()->site('fr'), [
            'globals.company.name', 'globals.nope.name', 'globals.company',
        ]);

        $this->assertSame([
            'globals.company.name' => '',
            'globals.nope.name' => '',
            'globals.company' => '',
        ], $values);
    }

    #[Test]
    public function it_converts_values_to_text()
    {
        $asset = Mockery::mock(AssetContract::class);
        $asset->shouldReceive('absoluteUrl')->andReturn('http://localhost/assets/file.pdf');

        $this->assertSame('Hello', SubmissionMentions::mentionValue('Hello'));
        $this->assertSame('5', SubmissionMentions::mentionValue(5));
        $this->assertSame('Hello', SubmissionMentions::mentionValue(new Value('Hello')));
        $this->assertSame('Red', SubmissionMentions::mentionValue(new LabeledValue('red', 'Red')));
        $this->assertSame('red', SubmissionMentions::mentionValue(new LabeledValue('red', null)));
        $this->assertSame('Yes', SubmissionMentions::mentionValue(true));
        $this->assertSame('No', SubmissionMentions::mentionValue(false));
        $this->assertSame('a, b, c', SubmissionMentions::mentionValue(['a', '', ['b', null], new LabeledValue('c', 'c')]));
        $this->assertSame($asset->absoluteUrl(), SubmissionMentions::mentionValue($asset));
        $this->assertSame($asset->absoluteUrl(), SubmissionMentions::mentionValue(new Value($asset)));
        $this->assertSame('', SubmissionMentions::mentionValue(null));
        $this->assertSame('', SubmissionMentions::mentionValue(new \stdClass));
        $this->assertSame('str', SubmissionMentions::mentionValue(new class
        {
            public function __toString()
            {
                return 'str';
            }
        }));
    }

    #[Test]
    public function it_prints_the_label_of_each_selected_option()
    {
        $options = ['red' => 'Red', 'blue' => 'Blue', 'green' => 'Green'];

        $form = tap(Form::make('choices')->formFields([
            'fields' => [
                ['handle' => 'boxes', 'field' => ['type' => 'checkboxes', 'options' => $options]],
                ['handle' => 'multi', 'field' => ['type' => 'select', 'multiple' => true, 'options' => $options]],
                ['handle' => 'single', 'field' => ['type' => 'select', 'options' => $options]],
                ['handle' => 'radio', 'field' => ['type' => 'radio', 'options' => $options]],
                ['handle' => 'buttons', 'field' => ['type' => 'button_group', 'options' => $options]],
            ],
        ]))->save();

        $submission = $form->makeSubmission()->data([
            'boxes' => ['red', 'blue'],
            'multi' => ['red', 'blue'],
            'single' => 'green',
            'radio' => 'green',
            'buttons' => 'green',
        ]);

        $this->assertSame([
            'boxes' => 'Red, Blue',
            'multi' => 'Red, Blue',
            'single' => 'Green',
            'radio' => 'Green',
            'buttons' => 'Green',
        ], SubmissionMentions::values($submission, ['boxes', 'multi', 'single', 'radio', 'buttons']));

        $this->assertSame(
            'Red, Blue | Red, Blue | Green',
            SubmissionMentions::replace($submission, '[[ boxes ]] | [[ multi ]] | [[ single ]]')
        );

        $email = new Email($submission, ['to' => 'test@test.com', 'subject' => 'Picked [[ boxes ]]', 'body' => 'You chose [[ multi ]]'], Site::default());

        $this->assertStringContainsString('You chose Red, Blue', $email->render());
        $this->assertSame('Picked Red, Blue', $email->subject);
    }

    #[Test]
    public function it_prints_the_label_of_each_selected_option_in_a_checkboxes_global()
    {
        $this->makeGlobalSet('company', 'Company', [
            'services' => ['type' => 'checkboxes', 'options' => ['red' => 'Red', 'blue' => 'Blue', 'green' => 'Green']],
        ])->makeLocalization('en')->data(['services' => ['red', 'blue']])->save();

        $submission = $this->makeSubmission();

        $this->assertSame(
            ['globals.company.services' => 'Red, Blue'],
            SubmissionMentions::values($submission, ['globals.company.services'])
        );

        $email = new Email($submission, [
            'to' => 'test@test.com',
            'subject' => 'Offering [[ globals.company.services ]]',
            'body' => 'We do [[ globals.company.services ]]',
        ], Site::default());

        $this->assertStringContainsString('We do Red, Blue', $email->render());
        $this->assertSame('Offering Red, Blue', $email->subject);
    }

    #[Test]
    public function it_only_collapses_arrays_with_exactly_the_option_shape()
    {
        $this->assertSame('Red', SubmissionMentions::mentionValue(['key' => 'red', 'value' => 'red', 'label' => 'Red']));
        $this->assertSame('red', SubmissionMentions::mentionValue(['key' => 'red', 'value' => 'red', 'label' => null]));
        $this->assertSame('a, b, c, d', SubmissionMentions::mentionValue(['key' => 'a', 'value' => 'b', 'label' => 'c', 'extra' => 'd']));
    }

    #[Test]
    public function it_prints_only_filenames_for_non_stored_uploads()
    {
        $submission = $this->makeUploadSubmission(['type' => 'form_upload'], ['abc123/cv.pdf', 'abc123/photo.jpg']);

        $this->assertSame(['upload' => 'cv.pdf, photo.jpg'], SubmissionMentions::values($submission, ['upload']));
        $this->assertSame('cv.pdf, photo.jpg', SubmissionMentions::replace($submission, '[[upload]]'));
    }

    #[Test]
    public function it_prints_the_filename_for_a_single_non_stored_upload()
    {
        $submission = $this->makeUploadSubmission(['type' => 'form_upload', 'max_files' => 1], 'abc123/cv.pdf');

        $this->assertSame('cv.pdf', SubmissionMentions::replace($submission, '[[upload]]'));
    }

    #[Test]
    public function it_prints_the_url_for_uploads_stored_as_assets()
    {
        $asset = Mockery::mock(AssetContract::class);
        $asset->shouldReceive('absoluteUrl')->andReturn('http://localhost/assets/cv.pdf');

        $submission = $this->makeUploadSubmission(['type' => 'form_upload', 'store' => true, 'container' => 'assets'], 'cv.pdf');

        $this->assertSame(
            'http://localhost/assets/cv.pdf',
            SubmissionMentions::replace($submission, '[[upload]]', submissionData: ['upload' => new Value($asset)])
        );
    }

    private function makeUploadSubmission(array $field, mixed $value)
    {
        $form = tap(Form::make('uploads')->formFields([
            'fields' => [['handle' => 'upload', 'field' => $field + ['display' => 'Upload']]],
        ]))->save();

        return $form->makeSubmission()->data(['upload' => $value]);
    }

    private function makeForm()
    {
        return tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'zebra', 'field' => ['type' => 'short_answer', 'display' => 'Zebra']],
                ['handle' => 'apple', 'field' => ['type' => 'short_answer', 'display' => 'Apple']],
            ],
        ]))->save();
    }

    private function makeSubmission()
    {
        return $this->makeForm()->makeSubmission()->data(['zebra' => 'Stripes', 'apple' => 'Granny Smith']);
    }

    private function makeGlobalSet(string $handle, string $title, array $fields, array $sites = ['en'])
    {
        $blueprint = Blueprint::makeFromFields($fields)->setHandle($handle)->setNamespace('globals');

        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('find')->with('globals.'.$handle)->andReturn($blueprint);

        return tap(GlobalSet::make($handle)->title($title)->sites($sites))->save();
    }
}

class TestRelationshipFieldtype extends Relationship
{
    protected static $handle = 'test_relationship';

    protected function toItemArray($id)
    {
        return [];
    }

    public function getIndexItems($request)
    {
        return collect();
    }
}
