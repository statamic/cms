<?php

namespace Tests\Stache;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ColdStacheUriTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function simulateColdStache(): void
    {
        Stache::clear();
        Blink::flush();
    }

    #[Test]
    public function entries_have_uris_on_cold_stache_with_single_site_structured_collection()
    {
        $collection = Collection::make('pages')
            ->routes('{parent_uri}/{slug}')
            ->structureContents(['root' => true]);
        $collection->save();

        EntryFactory::id('alfa-id')->collection('pages')->slug('alfa')->data(['title' => 'Alfa'])->create();
        EntryFactory::id('bravo-id')->collection('pages')->slug('bravo')->data(['title' => 'Bravo'])->create();

        $this->simulateColdStache();

        // Loading a non-URI index first triggers re-entrant URI index loading via getCachedItem().
        Stache::store('entries')->store('pages')->index('site')->load();

        $entries = Entry::query()
            ->where('collection', 'pages')
            ->whereNotNull('uri')
            ->whereStatus('published')
            ->get();

        $this->assertCount(2, $entries);
    }

    #[Test]
    public function entries_have_uris_on_cold_stache_with_multisite_structured_collection()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en_US'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr_FR'],
        ]);

        $collection = Collection::make('pages')
            ->routes('{parent_uri}/{slug}')
            ->structureContents(['root' => true])
            ->sites(['en', 'fr']);
        $collection->save();

        EntryFactory::id('alfa-id')->locale('en')->collection('pages')->slug('alfa')->data(['title' => 'Alfa'])->create();
        EntryFactory::id('bravo-id')->locale('fr')->collection('pages')->slug('bravo')->origin('alfa-id')->data(['title' => 'Bravo'])->create();

        $this->simulateColdStache();

        Stache::store('entries')->store('pages')->index('site')->load();

        $entries = Entry::query()
            ->where('collection', 'pages')
            ->whereNotNull('uri')
            ->whereStatus('published')
            ->get();

        $this->assertCount(2, $entries);
    }
}
