<?php

namespace Tests\Forms\Fields;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormFieldtype;
use Tests\TestCase;

class FormFieldtypeTest extends TestCase
{
    #[Test]
    public function it_can_make_a_fieldtype_selectable_in_forms()
    {
        $formFieldtype = new class extends FormFieldtype
        {
            public static $handle = 'test-selectable';
            protected $selectable = false;

            public function toFieldArray(): array
            {
                // TODO: Implement toFieldArray() method.
            }
        };

        $this->assertFalse($formFieldtype->isSelectable());

        $formFieldtype::makeSelectable();

        $this->assertTrue($formFieldtype->isSelectable());
        $this->assertTrue(FormFieldtypeRepository::hasBeenMadeSelectable('test-selectable'));
        $this->assertTrue(FormFieldtypeRepository::selectableIsOverriden('test-selectable'));
    }

    #[Test]
    public function it_can_make_a_fieldtype_unselectable_in_forms()
    {
        $formFieldtype = new class extends FormFieldtype
        {
            public static $handle = 'test-unselectable';
            protected $selectable = true;

            public function toFieldArray(): array
            {
                // TODO: Implement toFieldArray() method.
            }
        };

        $this->assertTrue($formFieldtype->isSelectable());

        $formFieldtype::makeUnselectable();

        $this->assertFalse($formFieldtype->isSelectable());
        $this->assertFalse(FormFieldtypeRepository::hasBeenMadeSelectable('test-unselectable'));
        $this->assertTrue(FormFieldtypeRepository::selectableIsOverriden('test-unselectable'));
    }
}
