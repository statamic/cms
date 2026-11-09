<?php

namespace Tests\Antlers\Components;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Antlers\ParserTestCase;

class BooleanStringAttributesTest extends ParserTestCase
{
    public static function componentProvider()
    {
        return [
            'antlers component' => ['boolean_strings'],
            'blade component' => ['boolean_strings_blade'],
        ];
    }

    #[Test]
    #[DataProvider('componentProvider')]
    public function written_true_and_false_attributes_keep_their_strings($component)
    {
        $this->assertStringStartsWith(
            '<div aria-hidden="true" aria-expanded="false">',
            trim($this->renderString('<x-'.$component.' aria-hidden="true" aria-expanded="false" />'))
        );
    }

    #[Test]
    #[DataProvider('componentProvider')]
    public function bound_true_and_false_strings_keep_their_strings($component)
    {
        $this->assertStringStartsWith(
            '<div aria-hidden="true" aria-expanded="false">',
            trim($this->renderString('<x-'.$component.' :aria-hidden="\'true\'" :aria-expanded="state" />', ['state' => 'false']))
        );
    }

    #[Test]
    #[DataProvider('componentProvider')]
    public function bound_booleans_are_still_booleans($component)
    {
        $this->assertStringStartsWith(
            '<div hidden="hidden">',
            trim($this->renderString('<x-'.$component.' :hidden="yes" :inert="no" />', ['yes' => true, 'no' => false]))
        );
    }

    #[Test]
    #[DataProvider('componentProvider')]
    public function attributes_match_blade($component)
    {
        $this->assertSame(
            trim(Blade::render('<x-'.$component.' aria-hidden="true" aria-expanded="false" :data-state="$state" />', ['state' => 'false'])),
            trim($this->renderString('<x-'.$component.' aria-hidden="true" aria-expanded="false" :data-state="state" />', ['state' => 'false']))
        );
    }

    #[Test]
    public function props_still_receive_booleans()
    {
        $this->assertStringEndsWith('>true</div>', trim($this->renderString('<x-boolean_strings_blade open="true" />')));
        $this->assertStringEndsWith('>false</div>', trim($this->renderString('<x-boolean_strings_blade open="false" />')));
    }
}
