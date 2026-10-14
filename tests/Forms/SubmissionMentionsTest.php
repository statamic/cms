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
use Statamic\Fields\LabeledValue;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Relationship;
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
        Form::registerMention('shop.owner', 'Owner', fn () => 'x');

        $form = $this->makeForm();

        $this->assertSame([
            'zebra' => 'Zebra',
            'apple' => 'Apple',
            'app.name' => 'App Name',
            'globals.company.name' => 'Company: Name',
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
        $this->assertCount(5, SubmissionMentions::options($this->makeForm()));
    }

    #[Test]
    public function it_does_not_call_registered_closures_when_building_options()
    {
        $called = false;
        Form::registerMention('shop.owner', 'Owner', function () use (&$called) {
            $called = true;
        });

        SubmissionMentions::options($this->makeForm());

        $this->assertFalse($called);
    }

    #[Test]
    public function it_only_calls_registered_closures_that_are_referenced()
    {
        $calls = [];
        Form::registerMention('shop.owner', 'Owner', function () use (&$calls) {
            $calls[] = 'owner';

            return 'Jason';
        });
        Form::registerMention('shop.other', 'Other', function () use (&$calls) {
            $calls[] = 'other';

            return 'Nope';
        });

        $values = SubmissionMentions::values($this->makeSubmission(), ['shop.owner']);

        $this->assertSame(['shop.owner' => 'Jason'], $values);
        $this->assertSame(['owner'], $calls);
    }

    #[Test]
    public function it_throws_for_invalid_registered_handles()
    {
        foreach (['owner', 'shop owner.name', 'shop.na!me', 'site.custom', 'globals.company.name'] as $handle) {
            try {
                Form::registerMention($handle, 'Label', fn () => 'x');
                $this->fail("Handle [{$handle}] should have been rejected.");
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString($handle, $e->getMessage());
            }
        }
    }

    #[Test]
    public function it_extracts_handles_from_content()
    {
        $this->assertSame(
            ['name', 'site.url', 'globals.a.b'],
            SubmissionMentions::handlesIn('Hi [[ name ]] at [[site.url]]', '[[name]] [[ globals.a.b ]] [ nope ]')
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
    public function it_resolves_site_and_registered_values()
    {
        config(['app.name' => 'My App']);
        $this->setSites(['en' => ['url' => 'http://localhost/', 'locale' => 'en_US', 'name' => 'Test Site']]);

        $values = SubmissionMentions::values($this->makeSubmission(), ['site.name', 'site.url', 'app.name']);

        $this->assertSame([
            'site.name' => 'Test Site',
            'site.url' => 'http://localhost',
            'app.name' => 'My App',
        ], $values);
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
