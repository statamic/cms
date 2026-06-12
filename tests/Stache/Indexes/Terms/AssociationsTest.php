<?php

namespace Tests\Stache\Indexes\Terms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssociationsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_builds_associations_from_entries_in_linked_collections(): void
    {
        Taxonomy::make('tags')->save();

        Collection::make('blog')
            ->sites(['en'])
            ->taxonomies(['tags'])
            ->save();

        Entry::make()->id('entry-1')->locale('en')->collection('blog')->slug('one')->data(['tags' => ['alfa', 'bravo']])->save();
        Entry::make()->id('entry-2')->locale('en')->collection('blog')->slug('two')->data(['tags' => ['alfa']])->save();
        Entry::make()->id('entry-3')->locale('en')->collection('blog')->slug('three')->data(['title' => 'No tags'])->save();

        $associations = Stache::store('terms')->store('tags')->index('associations');
        $associations->update();

        $items = collect($associations->items()->all());

        $this->assertCount(3, $items);

        $alfaItems = $items->where('slug', 'alfa')->values();
        $this->assertCount(2, $alfaItems);
        $this->assertTrue($alfaItems->pluck('entry')->contains('entry-1'));
        $this->assertTrue($alfaItems->pluck('entry')->contains('entry-2'));

        $bravoItems = $items->where('slug', 'bravo')->values();
        $this->assertCount(1, $bravoItems);
        $this->assertEquals('entry-1', $bravoItems->first()['entry']);

        $items->each(function ($item) {
            $this->assertEquals('blog', $item['collection']);
            $this->assertEquals('en', $item['site']);
        });
    }

    #[Test]
    public function it_builds_associations_across_multiple_collections(): void
    {
        Taxonomy::make('tags')->save();

        Collection::make('blog')->sites(['en'])->taxonomies(['tags'])->save();
        Collection::make('news')->sites(['en'])->taxonomies(['tags'])->save();

        Entry::make()->id('blog-1')->locale('en')->collection('blog')->slug('blog-one')->data(['tags' => ['alfa']])->save();
        Entry::make()->id('news-1')->locale('en')->collection('news')->slug('news-one')->data(['tags' => ['alfa']])->save();

        $associations = Stache::store('terms')->store('tags')->index('associations');
        $associations->update();

        $alfaItems = collect($associations->items()->all())->where('slug', 'alfa')->values();

        $this->assertCount(2, $alfaItems);
        $this->assertTrue($alfaItems->pluck('collection')->contains('blog'));
        $this->assertTrue($alfaItems->pluck('collection')->contains('news'));
    }

    #[Test]
    public function it_returns_empty_when_no_entries_have_the_taxonomy(): void
    {
        Taxonomy::make('tags')->save();

        Collection::make('blog')->sites(['en'])->taxonomies(['tags'])->save();

        Entry::make()->id('entry-1')->locale('en')->collection('blog')->slug('one')->data(['title' => 'No tags'])->save();

        $associations = Stache::store('terms')->store('tags')->index('associations');
        $associations->update();

        $this->assertEmpty($associations->items()->all());
    }
}
