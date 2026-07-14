<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Routing\ResolveRedirect;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;
use Statamic\Facades;
use Statamic\Fields\ArrayableString;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Link;
use Statamic\Fieldtypes\Link\LinkType;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LinkTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

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
            ['type' => 'url', 'url' => 'https://example.com', 'icon' => 'external-link'],
            $fieldtype->preProcessIndex('https://example.com')
        );
    }

    #[Test]
    public function it_pre_processes_numeric_value_for_index()
    {
        $fieldtype = (new Link)->setField(new Field('test', ['type' => 'link']));

        $this->assertEquals(
            ['type' => 'url', 'url' => 404, 'icon' => 'external-link'],
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
            ['type' => 'entry', 'url' => '/the-entry-url', 'icon' => 'collections'],
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
            ['type' => 'asset', 'url' => '/assets/image.jpg', 'icon' => 'assets'],
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
            ['type' => 'child', 'url' => '/parent/child', 'icon' => 'page'],
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
            ['type' => 'child', 'url' => '/first-child', 'icon' => 'page'],
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

    #[Test]
    #[DataProvider('initialOptionProvider')]
    public function it_preloads_the_initial_option(array $config, mixed $value, bool $withParent, mixed $expected)
    {
        $this->actingAs(tap(Facades\User::make()->makeSuper())->save());
        tap(Facades\Collection::make('pages')->routes('{slug}'))->sites(['en'])->save();

        $field = new Field('test', $config);
        $field->setValue($value);

        if ($withParent) {
            $field->setParent(Mockery::mock());
        }

        $fieldtype = (new Link)->setField($field);

        $this->assertSame($expected, $fieldtype->preload()['initialOption']);
    }

    public static function initialOptionProvider(): array
    {
        return [
            'configured option is used' => [['type' => 'link', 'default_option' => 'entry'], null, false, 'entry'],
            'url when required and no default option' => [['type' => 'link', 'required' => true], null, false, 'url'],
            'null when optional and no default option' => [['type' => 'link'], null, false, null],
            'first-child falls back to url when unavailable' => [['type' => 'link', 'default_option' => 'first-child', 'required' => true], null, true, 'url'],
            'asset falls back to url when unavailable and required' => [['type' => 'link', 'default_option' => 'asset', 'required' => true], null, false, 'url'],
            'asset falls back to null when unavailable and optional' => [['type' => 'link', 'default_option' => 'asset'], null, false, null],
            'existing value overrides default option' => [['type' => 'link', 'default_option' => 'entry'], 'https://example.com', false, 'url'],
            'null when has sometimes rule even if required' => [['type' => 'link', 'required' => true, 'validate' => 'sometimes'], null, false, null],
        ];
    }

    #[Test]
    public function it_registers_a_custom_link_type()
    {
        Link::extend('link-extend-test-basic', TestBasicLinkType::class);

        $this->assertArrayHasKey('link-extend-test-basic', Link::types());

        $type = Link::resolveType('link-extend-test-basic');

        $this->assertInstanceOf(TestBasicLinkType::class, $type);
        $this->assertEquals('link-extend-test-basic', $type->handle());
        $this->assertEquals('Basic Test Type', $type->title());
    }

    #[Test]
    public function it_appends_config_field_items_into_links_config_fields()
    {
        Link::extend('link-extend-test-config', TestConfigFieldsLinkType::class);

        $fields = (new Link)->configFields();

        $this->assertTrue($fields->has('link_extend_test_field'));
        $this->assertEquals('text', $fields->get('link_extend_test_field')->type());
    }

    #[Test]
    public function it_resolves_a_custom_type_through_resolve_redirect()
    {
        Link::extend('link-extend-test-resolve', TestResolvingLinkType::class);

        $resolver = new \Statamic\Routing\ResolveRedirect;

        $this->assertEquals('resolved:the-id', $resolver->item('link-extend-test-resolve::the-id'));
    }

    #[Test]
    public function it_passes_parent_and_localize_through_to_resolve()
    {
        Link::extend('link-extend-test-context', TestContextCapturingLinkType::class);

        $resolver = new \Statamic\Routing\ResolveRedirect;

        $resolver->item('link-extend-test-context::the-id', 'some-parent', true);

        $this->assertEquals(['the-id', 'some-parent', true], TestContextCapturingLinkType::$captured);
    }

    #[Test]
    public function it_includes_a_custom_type_in_preload_when_visible()
    {
        $this->setUpRoutableCollection();

        Link::extend('link-extend-test-visible', TestAlwaysVisibleLinkType::class);

        $fieldtype = (new Link)->setField(new Field('test', ['type' => 'link']));

        $types = $fieldtype->preload()['types'];

        $this->assertArrayHasKey('link-extend-test-visible', $types);
        $this->assertEquals('Always Visible', $types['link-extend-test-visible']['title']);
    }

    #[Test]
    public function it_excludes_a_custom_type_from_preload_when_not_visible()
    {
        $this->setUpRoutableCollection();

        Link::extend('link-extend-test-hidden', TestNeverVisibleLinkType::class);

        $fieldtype = (new Link)->setField(new Field('test', ['type' => 'link']));

        $this->assertArrayNotHasKey('link-extend-test-hidden', $fieldtype->preload()['types']);
    }

    #[Test]
    public function it_excludes_a_custom_type_with_a_null_fieldtype_from_preload()
    {
        $this->setUpRoutableCollection();

        Link::extend('link-extend-test-no-picker', TestNoPickerLinkType::class);

        $fieldtype = (new Link)->setField(new Field('test', ['type' => 'link']));

        $this->assertArrayNotHasKey('link-extend-test-no-picker', $fieldtype->preload()['types']);
    }

    #[Test]
    public function it_includes_custom_type_and_icon_in_pre_process_index()
    {
        Link::extend('link-extend-test-index', TestIndexLinkType::class);

        $fieldtype = (new Link)->setField(new Field('test', ['type' => 'link']));

        $this->assertEquals(
            ['type' => 'link-extend-test-index', 'url' => '/resolved-url', 'icon' => 'custom-icon'],
            $fieldtype->preProcessIndex('link-extend-test-index::the-id')
        );
    }

    private function setUpRoutableCollection(): void
    {
        $this->actingAs(tap(Facades\User::make()->makeSuper())->save());
        tap(Facades\Collection::make('pages')->routes('{slug}'))->sites(['en'])->save();
    }
}

class TestBasicLinkType extends LinkType
{
    protected static ?string $title = 'Basic Test Type';

    public function resolve(string $id, $parent = null, bool $localize = false): mixed
    {
        return "resolved:{$id}";
    }

    public function fieldtype(Field $field): ?array
    {
        return ['type' => 'text'];
    }
}

class TestConfigFieldsLinkType extends LinkType
{
    public function resolve(string $id, $parent = null, bool $localize = false): mixed
    {
        return null;
    }

    public function fieldtype(Field $field): ?array
    {
        return ['type' => 'text'];
    }

    public function configFieldItems(): array
    {
        return [
            'link_extend_test_field' => ['type' => 'text'],
        ];
    }
}

class TestResolvingLinkType extends LinkType
{
    public function resolve(string $id, $parent = null, bool $localize = false): mixed
    {
        return "resolved:{$id}";
    }

    public function fieldtype(Field $field): ?array
    {
        return null;
    }
}

class TestContextCapturingLinkType extends LinkType
{
    public static $captured;

    public function resolve(string $id, $parent = null, bool $localize = false): mixed
    {
        static::$captured = [$id, $parent, $localize];

        return null;
    }

    public function fieldtype(Field $field): ?array
    {
        return null;
    }
}

class TestAlwaysVisibleLinkType extends LinkType
{
    protected static ?string $title = 'Always Visible';

    public function resolve(string $id, $parent = null, bool $localize = false): mixed
    {
        return null;
    }

    public function fieldtype(Field $field): ?array
    {
        return ['type' => 'text'];
    }
}

class TestNeverVisibleLinkType extends LinkType
{
    public function resolve(string $id, $parent = null, bool $localize = false): mixed
    {
        return null;
    }

    public function fieldtype(Field $field): ?array
    {
        return ['type' => 'text'];
    }

    public function visible(Field $field): bool
    {
        return false;
    }
}

class TestNoPickerLinkType extends LinkType
{
    public function resolve(string $id, $parent = null, bool $localize = false): mixed
    {
        return null;
    }

    public function fieldtype(Field $field): ?array
    {
        return null;
    }
}

class TestIndexLinkType extends LinkType
{
    protected ?string $icon = 'custom-icon';

    public function resolve(string $id, $parent = null, bool $localize = false): mixed
    {
        return '/resolved-url';
    }

    public function fieldtype(Field $field): ?array
    {
        return null;
    }
}
