<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Toggle;
use Statamic\Forms\Insights\Checked;
use Tests\TestCase;

class ToggleTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Toggle)->setField(new FormField('agree', [
            'type' => 'toggle',
            'inline_label' => 'I agree to the terms',
        ]));

        $this->assertEquals([
            'type' => 'toggle',
            'inline_label' => 'I agree to the terms',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Toggle)->setField(new FormField('agree', [
            'type' => 'toggle',
            'inline_label' => 'I agree to the terms',
            'default' => true,
        ]));

        $this->assertEquals([
            'type' => 'toggle',
            'inline_label' => 'I agree to the terms',
            'default' => true,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_defaults_to_a_bar_chart()
    {
        $this->assertEquals(HorizontalBar::class, (new Toggle)->defaultChart());
    }

    #[Test]
    public function it_returns_boolean_chart_options()
    {
        $options = (new Toggle)->setField(new FormField('agree', ['type' => 'toggle']))->chartOptions(collect());

        $this->assertEquals(['true', 'false'], $options->map->key->all());
        $this->assertEquals(['Yes', 'No'], $options->map->label->all());
        $this->assertEquals(['checkmark-circle-filled', 'delete-circle-filled'], $options->map->icon->all());
    }

    #[Test]
    public function it_returns_a_checked_insight()
    {
        $insights = (new Toggle)->setField(new FormField('agree', ['type' => 'toggle']))->insights();

        $this->assertCount(1, $insights);
        $this->assertInstanceOf(Checked::class, $insights[0]);
    }
}
