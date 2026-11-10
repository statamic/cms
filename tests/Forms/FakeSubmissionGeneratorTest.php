<?php

namespace Tests\Forms;

use Faker\Generator;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Fields\Fieldtype;
use Statamic\Forms\FakeSubmissionGenerator;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FakeValueTestFieldtype extends Fieldtype
{
    protected static $handle = 'fake_value_test';

    public function previewValue(): mixed
    {
        return 'from preview value';
    }

    public function fakeValue(Generator $faker): mixed
    {
        return ['ok' => true];
    }
}

class PreviewValueOnlyTestFieldtype extends Fieldtype
{
    protected static $handle = 'preview_value_only_test';

    public function previewValue(): mixed
    {
        return 'from preview value';
    }
}

class FakeSubmissionGeneratorTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_generates_values_for_form_specific_fieldtypes()
    {
        $form = $this->makeForm('kitchen_sink', [
            ['handle' => 'name', 'field' => ['type' => 'name', 'display' => 'Who are you?']],
            ['handle' => 'email', 'field' => ['type' => 'email', 'display' => 'Contact']],
            ['handle' => 'phone', 'field' => ['type' => 'phone', 'display' => 'Call me']],
            ['handle' => 'website', 'field' => ['type' => 'website', 'display' => 'Homepage']],
            ['handle' => 'short', 'field' => ['type' => 'short_answer', 'display' => 'Subject']],
            ['handle' => 'long', 'field' => ['type' => 'long_answer', 'display' => 'Message']],
            ['handle' => 'number', 'field' => ['type' => 'number', 'display' => 'Quantity']],
            ['handle' => 'currency', 'field' => ['type' => 'currency', 'display' => 'Budget', 'currency' => 'USD']],
            ['handle' => 'toggle', 'field' => ['type' => 'toggle', 'display' => 'Subscribe']],
            ['handle' => 'date', 'field' => ['type' => 'date_picker', 'display' => 'Date']],
            ['handle' => 'time', 'field' => ['type' => 'time_picker', 'display' => 'Time']],
            ['handle' => 'dropdown', 'field' => [
                'type' => 'dropdown',
                'options' => [
                    ['key' => 'red', 'value' => 'Red'],
                    ['key' => 'blue', 'value' => 'Blue'],
                ],
            ]],
            ['handle' => 'multi', 'field' => [
                'type' => 'multi_choice',
                'options' => [
                    ['key' => 's', 'value' => 'S'],
                    ['key' => 'm', 'value' => 'M'],
                ],
            ]],
            ['handle' => 'checks', 'field' => [
                'type' => 'checkboxes',
                'options' => [
                    ['key' => 'a', 'value' => 'A'],
                    ['key' => 'b', 'value' => 'B'],
                ],
            ]],
            ['handle' => 'yesno', 'field' => ['type' => 'yes_no']],
            ['handle' => 'image', 'field' => [
                'type' => 'image_choice',
                'options' => [
                    ['key' => 'happy', 'image' => 'h.png'],
                    ['key' => 'sad', 'image' => 's.png'],
                ],
            ]],
            ['handle' => 'scale', 'field' => ['type' => 'opinion_scale', 'min' => 0, 'max' => 10]],
            ['handle' => 'stars', 'field' => [
                'type' => 'star_rating',
                'max_stars' => 5,
                'allow_half_stars' => true,
            ]],
            ['handle' => 'rank', 'field' => [
                'type' => 'ranking',
                'options' => [
                    ['key' => 'a', 'value' => 'A'],
                    ['key' => 'b', 'value' => 'B'],
                    ['key' => 'c', 'value' => 'C'],
                ],
            ]],
            ['handle' => 'dict', 'field' => [
                'type' => 'dictionary',
                'dictionary' => 'countries',
                'max_items' => 1,
            ]],
            ['handle' => 'group', 'field' => [
                'type' => 'group',
                'fields' => [
                    ['handle' => 'city', 'field' => ['type' => 'short_answer', 'display' => 'City']],
                    ['handle' => 'nested_email', 'field' => ['type' => 'email', 'display' => 'Email']],
                ],
            ]],
            ['handle' => 'upload', 'field' => ['type' => 'upload']],
            ['handle' => 'banner', 'field' => ['type' => 'banner', 'display' => 'Notice']],
            ['handle' => 'heading', 'field' => ['type' => 'heading', 'display' => 'Heading']],
            ['handle' => 'spacer', 'field' => ['type' => 'spacer']],
            ['handle' => 'paragraph', 'field' => ['type' => 'paragraph', 'html' => '<p>Hi</p>']],
        ]);

        $values = (new FakeSubmissionGenerator)->generate($form);

        $this->assertIsString($values['name']);
        $this->assertNotEmpty($values['name']);
        $this->assertMatchesRegularExpression('/^.+@.+\..+$/', $values['email']);
        $this->assertIsString($values['phone']);
        $this->assertNotEmpty($values['phone']);
        $this->assertMatchesRegularExpression('/^https?:\/\//', $values['website']);
        $this->assertIsString($values['short']);
        $this->assertIsString($values['long']);
        $this->assertIsInt($values['number']);
        $this->assertIsInt($values['currency']);
        $this->assertIsBool($values['toggle']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $values['date']);
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}$/', $values['time']);
        $this->assertContains($values['dropdown'], ['red', 'blue']);
        $this->assertContains($values['multi'], ['s', 'm']);
        $this->assertIsArray($values['checks']);
        $this->assertNotEmpty($values['checks']);
        $this->assertContains($values['yesno'], ['yes', 'no']);
        $this->assertContains($values['image'], ['happy', 'sad']);
        $this->assertGreaterThanOrEqual(0, $values['scale']);
        $this->assertLessThanOrEqual(10, $values['scale']);
        $this->assertGreaterThanOrEqual(0.5, $values['stars']);
        $this->assertLessThanOrEqual(5, $values['stars']);
        $this->assertEqualsCanonicalizing(['a', 'b', 'c'], $values['rank']);
        $this->assertIsString($values['dict']);
        $this->assertIsArray($values['group']);
        $this->assertArrayHasKey('city', $values['group']);
        $this->assertArrayHasKey('nested_email', $values['group']);
        $this->assertMatchesRegularExpression('/^.+@.+\..+$/', $values['group']['nested_email']);
        $this->assertNull($values['upload']);
        $this->assertNull($values['banner']);
        $this->assertNull($values['heading']);
        $this->assertNull($values['spacer']);
        $this->assertNull($values['paragraph']);
    }

    #[Test]
    public function it_uses_input_type_over_display_heuristics()
    {
        $form = $this->makeForm('input_types', [
            ['handle' => 'contact', 'field' => [
                'type' => 'text',
                'display' => 'Where should we reply?',
                'input_type' => 'email',
            ]],
            ['handle' => 'homepage', 'field' => [
                'type' => 'text',
                'display' => 'Link',
                'input_type' => 'url',
            ]],
            ['handle' => 'mobile', 'field' => [
                'type' => 'text',
                'display' => 'Call me',
                'input_type' => 'tel',
            ]],
            ['handle' => 'person', 'field' => [
                'type' => 'text',
                'display' => 'Who are you?',
                'autocomplete' => 'name',
            ]],
        ]);

        $values = (new FakeSubmissionGenerator)->generate($form);

        $this->assertMatchesRegularExpression('/^.+@.+\..+$/', $values['contact']);
        $this->assertMatchesRegularExpression('/^https?:\/\//', $values['homepage']);
        $this->assertIsString($values['mobile']);
        $this->assertNotEmpty($values['mobile']);
        $this->assertIsString($values['person']);
        $this->assertNotEmpty($values['person']);
    }

    #[Test]
    public function it_respects_number_min_and_max()
    {
        $form = $this->makeForm('ages', [
            ['handle' => 'age', 'field' => [
                'type' => 'number',
                'display' => 'Age',
                'min' => 16,
                'max' => 80,
            ]],
        ]);

        foreach (range(1, 20) as $_) {
            $value = (new FakeSubmissionGenerator)->generate($form)['age'];
            $this->assertGreaterThanOrEqual(16, $value);
            $this->assertLessThanOrEqual(80, $value);
        }
    }

    #[Test]
    public function it_generates_numbers_without_min_and_max()
    {
        $form = $this->makeForm('quantities', [
            ['handle' => 'quantity', 'field' => ['type' => 'number', 'display' => 'Quantity']],
        ]);

        $value = (new FakeSubmissionGenerator)->generate($form)['quantity'];

        $this->assertIsInt($value);
        $this->assertGreaterThanOrEqual(1, $value);
        $this->assertLessThanOrEqual(5000, $value);
    }

    #[Test]
    public function it_respects_star_rating_half_steps()
    {
        $form = $this->makeForm('stars', [
            ['handle' => 'rating', 'field' => [
                'type' => 'star_rating',
                'max_stars' => 5,
                'allow_half_stars' => true,
            ]],
        ]);

        foreach (range(1, 20) as $_) {
            $value = (new FakeSubmissionGenerator)->generate($form)['rating'];
            $this->assertContains($value, [0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5]);
        }
    }

    #[Test]
    public function it_uses_a_custom_fieldtypes_fake_value_over_its_preview_value()
    {
        FakeValueTestFieldtype::register();

        $form = $this->makeForm('custom_fake_type', [
            ['handle' => 'widget', 'field' => ['type' => 'fake_value_test']],
        ]);

        $values = (new FakeSubmissionGenerator)->generate($form);

        $this->assertEquals(['ok' => true], $values['widget']);
    }

    #[Test]
    public function it_falls_back_to_a_custom_fieldtypes_preview_value()
    {
        PreviewValueOnlyTestFieldtype::register();

        $form = $this->makeForm('custom_preview_type', [
            ['handle' => 'widget', 'field' => ['type' => 'preview_value_only_test']],
        ]);

        $values = (new FakeSubmissionGenerator)->generate($form);

        $this->assertSame('from preview value', $values['widget']);
    }

    #[Test]
    public function it_uses_defaults_for_text_but_randomizes_options()
    {
        $form = $this->makeForm('defaults', [
            ['handle' => 'source', 'field' => ['type' => 'short_answer', 'default' => 'newsletter']],
            ['handle' => 'colour', 'field' => [
                'type' => 'dropdown',
                'default' => 'red',
                'options' => [
                    ['key' => 'red', 'value' => 'Red'],
                    ['key' => 'blue', 'value' => 'Blue'],
                ],
            ]],
        ]);

        $values = collect(range(1, 30))->map(fn () => (new FakeSubmissionGenerator)->generate($form));

        $this->assertSame(['newsletter'], $values->pluck('source')->unique()->values()->all());
        $this->assertEqualsCanonicalizing(['red', 'blue'], $values->pluck('colour')->unique()->values()->all());
    }

    #[Test]
    public function it_generates_values_for_groups_with_core_fieldtypes()
    {
        $form = $this->makeForm('core_group', [
            ['handle' => 'address', 'field' => [
                'type' => 'group',
                'fields' => [
                    ['handle' => 'city', 'field' => ['type' => 'text', 'display' => 'City']],
                    ['handle' => 'country', 'field' => ['type' => 'text', 'default' => 'Canada']],
                    ['handle' => 'floor', 'field' => ['type' => 'integer', 'min' => 1, 'max' => 3]],
                ],
            ]],
        ]);

        $values = (new FakeSubmissionGenerator)->generate($form)['address'];

        $this->assertSame(['city', 'country', 'floor'], array_keys($values));
        $this->assertIsString($values['city']);
        $this->assertSame('Canada', $values['country']);
        $this->assertContains($values['floor'], [1, 2, 3]);
    }

    #[Test]
    public function it_returns_multiple_dictionary_values_when_max_items_allows()
    {
        $form = $this->makeForm('dicts', [
            ['handle' => 'countries', 'field' => [
                'type' => 'dictionary',
                'dictionary' => 'countries',
                'max_items' => 3,
            ]],
        ]);

        $value = (new FakeSubmissionGenerator)->generate($form)['countries'];

        $this->assertIsArray($value);
        $this->assertNotEmpty($value);
        $this->assertLessThanOrEqual(3, count($value));
    }

    private function makeForm(string $handle, array $fields)
    {
        return tap(Form::make($handle)->title(ucfirst($handle)), function ($form) use ($fields) {
            $form->formFields([
                'sections' => [
                    ['fields' => $fields],
                ],
            ])->save();
        });
    }
}
