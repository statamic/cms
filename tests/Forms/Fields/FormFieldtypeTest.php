<?php

namespace Tests\Forms\Fields;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\Text;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Fieldtypes\Dropdown;
use Statamic\Forms\Fieldtypes\ShortAnswer;
use Tests\TestCase;

class FormFieldtypeTest extends TestCase
{
    public function tearDown(): void
    {
        (new \ReflectionProperty(Fieldtype::class, 'extraConfigFields'))->setValue(null, []);
        (new \ReflectionProperty(FormFieldtype::class, 'extraConfigFields'))->setValue(null, []);

        parent::tearDown();
    }

    #[Test]
    public function it_can_make_a_fieldtype_selectable_in_forms()
    {
        $formFieldtype = new class extends FormFieldtype
        {
            public static $handle = 'test-selectable';
            protected $selectable = false;

            public function toFieldArray(): array
            {
                return [];
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
                return [];
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

    #[Test]
    public function config_fields_include_extras_appended_to_the_wrapped_fieldtype()
    {
        Text::appendConfigField('some_extra', ['type' => 'toggle']);

        $shortAnswer = new ShortAnswer;

        $this->assertEquals('toggle', $shortAnswer->configFields()->get('some_extra')->type());
        $this->assertTrue($shortAnswer->configBlueprint()->hasField('some_extra'));
    }

    #[Test]
    public function config_fields_exclude_extras_appended_to_other_fieldtypes()
    {
        Text::appendConfigField('some_extra', ['type' => 'toggle']);

        $dropdown = new Dropdown;

        $this->assertNull($dropdown->configFields()->get('some_extra'));
        $this->assertFalse($dropdown->configBlueprint()->hasField('some_extra'));
    }

    #[Test]
    public function extras_appended_to_the_wrapped_fieldtype_override_curated_config_fields()
    {
        Text::appendConfigField('placeholder', ['type' => 'textarea']);

        $shortAnswer = new ShortAnswer;

        $this->assertEquals('textarea', $shortAnswer->configFields()->get('placeholder')->type());
        $this->assertEquals('textarea', $shortAnswer->configBlueprint()->fields()->get('placeholder')->type());
    }

    #[Test]
    public function it_can_append_a_single_config_field()
    {
        ShortAnswer::appendConfigField('some_extra', ['type' => 'toggle']);

        $shortAnswer = new ShortAnswer;

        $this->assertEquals('toggle', $shortAnswer->configFields()->get('some_extra')->type());
        $this->assertTrue($shortAnswer->configBlueprint()->hasField('some_extra'));
    }

    #[Test]
    public function it_can_append_multiple_config_fields()
    {
        ShortAnswer::appendConfigFields([
            'some_extra' => ['type' => 'toggle'],
            'another_extra' => ['type' => 'textarea'],
        ]);

        $shortAnswer = new ShortAnswer;

        $this->assertEquals('toggle', $shortAnswer->configFields()->get('some_extra')->type());
        $this->assertEquals('textarea', $shortAnswer->configFields()->get('another_extra')->type());
    }

    #[Test]
    public function it_wont_override_previously_appended_config_fields()
    {
        ShortAnswer::appendConfigFields([
            'some_extra' => ['type' => 'toggle'],
            'another_extra' => ['type' => 'textarea'],
        ]);

        ShortAnswer::appendConfigField('yet_another_extra', ['type' => 'text']);

        $shortAnswer = new ShortAnswer;

        $this->assertEquals('toggle', $shortAnswer->configFields()->get('some_extra')->type());
        $this->assertEquals('textarea', $shortAnswer->configFields()->get('another_extra')->type());
        $this->assertEquals('text', $shortAnswer->configFields()->get('yet_another_extra')->type());
    }

    #[Test]
    public function appended_config_fields_exclude_those_appended_to_other_form_fieldtypes()
    {
        ShortAnswer::appendConfigField('some_extra', ['type' => 'toggle']);

        $dropdown = new Dropdown;

        $this->assertNull($dropdown->configFields()->get('some_extra'));
        $this->assertFalse($dropdown->configBlueprint()->hasField('some_extra'));
    }

    #[Test]
    public function config_fields_can_be_appended_to_every_form_fieldtype()
    {
        FormFieldtype::appendConfigField('some_extra', ['type' => 'toggle']);

        $this->assertEquals('toggle', (new ShortAnswer)->configFields()->get('some_extra')->type());
        $this->assertEquals('toggle', (new Dropdown)->configFields()->get('some_extra')->type());
    }

    #[Test]
    public function appended_config_fields_override_extras_appended_to_the_wrapped_fieldtype()
    {
        Text::appendConfigField('some_extra', ['type' => 'toggle']);
        ShortAnswer::appendConfigField('some_extra', ['type' => 'textarea']);

        $shortAnswer = new ShortAnswer;

        $this->assertEquals('textarea', $shortAnswer->configFields()->get('some_extra')->type());
    }
}
