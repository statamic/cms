<?php

namespace Tests\CP;

use PHPUnit\Framework\Attributes\Test;
use Statamic\CP\Color;
use Tests\TestCase;

class ColorTest extends TestCase
{
    #[Test]
    public function theme_has_defaults()
    {
        $this->assertEquals(Color::defaults(), Color::theme());
        $this->assertEquals(Color::defaults(dark: true), Color::theme(dark: true));
    }

    #[Test]
    public function theme_set_to_a_string_does_nothing()
    {
        config(['statamic.cp.theme' => 'rad']);

        $this->assertEquals(Color::defaults(), Color::theme());
        $this->assertEquals(Color::defaults(dark: true), Color::theme(dark: true));
    }

    #[Test]
    public function the_light_theme_can_be_customized()
    {
        config(['statamic.cp.theme' => ['primary' => Color::Sky[500]]]);

        $this->assertEquals([
            ...Color::defaults(),
            'primary' => Color::Sky[500],
        ], Color::theme());

        $this->assertEquals(Color::defaults(dark: true), Color::theme(dark: true));
    }

    #[Test]
    public function the_dark_theme_can_be_customized()
    {
        config(['statamic.cp.theme' => ['dark-primary' => Color::Sky[700]]]);

        $this->assertEquals([
            ...Color::defaults(dark: true),
            'dark-primary' => Color::Sky[700],
        ], Color::theme(dark: true));

        $this->assertEquals(Color::defaults(), Color::theme());
    }

    #[Test]
    public function grays_can_be_set_together_light_mode()
    {
        config(['statamic.cp.theme' => [
            'grays' => Color::Slate,
        ]]);

        $this->assertEquals([
            ...Color::defaults(),
            'gray-50' => Color::Slate[50],
            'gray-100' => Color::Slate[100],
            'gray-200' => Color::Slate[200],
            'gray-300' => Color::Slate[300],
            'gray-400' => Color::Slate[400],
            'gray-500' => Color::Slate[500],
            'gray-600' => Color::Slate[600],
            'gray-700' => Color::Slate[700],
            'gray-800' => Color::Slate[800],
            'gray-850' => Color::Slate[850],
            'gray-900' => Color::Slate[900],
            'gray-925' => Color::Slate[925],
            'gray-950' => Color::Slate[950],
        ], Color::theme());

        $this->assertEquals(Color::defaults(dark: true), Color::theme(dark: true));
    }

    #[Test]
    public function grays_can_be_set_together_dark_mode()
    {
        config(['statamic.cp.theme' => [
            'dark-grays' => Color::Stone,
        ]]);

        $this->assertEquals([
            ...Color::defaults(dark: true),
            'dark-gray-50' => Color::Stone[50],
            'dark-gray-100' => Color::Stone[100],
            'dark-gray-200' => Color::Stone[200],
            'dark-gray-300' => Color::Stone[300],
            'dark-gray-400' => Color::Stone[400],
            'dark-gray-500' => Color::Stone[500],
            'dark-gray-600' => Color::Stone[600],
            'dark-gray-700' => Color::Stone[700],
            'dark-gray-800' => Color::Stone[800],
            'dark-gray-850' => Color::Stone[850],
            'dark-gray-900' => Color::Stone[900],
            'dark-gray-925' => Color::Stone[925],
            'dark-gray-950' => Color::Stone[950],
        ], Color::theme(dark: true));

        $this->assertEquals(Color::defaults(), Color::theme());
    }
}
