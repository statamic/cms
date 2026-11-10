<?php

namespace Tests\Fields;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Tests\TestCase;

class FieldtypeSampleValuesTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        Carbon::setTestNow('2026-11-09 14:30:00');
    }

    #[Test]
    public function the_base_fieldtype_has_no_preview_value()
    {
        $fieldtype = new class extends Fieldtype
        {
        };

        $this->assertNull($fieldtype->previewValue());
        $this->assertNull($fieldtype->fakeValue($this->faker));
    }

    #[Test]
    public function the_base_fake_value_falls_back_to_the_preview_value()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function previewValue(): mixed
            {
                return 'preview';
            }
        };

        $this->assertSame('preview', $fieldtype->fakeValue($this->faker));
    }

    #[Test]
    #[DataProvider('previewValueProvider')]
    public function it_gets_preview_values(array $config, $expected)
    {
        $this->assertSame($expected, $this->field($config)->fieldtype()->previewValue());
    }

    public static function previewValueProvider()
    {
        $options = ['options' => ['red' => 'Red', 'blue' => 'Blue']];

        return [
            'text' => [['type' => 'text', 'display' => 'First Name'], '[First Name]'],
            'text email' => [['type' => 'text', 'input_type' => 'email'], 'name@example.com'],
            'text url' => [['type' => 'text', 'input_type' => 'url'], 'https://example.com'],
            'text tel' => [['type' => 'text', 'display' => 'Phone', 'input_type' => 'tel'], '[Phone]'],
            'slug' => [['type' => 'slug', 'display' => 'Page Title'], 'page-title'],
            'textarea' => [['type' => 'textarea', 'display' => 'Message'], '[Message]'],
            'markdown' => [['type' => 'markdown', 'display' => 'Body'], '[Body]'],
            'hidden' => [['type' => 'hidden', 'display' => 'Source'], '[Source]'],
            'hidden with default' => [['type' => 'hidden', 'display' => 'Source', 'default' => 'newsletter'], 'newsletter'],
            'revealer' => [['type' => 'revealer', 'display' => 'More'], null],
            'list' => [['type' => 'list', 'display' => 'Items'], ['[Items]']],
            'integer' => [['type' => 'integer'], 1],
            'integer with min' => [['type' => 'integer', 'min' => 16], 16],
            'range' => [['type' => 'range', 'min' => 0], 0],
            'float' => [['type' => 'float', 'min' => 1.5], 1.5],
            'opinion scale' => [['type' => 'opinion_scale', 'min' => 0, 'max' => 10], 0],
            'star rating' => [['type' => 'star_rating', 'min' => 1], 1],
            'toggle' => [['type' => 'toggle'], true],
            'date' => [['type' => 'date'], '2026-11-09'],
            'time' => [['type' => 'time'], '14:30'],
            'link' => [['type' => 'link'], 'https://example.com'],
            'select' => [['type' => 'select', ...$options], 'red'],
            'select multiple' => [['type' => 'select', 'multiple' => true, ...$options], ['red']],
            'select without options' => [['type' => 'select'], null],
            'radio' => [['type' => 'radio', ...$options], 'red'],
            'button group' => [['type' => 'button_group', ...$options], 'red'],
            'yes no' => [['type' => 'yes_no', 'options' => ['yes' => 'Yes', 'no' => 'No']], 'yes'],
            'checkboxes' => [['type' => 'checkboxes', ...$options], ['red']],
            'image choice' => [['type' => 'image_choice', 'options' => [['key' => 'a', 'image' => 'a.png']]], 'a'],
            'image choice multiple' => [['type' => 'image_choice', 'multiple' => true, 'options' => [['key' => 'a', 'image' => 'a.png']]], ['a']],
            'ranking' => [['type' => 'ranking', ...$options], ['red', 'blue']],
            'form upload' => [['type' => 'form_upload'], null],
            'group' => [['type' => 'group', 'fields' => [
                ['handle' => 'city', 'field' => ['type' => 'text', 'display' => 'City']],
                ['handle' => 'agree', 'field' => ['type' => 'toggle']],
            ]], ['city' => '[City]', 'agree' => true]],
        ];
    }

    #[Test]
    #[DataProvider('fakeValueProvider')]
    public function it_gets_fake_values(array $config, \Closure $assertion)
    {
        foreach (range(1, 10) as $_) {
            $assertion->call($this, $this->field($config)->fieldtype()->fakeValue($this->faker));
        }
    }

    public static function fakeValueProvider()
    {
        $options = ['options' => ['red' => 'Red', 'blue' => 'Blue']];

        return [
            'text' => [['type' => 'text'], fn ($value) => $this->assertIsString($value)],
            'text email' => [['type' => 'text', 'input_type' => 'email'], fn ($value) => $this->assertMatchesRegularExpression('/^.+@.+\..+$/', $value)],
            'text url' => [['type' => 'text', 'input_type' => 'url'], fn ($value) => $this->assertMatchesRegularExpression('/^https?:\/\//', $value)],
            'text email by handle' => [['type' => 'text', 'handle' => 'email_address'], fn ($value) => $this->assertMatchesRegularExpression('/^.+@.+\..+$/', $value)],
            'slug' => [['type' => 'slug'], fn ($value) => $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $value)],
            'textarea' => [['type' => 'textarea'], fn ($value) => $this->assertIsString($value)],
            'markdown' => [['type' => 'markdown'], fn ($value) => $this->assertIsString($value)],
            'hidden' => [['type' => 'hidden', 'display' => 'Source'], fn ($value) => $this->assertSame('[Source]', $value)],
            'hidden with default' => [['type' => 'hidden', 'default' => 'newsletter'], fn ($value) => $this->assertSame('newsletter', $value)],
            'revealer' => [['type' => 'revealer'], fn ($value) => $this->assertNull($value)],
            'text ignores default' => [['type' => 'text', 'default' => 'Hello'], fn ($value) => $this->assertNotSame('Hello', $value)],
            'list' => [['type' => 'list'], fn ($value) => $this->assertCount(3, $value)],
            'integer' => [['type' => 'integer', 'min' => 16, 'max' => 18], fn ($value) => $this->assertContains($value, [16, 17, 18])],
            'range' => [['type' => 'range', 'min' => 1, 'max' => 2], fn ($value) => $this->assertContains($value, [1, 2])],
            'float' => [['type' => 'float'], fn ($value) => $this->assertIsFloat($value)],
            'opinion scale' => [['type' => 'opinion_scale', 'min' => 0, 'max' => 2], fn ($value) => $this->assertContains($value, [0, 1, 2])],
            'star rating' => [['type' => 'star_rating', 'max_stars' => 2, 'allow_half_stars' => true], fn ($value) => $this->assertContains($value, [0.5, 1, 1.5, 2])],
            'toggle' => [['type' => 'toggle'], fn ($value) => $this->assertIsBool($value)],
            'date' => [['type' => 'date'], fn ($value) => $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $value)],
            'time' => [['type' => 'time'], fn ($value) => $this->assertMatchesRegularExpression('/^\d{2}:\d{2}$/', $value)],
            'link' => [['type' => 'link'], fn ($value) => $this->assertMatchesRegularExpression('/^https?:\/\//', $value)],
            'select' => [['type' => 'select', ...$options], fn ($value) => $this->assertContains($value, ['red', 'blue'])],
            'radio' => [['type' => 'radio', ...$options], fn ($value) => $this->assertContains($value, ['red', 'blue'])],
            'button group' => [['type' => 'button_group', ...$options], fn ($value) => $this->assertContains($value, ['red', 'blue'])],
            'checkboxes' => [['type' => 'checkboxes', ...$options], fn ($value) => $this->assertEmpty(array_diff($value, ['red', 'blue']))],
            'image choice multiple' => [['type' => 'image_choice', 'multiple' => true, 'options' => [['key' => 'a', 'image' => 'a.png']]], fn ($value) => $this->assertSame(['a'], $value)],
            'ranking' => [['type' => 'ranking', ...$options], fn ($value) => $this->assertEqualsCanonicalizing(['red', 'blue'], $value)],
            'dictionary' => [['type' => 'dictionary', 'dictionary' => 'countries', 'max_items' => 1], fn ($value) => $this->assertIsString($value)],
            'form upload' => [['type' => 'form_upload'], fn ($value) => $this->assertNull($value)],
            'group' => [['type' => 'group', 'fields' => [
                ['handle' => 'city', 'field' => ['type' => 'hidden', 'default' => 'Toronto']],
                ['handle' => 'agree', 'field' => ['type' => 'toggle']],
            ]], function ($value) {
                $this->assertSame('Toronto', $value['city']);
                $this->assertIsBool($value['agree']);
            }],
        ];
    }

    private function field(array $config): Field
    {
        return new Field($config['handle'] ?? 'test', $config);
    }
}
