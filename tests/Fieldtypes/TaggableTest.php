<?php

namespace Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Taggable;
use Tests\TestCase;

class TaggableTest extends TestCase
{
    #[Test]
    public function it_pre_processes()
    {
        $this->assertEquals(['foo'], $this->field()->preProcess('foo'));
        $this->assertEquals(['foo'], $this->field()->preProcess(['foo']));
        $this->assertEquals(['foo', 'bar'], $this->field()->preProcess(['foo', 'bar']));
        $this->assertEquals([], $this->field()->preProcess(null));
    }

    private function field($config = [])
    {
        $ft = new Taggable;

        return $ft->setField(new Field('test', array_merge($config, ['type' => $ft->handle()])));
    }
}
