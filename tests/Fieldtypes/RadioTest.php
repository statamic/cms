<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Radio;
use Tests\TestCase;

class RadioTest extends TestCase
{
    use CastsBooleansTests, HasSelectOptionsTests, LabeledValueTests;

    private function field($config)
    {
        $ft = new Radio;

        return $ft->setField(new Field('test', array_merge($config, ['type' => $ft->handle()])));
    }

    #[Test]
    public function it_migrates_the_legacy_inline_config_to_appearance()
    {
        $ft = new Radio;

        $this->assertSame(['appearance' => 'inline'], $ft->migrateConfig(['inline' => true]));
        $this->assertSame([], $ft->migrateConfig(['inline' => false]));
        $this->assertSame(['appearance' => 'chips'], $ft->migrateConfig(['appearance' => 'chips', 'inline' => true]));
        $this->assertSame(['appearance' => 'default'], $ft->migrateConfig(['appearance' => 'default']));
    }
}
