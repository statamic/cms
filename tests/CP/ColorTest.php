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
    }

    #[Test]
    public function the_theme_can_be_customized()
    {
        config(['statamic.cp.colors' => ['primary' => Color::Sky[500]]]);

        $this->assertEquals([
            ...Color::defaults(),
            'primary' => Color::Sky[500],
        ], Color::theme());
    }

    #[Test]
    public function grays_can_be_set_together()
    {
        config(['statamic.cp.colors' => ['grays' => Color::Slate]]);

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
            'gray-950' => Color::Slate[950],
        ], Color::theme());
    }
}
