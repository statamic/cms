<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\FieldTransformer;
use Statamic\Fieldtypes\Info;
use Tests\TestCase;

class InfoTest extends TestCase
{
    #[Test]
    public function it_preserves_the_custom_icon_when_saving_a_blueprint()
    {
        $iconHandle = (new Info)->configFields()->all()->first(fn ($field) => $field->type() === 'icon')->handle();
        $field = FieldTransformer::toVue([
            'handle' => 'notice',
            'field' => [
                'type' => 'info',
                'content' => 'Helpful information.',
                $iconHandle => 'lightbulb-idea',
            ],
        ]);

        $this->assertSame('info', $field['icon']);
        $this->assertSame('lightbulb-idea', FieldTransformer::fromVue($field)['field'][$iconHandle] ?? null);
    }

    #[Test]
    public function it_is_a_non_data_fieldtype()
    {
        $fieldtype = new Info;

        $this->assertSame(['special'], $fieldtype->categories());
        $this->assertFalse($fieldtype->localizable());
        $this->assertFalse($fieldtype->validatable());
        $this->assertFalse($fieldtype->defaultable());
    }

    #[Test]
    public function it_has_configurable_content_state_and_icon()
    {
        $fields = (new Info)->configFields();

        $this->assertSame('textarea', $fields->get('content')->type());
        $this->assertSame('select', $fields->get('state')->type());
        $this->assertSame('notice', $fields->get('state')->get('default'));
        $this->assertSame([
            'notice' => 'Notice',
            'tip' => 'Tip',
            'warning' => 'Warning',
            'important' => 'Important Warning',
            'success' => 'Success',
        ], $fields->get('state')->get('options'));
        $this->assertSame('icon', $fields->get('alert_icon')->type());
        $this->assertSame('default', $fields->get('alert_icon')->get('set'));
        $this->assertSame('compact', $fields->get('alert_icon')->get('mode'));
    }
}
