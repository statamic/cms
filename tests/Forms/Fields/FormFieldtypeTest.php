<?php

namespace Tests\Forms\Fields;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[Test]
    public function it_resolves_view_using_underlying_fieldtype_handle()
    {
        View::addNamespace('statamic', __DIR__.'/__fixtures__/views');

        $formFieldtype = new class extends FormFieldtype
        {
            public static $handle = 'short_answer';
            protected static $fieldtype = 'text';

            public function toFieldArray(): array
            {
                return ['type' => 'text'];
            }
        };

        $this->assertEquals('statamic::forms.antlers.fields.text', $formFieldtype->view());
    }

    #[Test]
    public function it_resolves_view_using_form_fieldtype_handle()
    {
        View::addNamespace('statamic', __DIR__.'/__fixtures__/views');

        $formFieldtype = new class extends FormFieldtype
        {
            public static $handle = 'short_answer';
            protected static $fieldtype = 'unknown_underlying_fieldtype';

            public function toFieldArray(): array
            {
                return ['type' => 'text'];
            }
        };

        $this->assertEquals('statamic::forms.antlers.fields.short_answer', $formFieldtype->view());
    }

    #[Test]
    public function it_falls_back_to_underlying_fieldtype_view_method()
    {
        $formFieldtype = new class extends FormFieldtype
        {
            public static $handle = 'totally_unknown';
            protected static $fieldtype = 'text';

            public function toFieldArray(): array
            {
                return ['type' => 'text'];
            }
        };

        $this->assertEquals('statamic::forms.antlers.fields.default', $formFieldtype->view());
    }

    #[Test]
    #[DataProvider('collectsValueProvider')]
    public function it_determines_whether_it_collects_a_value($categories, $collectsValue)
    {
        $formFieldtype = new class($categories) extends FormFieldtype
        {
            public function __construct(array $categories)
            {
                $this->categories = $categories;
            }

            public function toFieldArray(): array
            {
                return ['type' => 'text'];
            }
        };

        $this->assertEquals($collectsValue, $formFieldtype->collectsValue());
    }

    public static function collectsValueProvider()
    {
        return [
            'text' => [['text'], true],
            'contact' => [['contact'], true],
            'information' => [['information'], false],
            'structure' => [['structure'], false],
            'information alongside another category' => [['text', 'information'], false],
            'no categories' => [[], true],
        ];
    }
}
