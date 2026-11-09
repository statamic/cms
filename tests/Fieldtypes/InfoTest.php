<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fieldtypes\Info;
use Tests\TestCase;

class InfoTest extends TestCase
{
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
        $this->assertSame('icon', $fields->get('icon')->type());
        $this->assertSame('default', $fields->get('icon')->get('set'));
        $this->assertSame('compact', $fields->get('icon')->get('mode'));
    }
}
