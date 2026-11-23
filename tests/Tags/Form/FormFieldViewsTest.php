<?php

namespace Tests\Tags\Form;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class FormFieldViewsTest extends FormTestCase
{
    #[Test]
    #[DataProvider('templateLanguageProvider')]
    public function opinion_scale_starting_at_zero_has_nothing_checked_without_a_value($language)
    {
        config(['statamic.templates.language' => $language]);

        $output = $this->renderField(['handle' => 'scale', 'field' => ['type' => 'opinion_scale', 'min' => 0, 'max' => 10]]);

        $this->assertStringContainsString('value="0"', $output);
        $this->assertStringNotContainsString('checked', $output);
    }

    #[Test]
    #[DataProvider('templateLanguageProvider')]
    public function opinion_scale_checks_the_old_value($language)
    {
        config(['statamic.templates.language' => $language]);

        $output = $this->renderField(
            ['handle' => 'scale', 'field' => ['type' => 'opinion_scale', 'min' => 0, 'max' => 10]],
            ['scale' => '0'],
        );

        $this->assertMatchesRegularExpression('/value="0"[^>]*checked/', $output);
        $this->assertSame(1, substr_count($output, 'checked'));
    }

    #[Test]
    #[DataProvider('choiceFieldProvider')]
    public function choice_field_with_an_option_keyed_zero_has_nothing_selected_without_a_value($language, $field, $attribute)
    {
        config(['statamic.templates.language' => $language]);

        $output = $this->renderField(['handle' => 'choice', 'field' => $field]);

        $this->assertStringContainsString('value="0"', $output);
        $this->assertStringNotContainsString($attribute, $output);
    }

    #[Test]
    #[DataProvider('choiceFieldProvider')]
    public function choice_field_selects_the_old_value_keyed_zero($language, $field, $attribute)
    {
        config(['statamic.templates.language' => $language]);

        $output = $this->renderField(['handle' => 'choice', 'field' => $field], ['choice' => '0']);

        $this->assertMatchesRegularExpression('/value="0"[^>]*'.$attribute.'/', $output);
        $this->assertSame(1, substr_count($output, $attribute));
    }

    public static function choiceFieldProvider()
    {
        $options = [
            ['key' => '0', 'value' => 'Red'],
            ['key' => '1', 'value' => 'Green'],
            ['key' => '2', 'value' => 'Blue'],
        ];

        $fields = [
            'dropdown' => [['type' => 'select', 'options' => $options], 'selected'],
            'multiple choice' => [['type' => 'radio', 'options' => $options], 'checked'],
        ];

        return collect(['antlers', 'blade'])->flatMap(fn ($language) => collect($fields)->mapWithKeys(fn ($field, $name) => [
            "{$name} ({$language})" => [$language, ...$field],
        ]))->all();
    }

    #[Test]
    #[DataProvider('templateLanguageProvider')]
    public function unrated_star_rating_is_not_submitted($language)
    {
        config(['statamic.templates.language' => $language]);

        $input = $this->starRatingInput($this->renderField(['handle' => 'stars', 'field' => ['type' => 'star_rating', 'max_stars' => 5]]));

        $this->assertStringContainsString('data-unrated', $input);
        $this->assertStringContainsString('data-name="stars"', $input);
        $this->assertDoesNotMatchRegularExpression('/ name="stars"/', $input);
    }

    #[Test]
    #[DataProvider('templateLanguageProvider')]
    public function rated_star_rating_is_submitted($language)
    {
        config(['statamic.templates.language' => $language]);

        $input = $this->starRatingInput($this->renderField(
            ['handle' => 'stars', 'field' => ['type' => 'star_rating', 'max_stars' => 5]],
            ['stars' => '4'],
        ));

        $this->assertStringNotContainsString('data-unrated', $input);
        $this->assertMatchesRegularExpression('/ name="stars"/', $input);
        $this->assertStringContainsString('value="4"', $input);
    }

    #[Test]
    #[DataProvider('templateLanguageProvider')]
    public function ranking_keeps_the_field_name_when_rerendering_a_value($language)
    {
        config(['statamic.templates.language' => $language]);

        $output = $this->renderField(
            ['handle' => 'seasons', 'field' => ['type' => 'ranking', 'options' => [
                ['key' => 'summer', 'value' => 'Summer'],
                ['key' => 'winter', 'value' => 'Winter'],
            ]]],
            ['seasons' => ['winter', 'summer']],
        );

        $this->assertStringContainsString('Winter<input type="hidden" name="seasons[]" value="winter">', $output);
        $this->assertStringContainsString('Summer<input type="hidden" name="seasons[]" value="summer">', $output);
        $this->assertStringNotContainsString('name="winter[]"', $output);
        $this->assertStringNotContainsString('name="summer[]"', $output);
    }

    #[Test]
    #[DataProvider('currencyStepProvider')]
    public function currency_allows_amounts_with_the_currency_decimals($language, $currency, $step)
    {
        config(['statamic.templates.language' => $language]);

        $output = $this->renderField(['handle' => 'price', 'field' => ['type' => 'currency', 'currency' => $currency]]);

        $this->assertStringContainsString('step="'.$step.'"', $output);
    }

    public static function currencyStepProvider()
    {
        return collect(['antlers', 'blade'])->flatMap(fn ($language) => [
            "two decimals ({$language})" => [$language, 'USD', '0.01'],
            "no decimals ({$language})" => [$language, 'JPY', '1'],
        ])->all();
    }

    public static function templateLanguageProvider()
    {
        return [
            'antlers' => ['antlers'],
            'blade' => ['blade'],
        ];
    }

    private function starRatingInput(string $output): string
    {
        preg_match('/<input id="views-form-stars-field"[^>]*>/', $output, $matches);

        return $matches[0];
    }

    private function renderField(array $field, array $oldData = []): string
    {
        $fields = $oldData
            ? [['handle' => 'failing_field', 'field' => ['type' => 'text', 'validate' => ['required']]], $field]
            : [$field];

        $this->createForm(['fields' => $fields], 'views');

        if ($oldData) {
            $this->post('/!/forms/views', $oldData)
                ->assertSessionHasErrors(['failing_field'], null, 'form.views');
        }

        return $this->normalizeHtml(
            $this->tag('{{ form:views }}{{ form:fields }}{{ field }}{{ /form:fields }}{{ /form:views }}', $oldData)
        );
    }
}
