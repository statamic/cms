<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Icon;
use Tests\TestCase;

class IconTest extends TestCase
{
    #[Test]
    public function it_finds_default_icons()
    {
        $result = (string) Antlers::parse('{{ svg src="{test|raw}" }}', ['test' => new Value('add', $this->fieldtype())]);

        $this->assertStringContainsString('<svg', $result);
    }

    #[Test]
    public function it_accepts_svg_strings()
    {
        $result = (string) Antlers::parse('{{ svg :src="test" class="w-4 h-4" sanitize="false" }}', ['test' => new Value('add', $this->fieldtype())]);

        $this->assertStringContainsString('<svg class="w-4 h-4"', $result);
    }

    private function fieldtype($config = [])
    {
        return (new Icon)->setField(new Field('test', array_merge(['type' => 'icon'], $config)));
    }
}
