<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Tests\TestCase;

class SummaryDefaultsTest extends TestCase
{
    #[Test]
    #[DataProvider('fieldProvider')]
    public function the_default_chart_is_registered_and_applies(array $config)
    {
        $field = new FormField('field', $config);
        $chart = $field->fieldtype()->defaultChart();

        $this->assertNotNull($chart);
        $this->assertSame($chart, app('statamic.form-charts')->get($chart::handle()));
        $this->assertTrue(app($chart)->appliesTo($field));
    }

    #[Test]
    #[DataProvider('fieldProvider')]
    public function the_default_insights_are_registered_and_apply(array $config)
    {
        $field = new FormField('field', $config);
        $fieldtype = $field->fieldtype();

        $this->assertContainsOnlyString($fieldtype->defaultInsights());

        foreach ($fieldtype->defaultInsights() as $insight) {
            $this->assertSame($insight, app('statamic.form-insights')->get($insight::handle()));
            $this->assertTrue(app($insight)->setConfig($fieldtype->insightConfig())->appliesTo($field), "{$insight} does not apply");
        }
    }

    public static function fieldProvider()
    {
        return [
            'checkboxes' => [['type' => 'checkboxes']],
            'currency' => [['type' => 'currency', 'currency' => 'GBP']],
            'single dictionary' => [['type' => 'dictionary', 'dictionary' => 'countries', 'max_items' => 1]],
            'multiple dictionary' => [['type' => 'dictionary', 'dictionary' => 'countries']],
            'single dropdown' => [['type' => 'dropdown']],
            'multiple dropdown' => [['type' => 'dropdown', 'multiple' => true]],
            'single image choice' => [['type' => 'image_choice']],
            'multiple image choice' => [['type' => 'image_choice', 'multiple' => true]],
            'multi choice' => [['type' => 'multi_choice']],
            'number' => [['type' => 'number']],
            'opinion scale' => [['type' => 'opinion_scale']],
            'ranking' => [['type' => 'ranking']],
            'star rating' => [['type' => 'star_rating']],
            'toggle' => [['type' => 'toggle']],
            'yes no' => [['type' => 'yes_no']],
        ];
    }
}
