<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Routing\ResolveRedirect;
use Mockery;
use Statamic\Contracts\Assets\Asset;
use Statamic\Entries\Entry;
use Statamic\Fields\ArrayableString;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Link;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LinkTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
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
    }

    /** @test */
    public function it_augments_to_entry()
    {
        $entry = Mockery::mock(Entry::class);
        $entry->shouldReceive('url')->once()->andReturn('/the-entry-url');

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
    }

    /** @test */
    public function it_augments_invalid_entry_to_null()
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
    }

    /** @test */
    public function it_augments_to_asset()
    {
        $asset = Mockery::mock(Asset::class);
        $asset->shouldReceive('url')->once()->andReturn('/the-asset-url');

        ResolveRedirect::shouldReceive('item')
            ->with('asset::test', $parent = new Entry, true)
            ->once()
            ->andReturn($asset);

        $field = new Field('test', ['type' => 'link']);
        $field->setParent($parent);
        $fieldtype = (new Link)->setField($field);

        $augmented = $fieldtype->augment('asset::test');
        $this->assertInstanceOf(ArrayableString::class, $augmented);
        $this->assertEquals($asset, $augmented->value());
        $this->assertEquals('/the-asset-url', (string) $augmented);
    }

    /** @test */
    public function it_augments_invalid_asset_to_null()
    {
        ResolveRedirect::shouldReceive('item')
            ->with('asset::invalid', $parent = new Entry, true)
            ->once()
            ->andReturnNull();

        $field = new Field('test', ['type' => 'link']);
        $field->setParent($parent);
        $fieldtype = (new Link)->setField($field);

        $augmented = $fieldtype->augment('asset::invalid');
        $this->assertInstanceOf(ArrayableString::class, $augmented);
        $this->assertNull($augmented->value());
    }

    /** @test */
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
    }
}
