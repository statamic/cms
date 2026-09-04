<?php

namespace Tests\Listeners;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Structures\CollectionTreeRepository;
use Statamic\Facades\Collection;
use Statamic\Facades\Config;
use Statamic\Facades\Entry;
use Statamic\Facades\File;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Nav;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\YAML;
use Statamic\Sites\Sites;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CleanupSiteContentTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        File::put(resource_path('sites.yaml'), YAML::dump([
            'en' => ['name' => 'English', 'url' => '/', 'locale' => 'en_US'],
            'fr' => ['name' => 'French', 'url' => '/fr/', 'locale' => 'fr_FR'],
            'de' => ['name' => 'German', 'url' => '/de/', 'locale' => 'de_DE'],
        ]));

        Site::swap(new Sites);
        Config::set('statamic.system.multisite', true);
    }

    private function deleteSite(string $handle): void
    {
        Site::setSites(collect(Site::config())->forget($handle)->all())->save();
    }

    #[Test]
    public function it_deletes_content_belonging_to_a_removed_site()
    {
        $blog = tap(Collection::make('blog')->sites(['en', 'de']))->save();
        Entry::make()->id('en-1')->locale('en')->collection('blog')->slug('en-1')->data(['title' => 'EN 1'])->save();
        Entry::make()->id('de-1')->locale('de')->collection('blog')->slug('de-1')->data(['title' => 'DE 1'])->save();

        $pages = tap(Collection::make('pages')->sites(['en', 'de'])->structureContents(['root' => true]))->save();
        Entry::make()->id('p-en')->locale('en')->collection('pages')->slug('p-en')->data(['title' => 'P EN'])->save();
        Entry::make()->id('p-de')->locale('de')->collection('pages')->slug('p-de')->data(['title' => 'P DE'])->save();
        $pages->structure()->in('en')->tree([['entry' => 'p-en']])->save();
        $pages->structure()->in('de')->tree([['entry' => 'p-de']])->save();

        $nav = tap(Nav::make('main'))->save();
        $nav->makeTree('en', [['id' => 'a']])->save();
        $nav->makeTree('de', [['id' => 'b']])->save();

        $tags = tap(Taxonomy::make('tags')->sites(['en', 'de']))->save();
        $alpha = Term::make()->taxonomy('tags')->slug('alpha');
        $alpha->dataForLocale('en', ['title' => 'Alpha EN']);
        $alpha->dataForLocale('de', ['title' => 'Alpha DE']);
        $alpha->save();

        $company = tap(GlobalSet::make('company')->sites(['en' => null, 'de' => 'en']))->save();
        $company->in('en')->data(['name' => 'ACME'])->save();
        $company->in('de')->data(['name' => 'ACME DE'])->save();

        $this->deleteSite('de');

        $this->assertEquals(0, Entry::query()->where('site', 'de')->count());
        $this->assertNotNull(Entry::find('en-1'));
        $this->assertNotNull(Entry::find('p-en'));

        $this->assertEquals(['en'], Collection::findByHandle('blog')->sites()->all());
        $this->assertEquals(['en'], Collection::findByHandle('pages')->sites()->all());
        $this->assertNull(Collection::findByHandle('pages')->structure()->in('de'));
        $this->assertNull(app(CollectionTreeRepository::class)->find('pages', 'de'));

        $this->assertNull(Nav::findByHandle('main')->in('de'));
        $this->assertNotNull(Nav::findByHandle('main')->in('en'));

        $this->assertEquals(['en'], Taxonomy::findByHandle('tags')->sites()->all());
        $alpha = Term::find('tags::alpha')?->term();
        $this->assertNotNull($alpha);
        $this->assertTrue($alpha->dataForLocale('de')->isEmpty());
        $this->assertEquals('Alpha EN', $alpha->dataForLocale('en')->get('title'));

        $company = GlobalSet::findByHandle('company');
        $this->assertNull($company->in('de'));
        $this->assertFalse($company->sites()->contains('de'));
    }

    #[Test]
    public function it_flattens_localizations_whose_origin_was_in_the_removed_site()
    {
        tap(Collection::make('blog')->sites(['en', 'de']))->save();

        Entry::make()->id('origin')->locale('de')->collection('blog')->slug('origin')
            ->data(['title' => 'Origin DE', 'body' => 'shared body'])->save();
        Entry::make()->id('loc')->locale('en')->collection('blog')->slug('loc')->origin('origin')
            ->data(['title' => 'Localized EN'])->save();

        $this->deleteSite('de');

        $this->assertNull(Entry::find('origin'));

        $loc = Entry::find('loc');
        $this->assertNotNull($loc);
        $this->assertNull($loc->origin());
        $this->assertEquals('Localized EN', $loc->value('title'));
        $this->assertEquals('shared body', $loc->value('body'));
    }

    #[Test]
    public function it_can_delete_the_default_site()
    {
        tap(Collection::make('blog')->sites(['en', 'fr', 'de']))->save();
        Entry::make()->id('fr-1')->locale('fr')->collection('blog')->slug('fr-1')->data(['title' => 'FR 1'])->save();
        Entry::make()->id('en-1')->locale('en')->collection('blog')->slug('en-1')->data(['title' => 'EN 1'])->save();

        $tags = tap(Taxonomy::make('tags')->sites(['en', 'fr', 'de']))->save();
        $alpha = Term::make()->taxonomy('tags')->slug('alpha');
        $alpha->dataForLocale('en', ['title' => 'Alpha EN']);
        $alpha->dataForLocale('de', ['title' => 'Alpha DE']);
        $alpha->save();

        $this->deleteSite('en');

        $this->assertEquals('fr', Site::default()->handle());
        $this->assertEquals(0, Entry::query()->where('site', 'en')->count());
        $this->assertNotNull(Entry::find('fr-1'));
        $this->assertEquals(['fr', 'de'], Collection::findByHandle('blog')->sites()->all());

        // The term's root locale (en) was removed, so a surviving localization is
        // promoted to the new default locale to keep the term file valid.
        $alpha = Term::find('tags::alpha')?->term();
        $this->assertNotNull($alpha);
        $this->assertEquals('Alpha DE', $alpha->dataForLocale('de')->get('title'));
        $this->assertEquals('Alpha DE', $alpha->dataForLocale('fr')->get('title'));
    }
}
