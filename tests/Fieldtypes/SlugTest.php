<?php

namespace Tests\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fieldtypes\Slug;
use Statamic\Support\Str;
use Tests\TestCase;

class SlugTest extends TestCase
{
    /**
     * @test
     **/
    public function it_generates_slugs()
    {
        $field = (new Slug)->setField(new Field('test', [
            'generate' => true,
        ]));

        foreach ($this->values() as $value) {
            $this->assertSame(Str::slug($value), $field->process($value));
        }

        // Null is still null
        $this->assertNull($field->process(null));
    }

    /**
     * @test
     **/
    public function it_doesnt_generate_slugs()
    {
        $field = (new Slug)->setField(new Field('test', [
            'generate' => false,
        ]));

        foreach ($this->values() as $value) {
            $this->assertSame($value, $field->process($value));
        }

        // Null is still null
        $this->assertNull($field->process(null));
    }

    protected function values()
    {
        return ['hello world!', 'this is _a_ sentence! with #special# charac-ters', 'i-am-already-a-slug', 'single'];
    }
}
