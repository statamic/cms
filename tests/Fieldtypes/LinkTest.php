<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Routing\ResolveRedirect;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;
use Statamic\Facades;
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

    #[Test]
    public function it_pre_processes_url_for_index()
    {
        $fieldtype = (new Link)->setField(new Field('test', ['type' => 'link']));

        $this->assertEquals(
            ['type' => 'url', 'url' => 'https://example.com'],
            $fieldtype->preProcessIndex('https://example.com')
        );
    }

    #[Test]
    public function it_pre_processes_numeric_value_for_index()
    {
        $fieldtype = (new Link)->setField(new Field('test', ['type' => 'link']));

        $this->assertEquals(
            ['type' => 'url', 'url' => 404],
            $fieldtype->preProcessIndex('404')
        );
    }

    #[Test]
    public function it_pre_processes_entry_reference_for_index()
    {
        $entry = Mockery::mock(\Statamic\Contracts\Entries\Entry::class);
        $entry->shouldReceive('url')->once()->andReturn('/the-entry-url');

        Facades\Entry::shouldReceive('find')->with('entry-id')->once()->andReturn($entry);

        $fieldtype = (new Link)->setField(new Field('test', ['type' => 'link']));

        $this->assertEquals(
            ['type' => 'entry', 'url' => '/the-entry-url'],
            $fieldtype->preProcessIndex('entry::entry-id')
        );
    }

    #[Test]
    public function it_pre_processes_asset_reference_for_index()
    {
        $asset = Mockery::mock(\Statamic\Contracts\Assets\Asset::class);
        $asset->shouldReceive('url')->once()->andReturn('/assets/image.jpg');

        Facades\Asset::shouldReceive('find')->with('main::image.jpg')->once()->andReturn($asset);

        $fieldtype = (new Link)->setField(new Field('test', ['type' => 'link']));

        $this->assertEquals(
            ['type' => 'asset', 'url' => '/assets/image.jpg'],
            $fieldtype->preProcessIndex('asset::main::image.jpg')
        );
    }

    #[Test]
    public function it_pre_processes_entry_with_null_url_for_index()
    {
        $entry = Mockery::mock(\Statamic\Contracts\Entries\Entry::class);
        $entry->shouldReceive('url')->once()->andReturnNull();

        Facades\Entry::shouldReceive('find')->with('entry-id')->once()->andReturn($entry);

        $fieldtype = (new Link)->setField(new Field('test', ['type' => 'link']));

        $this->assertNull($fieldtype->preProcessIndex('entry::entry-id'));
    }

    #[Test]
    public function it_pre_processes_missing_entry_reference_for_index()
    {
        Facades\Entry::shouldReceive('find')->with('missing-id')->once()->andReturnNull();

        $fieldtype = (new Link)->setField(new Field('test', ['type' => 'link']));

        $this->assertNull($fieldtype->preProcessIndex('entry::missing-id'));
    }

    #[Test]
    public function it_pre_processes_missing_asset_reference_for_index()
    {
        Facades\Asset::shouldReceive('find')->with('main::missing.jpg')->once()->andReturnNull();

        $fieldtype = (new Link)->setField(new Field('test', ['type' => 'link']));

        $this->assertNull($fieldtype->preProcessIndex('asset::main::missing.jpg'));
    }

    #[Test]
    public function it_pre_processes_first_child_for_index()
    {
        $child = Mockery::mock();
        $child->shouldReceive('url')->once()->andReturn('/parent/child');

        $pages = Mockery::mock();
        $pages->shouldReceive('all')->once()->andReturn(collect([$child]));

        $parent = Mockery::mock();
        $parent->shouldReceive('isRoot')->once()->andReturn(false);
        $parent->shouldReceive('pages')->once()->andReturn($pages);

        $entry = Mockery::mock(\Statamic\Contracts\Entries\Entry::class);
        $entry->shouldReceive('page')->once()->andReturn($parent);

        $field = new Field('test', ['type' => 'link']);
        $field->setParent($entry);
        $fieldtype = (new Link)->setField($field);

        $this->assertEquals(
            ['type' => 'child', 'url' => '/parent/child'],
            $fieldtype->preProcessIndex('@child')
        );
    }

    #[Test]
    public function it_pre_processes_first_child_for_index_when_parent_is_root()
    {
        $child = Mockery::mock();
        $child->shouldReceive('url')->once()->andReturn('/first-child');

        $tree = Mockery::mock();
        $tree->shouldReceive('pages')->once()->andReturn($tree);
        $tree->shouldReceive('all')->once()->andReturn(collect(['root-page', $child])->slice(0));

        $parent = Mockery::mock();
        $parent->shouldReceive('isRoot')->once()->andReturn(true);
        $parent->shouldReceive('locale')->once()->andReturn('en');
        $parent->shouldReceive('structure')->once()->andReturn($structure = Mockery::mock());
        $structure->shouldReceive('in')->with('en')->once()->andReturn($tree);

        $entry = Mockery::mock(\Statamic\Contracts\Entries\Entry::class);
        $entry->shouldReceive('page')->once()->andReturn($parent);

        $field = new Field('test', ['type' => 'link']);
        $field->setParent($entry);
        $fieldtype = (new Link)->setField($field);

        $this->assertEquals(
            ['type' => 'child', 'url' => '/first-child'],
            $fieldtype->preProcessIndex('@child')
        );
    }

    #[Test]
    public function it_pre_processes_first_child_for_index_when_parent_is_not_an_entry()
    {
        $field = new Field('test', ['type' => 'link']);
        $field->setParent(Mockery::mock());
        $fieldtype = (new Link)->setField($field);

        $this->assertNull($fieldtype->preProcessIndex('@child'));
    }

    #[Test]
    public function it_pre_processes_first_child_for_index_when_no_children()
    {
        $pages = Mockery::mock();
        $pages->shouldReceive('all')->once()->andReturn(collect());

        $parent = Mockery::mock();
        $parent->shouldReceive('isRoot')->once()->andReturn(false);
        $parent->shouldReceive('pages')->once()->andReturn($pages);

        $entry = Mockery::mock(\Statamic\Contracts\Entries\Entry::class);
        $entry->shouldReceive('page')->once()->andReturn($parent);

        $field = new Field('test', ['type' => 'link']);
        $field->setParent($entry);
        $fieldtype = (new Link)->setField($field);

        $this->assertNull($fieldtype->preProcessIndex('@child'));
    }
}
