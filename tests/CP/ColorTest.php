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
}
