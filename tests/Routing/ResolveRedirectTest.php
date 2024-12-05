<?php

namespace Tests\Routing;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Statamic\Fieldtypes\Link;
use Statamic\Fieldtypes\Link\ArrayableLink;
use Statamic\Routing\ResolveRedirect;
use Statamic\Structures\Page;
use Statamic\Structures\Pages;
use Tests\TestCase;

class ResolveRedirectTest extends TestCase
{
    #[Test]
    public function it_resolves_standard_redirects()
    {
        $resolver = new ResolveRedirect;

        $this->assertEquals('http://test.com', $resolver('http://test.com'));
        $this->assertEquals('https://test.com', $resolver('https://test.com'));
        $this->assertEquals('/test', $resolver('/test'));
        $this->assertEquals('test', $resolver('test'));
        $this->assertSame(404, $resolver('404'));
        $this->assertSame(404, $resolver(404));
        $this->assertSame('4-oh-4', $resolver('4-oh-4')); // strings with numbers won't become ints
        $this->assertNull($resolver(null));
    }

    #[Test]
    public function it_cant_resolve_a_first_child_without_a_parent()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot resolve a page\'s child redirect without providing a page.');

        $resolver = new ResolveRedirect;

        $this->assertEquals('/page/child', $resolver('@child'));
    }

    #[Test]
    public function it_cannot_resolve_a_first_child_redirect_if_the_parent_is_not_a_page()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot resolve a page\'s child redirect without providing a page.');

        $resolver = new ResolveRedirect;

        $this->assertEquals('/page/child', $resolver('@child', 'not a page object'));
    }

    #[Test]
    public function it_resolves_first_child()
    {
        $resolver = new ResolveRedirect;

        $child = Mockery::mock(Page::class);
        $child->shouldReceive('url')->andReturn('/parent/first-child');

        $children = Mockery::mock(Pages::class);
        $children->shouldReceive('all')->andReturn(collect([$child]));

        $parent = Mockery::mock(Page::class);
        $parent->shouldReceive('isRoot')->andReturnFalse();
        $parent->shouldReceive('pages')->andReturn($children);

        $this->assertEquals('/parent/first-child', $resolver('@child', $parent));
        $this->assertEquals($child, $resolver->item('@child', $parent));
    }

    #[Test]
    public function it_resolves_first_child_through_an_entry()
    {
        $resolver = new ResolveRedirect;

        $child = Mockery::mock(Page::class);
        $child->shouldReceive('url')->andReturn('/parent/first-child');

        $children = Mockery::mock(Pages::class);
        $children->shouldReceive('all')->andReturn(collect([$child]));

        $parentPage = Mockery::mock(Page::class);
        $parentPage->shouldReceive('isRoot')->andReturnFalse();
        $parentPage->shouldReceive('pages')->andReturn($children);

        $parent = Mockery::mock(Entry::class);
        $parent->shouldReceive('page')->andReturn($parentPage);

        $this->assertEquals('/parent/first-child', $resolver('@child', $parent));
        $this->assertEquals($child, $resolver->item('@child', $parent));
    }

    #[Test]
    public function it_resolves_a_first_child_redirect_when_its_a_root_page()
    {
        // When the parent is a root page of a structure, the first child should
        // be considered the first non-root page. In the UI, it would look like
        // its first sibling.
        //
        //  |-- Root            <-- The 'parent'
        //  |-- Some Page       <-- Redirects here.
        //  |   |-- Child
        //  |-- Another Page
        //      |-- Child
        //

        $resolver = new ResolveRedirect;

        $root = Mockery::mock(Page::class);
        $child = Mockery::mock(Page::class)->shouldReceive('url')->andReturn('/parent/first-child')->getMock();
        $children = Mockery::mock(Pages::class)->shouldReceive('all')->andReturn(collect([$root, $child]))->getMock();
        $tree = Mockery::mock()->shouldReceive('pages')->andReturn($children)->getMock();
        $structure = Mockery::mock()->shouldreceive('in')->with('en')->andReturn($tree)->getMock();

        $root->shouldReceive('isRoot')->andReturnTrue();
        $root->shouldReceive('locale')->andReturn('en');
        $root->shouldReceive('structure')->andReturn($structure);

        $this->assertEquals('/parent/first-child', $resolver('@child', $root));
        $this->assertEquals($child, $resolver->item('@child', $root));
    }

    #[Test]
    public function a_parent_without_a_child_resolves_to_a_404()
    {
        $resolver = new ResolveRedirect;

        $pages = Mockery::mock(Pages::class);
        $pages->shouldReceive('all')->andReturn(collect([]));

        $parent = Mockery::mock(Page::class);
        $parent->shouldReceive('isRoot')->andReturnFalse();
        $parent->shouldReceive('pages')->andReturn($pages);

        $this->assertSame(404, $resolver('@child', $parent));
        $this->assertSame(null, $resolver->item('@child', $parent));
    }

    #[Test]
    public function it_resolves_references_to_entries()
    {
        $resolver = new ResolveRedirect;

        $entry = Mockery::mock(Entry::class)->shouldReceive('url')->once()->andReturn('/the-entry')->getMock();
        Facades\Entry::shouldReceive('find')->with('123')->twice()->andReturn($entry);

        $this->assertEquals('/the-entry', $resolver('entry::123'));
        $this->assertEquals($entry, $resolver->item('entry::123'));
    }

    #[Test]
    public function it_resolves_references_to_entries_localized()
    {
        $resolver = new ResolveRedirect;

        $parentEntry = Mockery::mock(Entry::class);
        $frenchEntry = Mockery::mock(Entry::class)->shouldReceive('url')->once()->andReturn('/le-entry')->getMock();
        $defaultEntry = Mockery::mock(Entry::class)->shouldReceive('in')->twice()->andReturn($frenchEntry)->getMock();
        Facades\Entry::shouldReceive('find')->with('123')->twice()->andReturn($defaultEntry);

        $this->assertEquals('/le-entry', $resolver('entry::123', $parentEntry, true));
        $this->assertEquals($frenchEntry, $resolver->item('entry::123', $parentEntry, true));
    }

    #[Test]
    public function it_resolves_references_to_entries_localized_with_fallback()
    {
        $resolver = new ResolveRedirect;

        $parentEntry = Mockery::mock(Entry::class);
        $entry = Mockery::mock(Entry::class);
        $entry->shouldReceive('in')->twice()->andReturn(null);
        $entry->shouldReceive('url')->once()->andReturn('/the-entry');
        Facades\Entry::shouldReceive('find')->with('123')->twice()->andReturn($entry);

        $this->assertEquals('/the-entry', $resolver('entry::123', $parentEntry, true));
        $this->assertEquals($entry, $resolver->item('entry::123', $parentEntry, true));
    }

    #[Test]
    public function it_resolves_references_to_assets()
    {
        $resolver = new ResolveRedirect;

        $asset = Mockery::mock(Asset::class)->shouldReceive('url')->once()->andReturn('/assets/foo/bar/baz.jpg')->getMock();
        Facades\Asset::shouldReceive('find')->with('foo::bar/baz.jpg')->twice()->andReturn($asset);

        $this->assertEquals('/assets/foo/bar/baz.jpg', $resolver('asset::foo::bar/baz.jpg'));
        $this->assertEquals($asset, $resolver->item('asset::foo::bar/baz.jpg'));
    }

    #[Test]
    public function unknown_entry_ids_resolve_to_404()
    {
        $resolver = new ResolveRedirect;

        Facades\Entry::shouldReceive('find')->with('123')->twice()->andReturnNull();

        $this->assertSame(404, $resolver('entry::123'));
        $this->assertSame(null, $resolver->item('entry::123'));
    }

    #[Test]
    public function it_can_invoke_the_class_or_call_resolve()
    {
        $resolve = $this->partialMock(ResolveRedirect::class);

        $resolve->shouldReceive('resolve')->once()->with('foo', 'bar', false)->andReturn('hello');

        $this->assertEquals('hello', $resolve('foo', 'bar'));
    }

    #[Test]
    public function it_can_resolve_a_group_field_with_url()
    {
        // When using the "Link" blueprint, the `redirect` field would be a `group` type
        // contain `url` and `code` subfields. The `url` subfield is a `link` type.
        // When resolving the redirect, the augmented value of the group field
        // will be passed in which is a `Values` object.

        $resolver = new ResolveRedirect;

        $fieldtype = Mockery::mock(Link::class)->makePartial()->shouldReceive('augment')->andReturn(new ArrayableLink('/test'))->getMock();

        $value = new Values([
            'url' => new Value('/test', 'url', $fieldtype),
            'code' => '', // irrelevant for this test
        ]);

        $this->assertEquals('/test', $resolver($value));
        $this->assertEquals('/test', $resolver->item($value));
    }

    #[Test]
    public function it_can_resolve_a_group_field_with_entry()
    {
        // Same as above, but in this case, an entry was selected in the nested link field.

        $resolver = new ResolveRedirect;

        $entry = Mockery::mock(Entry::class)->shouldReceive('url')->once()->andReturn('/the-entry')->getMock();

        $fieldtype = Mockery::mock(Link::class)->makePartial()->shouldReceive('augment')->andReturn(new ArrayableLink($entry))->getMock();

        $value = new Values([
            'url' => new Value('entry::123', 'url', $fieldtype),
            'code' => '', // irrelevant for this test
        ]);

        $this->assertEquals('/the-entry', $resolver($value));
        $this->assertEquals($entry, $resolver->item($value));
    }

    #[Test]
    public function it_can_resolve_arrays_with_url_and_code()
    {
        // Same as above, but without a blueprint field.
        // Maybe a user put an array directly in their data.

        $resolver = new ResolveRedirect;

        $arr = ['url' => '/test', 'code' => 301];
        $this->assertEquals('/test', $resolver($arr));
        $this->assertEquals('/test', $resolver->item($arr));
    }
}
