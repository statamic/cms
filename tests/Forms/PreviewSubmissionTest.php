<?php

namespace Tests\Forms;

use Faker\Generator;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\SampleValue;
use Statamic\Forms\PreviewSubmission;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class PreviewSubmissionTestFieldtype extends Fieldtype
{
    protected static $handle = 'preview_submission_test';

    public function previewValue(): mixed
    {
        return 'from preview value';
    }

    public function fakeValue(Generator $faker): mixed
    {
        throw new \Exception('The preview should not call fakeValue().');
    }
}

class PreviewSubmissionFakeOnlyTestFieldtype extends Fieldtype
{
    protected static $handle = 'preview_submission_fake_only_test';

    public function fakeValue(Generator $faker): mixed
    {
        throw new \Exception('The preview should not call fakeValue().');
    }
}

class PreviewSubmissionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_uses_example_values_for_each_field_type()
    {
        Carbon::setTestNow('2026-11-09 14:30:00');

        $form = $this->makeForm('kitchen_sink', [
            ['handle' => 'name', 'field' => ['type' => 'name', 'display' => 'Who are you?']],
            ['handle' => 'first_name', 'field' => ['type' => 'short_answer', 'display' => 'First Name']],
            ['handle' => 'email', 'field' => ['type' => 'email', 'display' => 'Contact']],
            ['handle' => 'phone', 'field' => ['type' => 'phone', 'display' => 'Call me']],
            ['handle' => 'website', 'field' => ['type' => 'website', 'display' => 'Homepage']],
            ['handle' => 'long', 'field' => ['type' => 'long_answer', 'display' => 'Message']],
            ['handle' => 'number', 'field' => ['type' => 'number', 'display' => 'Quantity']],
            ['handle' => 'age', 'field' => ['type' => 'number', 'display' => 'Age', 'min' => 16, 'max' => 80]],
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
            ['handle' => 'dropdown_multiple', 'field' => [
                'type' => 'dropdown',
                'multiple' => true,
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
            ['handle' => 'images', 'field' => [
                'type' => 'image_choice',
                'multiple' => true,
                'options' => [
                    ['key' => 'happy', 'image' => 'h.png'],
                    ['key' => 'sad', 'image' => 's.png'],
                ],
            ]],
            ['handle' => 'scale', 'field' => ['type' => 'opinion_scale', 'min' => 0, 'max' => 10]],
            ['handle' => 'stars', 'field' => ['type' => 'star_rating', 'max_stars' => 5]],
            ['handle' => 'rank', 'field' => [
                'type' => 'ranking',
                'options' => [
                    ['key' => 'a', 'value' => 'A'],
                    ['key' => 'b', 'value' => 'B'],
                    ['key' => 'c', 'value' => 'C'],
                ],
            ]],
            ['handle' => 'dict', 'field' => ['type' => 'dictionary', 'dictionary' => 'countries', 'max_items' => 1]],
            ['handle' => 'dicts', 'field' => ['type' => 'dictionary', 'dictionary' => 'countries', 'max_items' => 3]],
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

        $values = (new PreviewSubmission)->values($form);

        $this->assertSame('Jane Doe', $values['name']);
        $this->assertSame('Jane', $values['first_name']);
        $this->assertSame('name@example.com', $values['email']);
        $this->assertSame('555-0100', $values['phone']);
        $this->assertSame('https://example.com', $values['website']);
        $this->assertSame(SampleValue::paragraph(), $values['long']);
        $this->assertSame(1, $values['number']);
        $this->assertSame(16, $values['age']);
        $this->assertSame(1, $values['currency']);
        $this->assertTrue($values['toggle']);
        $this->assertSame('2026-11-09', $values['date']);
        $this->assertSame('14:30', $values['time']);
        $this->assertSame('red', $values['dropdown']);
        $this->assertSame(['red'], $values['dropdown_multiple']);
        $this->assertSame('s', $values['multi']);
        $this->assertSame(['a'], $values['checks']);
        $this->assertSame('yes', $values['yesno']);
        $this->assertSame('happy', $values['image']);
        $this->assertSame(['happy'], $values['images']);
        $this->assertSame(0, $values['scale']);
        $this->assertSame(1, $values['stars']);
        $this->assertSame(['a', 'b', 'c'], $values['rank']);
        $this->assertIsString($values['dict']);
        $this->assertIsArray($values['dicts']);
        $this->assertCount(1, $values['dicts']);
        $this->assertSame(['city' => 'Springfield', 'nested_email' => 'name@example.com'], $values['group']);
        $this->assertNull($values['upload']);
        $this->assertNull($values['banner']);
        $this->assertNull($values['heading']);
        $this->assertNull($values['spacer']);
        $this->assertNull($values['paragraph']);
    }

    #[Test]
    public function it_uses_a_custom_fieldtypes_preview_value()
    {
        PreviewSubmissionTestFieldtype::register();

        $form = $this->makeForm('custom_type', [
            ['handle' => 'widget', 'field' => ['type' => 'preview_submission_test']],
        ]);

        $this->assertSame('from preview value', (new PreviewSubmission)->values($form)['widget']);
    }

    #[Test]
    public function it_uses_null_for_fieldtypes_without_a_preview_value()
    {
        PreviewSubmissionFakeOnlyTestFieldtype::register();

        $form = $this->makeForm('fake_only_type', [
            ['handle' => 'widget', 'field' => ['type' => 'preview_submission_fake_only_test']],
        ]);

        $this->assertNull((new PreviewSubmission)->values($form)['widget']);
    }

    #[Test]
    public function it_uses_example_values_for_groups_with_core_fieldtypes()
    {
        $form = $this->makeForm('core_group', [
            ['handle' => 'address', 'field' => [
                'type' => 'group',
                'fields' => [
                    ['handle' => 'city', 'field' => ['type' => 'text', 'display' => 'City']],
                    ['handle' => 'floor', 'field' => ['type' => 'integer', 'min' => 2]],
                ],
            ]],
        ]);

        $this->assertSame(['city' => 'Springfield', 'floor' => 2], (new PreviewSubmission)->values($form)['address']);
    }

    #[Test]
    public function it_returns_the_same_values_every_time()
    {
        $form = $this->makeForm('repeatable', [
            ['handle' => 'name', 'field' => ['type' => 'short_answer', 'display' => 'Name']],
            ['handle' => 'email', 'field' => ['type' => 'email', 'display' => 'Email']],
            ['handle' => 'colour', 'field' => [
                'type' => 'dropdown',
                'options' => [
                    ['key' => 'red', 'value' => 'Red'],
                    ['key' => 'blue', 'value' => 'Blue'],
                ],
            ]],
        ]);

        $this->assertSame((new PreviewSubmission)->values($form), (new PreviewSubmission)->values($form));
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
