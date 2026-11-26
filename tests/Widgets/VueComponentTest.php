<?php

namespace Tests\Widgets;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Widgets\VueComponent;
use Tests\TestCase;

class VueComponentTest extends TestCase
{
    #[Test]
    public function subclasses_with_untyped_signatures_still_work()
    {
        $component = LegacyVueComponent::render('legacy-widget', ['foo' => 'bar']);

        $this->assertInstanceOf(LegacyVueComponent::class, $component);
        $this->assertEquals([
            'name' => 'legacy-widget',
            'props' => ['foo' => 'bar'],
            'legacy' => true,
        ], $component->toArray());
    }
}

class LegacyVueComponent extends VueComponent
{
    public static function render($name, $props = [])
    {
        return parent::render($name, $props);
    }

    public function toArray()
    {
        return parent::toArray() + ['legacy' => true];
    }
}
