<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Routing\ResolveRedirect;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;
use Statamic\Fields\ArrayableString;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Link;
use Tests\TestCase;

class LinkTest extends TestCase
{
    #[Test]
    public function it_augments_string_to_string()
    {
        ResolveRedirect::shouldReceive('item')
            ->with('/foo', $parent = new Entry, true)
            ->once()
            ->andReturn('/foo');

        $field = new Field('test', ['type' => 'link']);
        $field->setParent($parent);
        $fieldtype = (new Link)->setField($field);

        $augmented = $fieldtype->augment('/foo');
        $this->assertInstanceOf(ArrayableString::class, $augmented);
        $this->assertEquals('/foo', $augmented->value());
        $this->assertEquals(['url' => '/foo'], $augmented->toArray());
    }

    #[Test]
    public function it_augments_reference_to_object()
    {
        $entry = Mockery::mock();
        $entry->shouldReceive('url')->once()->andReturn('/the-entry-url');
        $entry->shouldReceive('toAugmentedArray')->once()->andReturn('augmented entry array');

        ResolveRedirect::shouldReceive('item')
            ->with('entry::test', $parent = new Entry, true)
            ->once()
            ->andReturn($entry);

        $field = new Field('test', ['type' => 'link']);
        $field->setParent($parent);
        $fieldtype = (new Link)->setField($field);

        $augmented = $fieldtype->augment('entry::test');
        $this->assertInstanceOf(ArrayableString::class, $augmented);
        $this->assertEquals($entry, $augmented->value());
        $this->assertEquals('/the-entry-url', (string) $augmented);
        $this->assertEquals('augmented entry array', $augmented->toArray());
    }

    #[Test]
    public function it_augments_invalid_object_to_null()
    {
        ResolveRedirect::shouldReceive('item')
            ->with('entry::invalid', $parent = new Entry, true)
            ->once()
            ->andReturnNull();

        $field = new Field('test', ['type' => 'link']);
        $field->setParent($parent);
        $fieldtype = (new Link)->setField($field);

        $augmented = $fieldtype->augment('entry::invalid');
        $this->assertInstanceOf(ArrayableString::class, $augmented);
        $this->assertNull($augmented->value());
        $this->assertEquals(['url' => null], $augmented->toArray());
    }

    #[Test]
    public function it_augments_null_to_null()
    {
        // null could technically be passed to the ResolveRedirect class, where it would
        // just return null, but we'll just avoid calling it for a little less overhead.
        ResolveRedirect::shouldReceive('resolve')->never();

        $field = new Field('test', ['type' => 'link']);
        $field->setParent(new Entry);
        $fieldtype = (new Link)->setField($field);

        $augmented = $fieldtype->augment(null);
        $this->assertInstanceOf(ArrayableString::class, $augmented);
        $this->assertNull($augmented->value());
        $this->assertEquals(['url' => null], $augmented->toArray());
    }
}
