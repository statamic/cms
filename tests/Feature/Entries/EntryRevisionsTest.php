<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Folder;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Statamic\Revisions\Revision;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntryRevisionsTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $dir;

    private $collection;

    public function setUp(): void
    {
        parent::setUp();
        $this->dir = __DIR__.'/tmp';
        config(['statamic.revisions.enabled' => true]);
        $this->collection = tap(Collection::make('blog')->revisionsEnabled(true)->dated(true))->save();
    }

    public function tearDown(): void
    {
        Folder::delete($this->dir);
        parent::tearDown();
    }

    #[Test]
    public function it_gets_revisions()
    {
        $now = Carbon::parse('2017-02-03');
        Carbon::setTestNow($now);
        $this->setTestBlueprint(
            'test',
            [
                'foo' => ['type' => 'text'],
                'bar' => [
                    'type' => 'text',
                    'revisable' => false,
                ],
            ]
        );
        $this->setTestRoles(['test' => ['access cp', 'view blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->published(true)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Original title',
                'foo' => 'bar',
                'bar' => 'foo',
            ])->create();

        tap($entry->makeRevision(), function ($copy) {
            $copy->message('Revision one');
            $copy->date(Carbon::parse('2017-02-01'));
        })->save();

        tap($entry->makeRevision(), function ($copy) {
            $copy->message('Revision two');
            $copy->date(Carbon::parse('2017-02-03'));
        })->save();

        tap($entry->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['title'] = 'Title modified in working copy';
            $attrs['data']['foo'] = 'baz';
            $copy->attributes($attrs);
        })->save();

        $this
            ->actingAs($user)
            ->get($entry->revisionsUrl())
            ->assertOk()
            ->assertJsonPath('0.revisions.0.action', 'revision')
            ->assertJsonPath('0.revisions.0.message', 'Revision one')
            ->assertJsonPath('0.revisions.0.attributes.data.title', 'Original title')
            ->assertJsonPath('0.revisions.0.attributes.item_url', 'http://localhost/cp/collections/blog/entries/1/revisions/'.Carbon::parse('2017-02-01')->timestamp)
            ->assertJsonPath('0.revisions.0.attributes.data.bar', null)

            ->assertJsonPath('1.revisions.0.action', 'working')
            ->assertJsonPath('1.revisions.0.message', null)
            ->assertJsonPath('1.revisions.0.attributes.data.title', 'Title modified in working copy')
            ->assertJsonPath('1.revisions.0.attributes.item_url', null)

            ->assertJsonPath('1.revisions.1.action', 'revision')
            ->assertJsonPath('1.revisions.1.message', 'Revision two')
            ->assertJsonPath('1.revisions.1.attributes.data.title', 'Original title')
            ->assertJsonPath('1.revisions.1.attributes.item_url', 'http://localhost/cp/collections/blog/entries/1/revisions/'.Carbon::parse('2017-02-03')->timestamp);
    }

    #[Test]
    public function it_denies_access_to_revisions_without_permission_to_view_entry()
    {
        $now = Carbon::parse('2017-02-03');
        Carbon::setTestNow($now);
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->published(true)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Original title',
                'foo' => 'bar',
            ])->create();

        tap($entry->makeRevision(), function ($copy) {
            $copy->message('Revision one');
            $copy->date(Carbon::parse('2017-02-01'));
        })->save();

        tap($entry->makeRevision(), function ($copy) {
            $copy->message('Revision two');
            $copy->date(Carbon::parse('2017-02-03'));
        })->save();

        tap($entry->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['title'] = 'Title modified in working copy';
            $attrs['data']['foo'] = 'baz';
            $copy->attributes($attrs);
        })->save();

        $this
            ->actingAs($user)
            ->getJson($entry->revisionsUrl())
            ->assertForbidden();
    }

    #[Test]
    public function it_denies_access_to_a_specific_revision_without_permission_to_view_entry()
    {
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->published(true)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Original title',
                'foo' => 'bar',
            ])->create();

        $revision = tap($entry->makeRevision(), function ($copy) {
            $copy->message('Revision one');
            $copy->date(Carbon::parse('2017-02-01'));
        });
        $revision->save();

        $this
            ->actingAs($user)
            ->getJson($entry->revisionsUrl().'/'.$revision->date()->timestamp)
            ->assertForbidden();
    }

    #[Test]
    public function it_views_a_specific_revision()
    {
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'view blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->published(true)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Original title',
                'foo' => 'bar',
            ])->create();

        $revision = tap($entry->makeRevision(), function ($copy) {
            $copy->message('Revision one');
            $copy->date(Carbon::parse('2017-02-01'));
        });
        $revision->save();

        $this
            ->actingAs($user)
            ->getJson($entry->revisionsUrl().'/'.$revision->date()->timestamp)
            ->assertOk();
    }

    #[Test]
    public function it_publishes_an_entry()
    {
        $now = Carbon::parse('2017-02-03');
        Carbon::setTestNow($now);
        $this->setTestBlueprint(
            'test',
            [
                'foo' => ['type' => 'text'],
                'bar' => [
                    'type' => 'text',
                    'revisable' => false,
                ],
            ]
        );
        $this->setTestRoles(['test' => ['access cp', 'publish blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->published(false)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
                'bar' => 'foo',
            ])->create();

        tap($entry->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['foo'] = 'foo modified in working copy';
            $attrs['date'] = 1482624000; // 2016-12-25
            $copy->attributes($attrs);
        })->save();

        $this->assertFalse($entry->published());
        $this->assertCount(0, $entry->revisions());

        $this
            ->actingAs($user)
            ->publish($entry, ['message' => 'Test!'])
            ->assertOk();

        $entry = Entry::find($entry->id());
        $this->assertEquals([
            'blueprint' => 'test',
            'title' => 'Title',
            'foo' => 'foo modified in working copy',
            'bar' => 'foo',
            'updated_at' => $now->timestamp,
            'updated_by' => $user->id(),
        ], $entry->data()->all());
        $this->assertTrue($entry->published());
        $this->assertCount(1, $entry->revisions());
        $this->assertEquals('2016-12-25', $entry->date()->format('Y-m-d'));
        $revision = $entry->latestRevision();
        $this->assertEquals([
            'published' => true,
            'slug' => 'test',
            'id' => '1',
            'date' => 1482624000,
            'data' => [
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'foo modified in working copy',
            ],
        ], $revision->attributes());
        $this->assertEquals('user-1', $revision->user()->id());
        $this->assertEquals('Test!', $revision->message());
        $this->assertEquals('publish', $revision->action());
        $this->assertFalse($entry->hasWorkingCopy());
    }

    #[Test]
    public function it_unpublishes_an_entry()
    {
        $now = Carbon::parse('2017-02-03');
        Carbon::setTestNow($now);
        $this->setTestBlueprint(
            'test',
            [
                'foo' => ['type' => 'text'],
                'bar' => [
                    'type' => 'text',
                    'revisable' => false,
                ],
            ]
        );
        $this->setTestRoles(['test' => ['access cp', 'publish blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->published(true)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
                'bar' => 'foo',
            ])->create();

        $this->assertTrue($entry->published());
        $this->assertCount(0, $entry->revisions());

        $this
            ->actingAs($user)
            ->unpublish($entry, ['message' => 'Test!'])
            ->assertOk();

        $entry = Entry::find($entry->id());
        $this->assertEquals([
            'blueprint' => 'test',
            'title' => 'Title',
            'foo' => 'bar',
            'bar' => 'foo',
            'updated_at' => $now->timestamp,
            'updated_by' => $user->id(),
        ], $entry->data()->all());
        $this->assertFalse($entry->published());
        $this->assertCount(1, $entry->revisions());
        $revision = $entry->latestRevision();
        $this->assertEquals([
            'published' => false,
            'slug' => 'test',
            'id' => '1',
            'date' => 1293235200, // 2010-12-25
            'data' => [
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ],
        ], $revision->attributes());
        $this->assertEquals('user-1', $revision->user()->id());
        $this->assertEquals('Test!', $revision->message());
        $this->assertEquals('unpublish', $revision->action());
    }

    #[Test]
    public function it_denies_creating_a_revision_without_permission_to_edit_entry()
    {
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'view blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->published(false)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ])->create();

        tap($entry->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['foo'] = 'foo modified in working copy';
            $copy->attributes($attrs);
        })->save();

        $this
            ->actingAs($user)
            ->postJson($entry->createRevisionUrl(), ['message' => 'Test!'])
            ->assertForbidden();
    }

    #[Test]
    public function it_creates_a_revision()
    {
        $this->setTestBlueprint(
            'test',
            [
                'foo' => ['type' => 'text'],
                'bar' => [
                    'type' => 'text',
                    'revisable' => false,
                ],
            ]
        );
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->published(false)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
                'bar' => 'foo',
            ])->create();

        tap($entry->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['foo'] = 'foo modified in working copy';
            $copy->attributes($attrs);
        })->save();

        $this->assertFalse($entry->published());
        $this->assertCount(0, $entry->revisions());

        $this
            ->actingAs($user)
            ->post($entry->createRevisionUrl(), ['message' => 'Test!'])
            ->assertOk();

        $entry = Entry::find($entry->id());
        $this->assertEquals([
            'blueprint' => 'test',
            'title' => 'Title',
            'foo' => 'bar',
            'bar' => 'foo',
        ], $entry->data()->all());
        $this->assertFalse($entry->published());
        $this->assertCount(1, $entry->revisions());
        $revision = $entry->latestRevision();
        $this->assertEquals([
            'published' => false,
            'slug' => 'test',
            'id' => '1',
            'date' => 1293235200, // 2010-12-25
            'data' => [
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'foo modified in working copy',
            ],
        ], $revision->attributes());
        $this->assertEquals('user-1', $revision->user()->id());
        $this->assertEquals('Test!', $revision->message());
        $this->assertEquals('revision', $revision->action());
        $this->assertTrue($entry->hasWorkingCopy());
    }

    #[Test]
    public function it_denies_restoring_a_revision_without_permission_to_edit_entry()
    {
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'view blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $entry = EntryFactory::id('123')
            ->slug('test')
            ->collection('blog')
            ->published(false)
            ->data([
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ])->create();

        $this
            ->actingAs($user)
            ->postJson($entry->restoreRevisionUrl(), ['revision' => '1553546421'])
            ->assertForbidden();
    }

    #[Test]
    public function it_restores_a_published_entrys_working_copy_to_another_revision()
    {
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $revision = tap((new Revision)
            ->key('collections/blog/en/123')
            ->date(Carbon::createFromTimestamp('1553546421', config('app.timezone')))
            ->attributes([
                'published' => false,
                'slug' => 'existing-slug',
                'date' => 1246665600, // 2009-07-04
                'data' => ['foo' => 'existing foo'],
            ]))->save();

        $revision->toWorkingCopy()->save();

        $entry = EntryFactory::id('123')
            ->slug('test')
            ->collection('blog')
            ->published(true)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ])->create();

        $workingCopy = tap($entry->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['foo'] = 'foo modified in working copy';
            $attrs['date'] = 1482624000; // 2016-12-25
            $copy->attributes($attrs);
        });
        $workingCopy->save();

        $this->assertTrue($entry->published());
        $this->assertCount(1, $entry->revisions());
        $this->assertEquals('bar', $entry->get('foo'));
        $this->assertEquals('foo modified in working copy', $entry->fromWorkingCopy()->get('foo'));
        $this->assertEquals('2010-12-25', $entry->date()->format('Y-m-d'));
        $this->assertEquals('2016-12-25', $entry->fromWorkingCopy()->date()->format('Y-m-d'));

        $this
            ->actingAs($user)
            ->restore($entry, ['revision' => '1553546421'])
            ->assertOk()
            ->assertSessionHas('success');

        $entry = Entry::find($entry->id());
        $this->assertEquals('test', $entry->slug());
        $this->assertEquals('bar', $entry->get('foo'));
        $this->assertEquals('existing foo', $entry->fromWorkingCopy()->get('foo'));
        $this->assertEquals('2010-12-25', $entry->date()->format('Y-m-d'));
        $this->assertEquals('2009-07-04', $entry->fromWorkingCopy()->date()->format('Y-m-d'));
        $this->assertTrue($entry->published());
        $this->assertTrue($entry->hasWorkingCopy());
        $this->assertCount(1, $entry->revisions());
    }

    #[Test]
    public function it_restores_an_unpublished_entrys_contents_to_another_revision()
    {
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $revision = tap((new Revision)
            ->key('collections/blog/en/123')
            ->date(Carbon::createFromTimestamp('1553546421', config('app.timezone')))
            ->attributes([
                'published' => true,
                'slug' => 'existing-slug',
                'data' => ['foo' => 'existing foo'],
            ]))->save();

        $revision->toWorkingCopy()->save();

        $entry = EntryFactory::id('123')
            ->slug('test')
            ->collection('blog')
            ->published(false)
            ->data([
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ])->create();

        $this->assertFalse($entry->published());
        $this->assertCount(1, $entry->revisions());
        $this->assertEquals('bar', $entry->get('foo'));

        $this
            ->actingAs($user)
            ->restore($entry, ['revision' => '1553546421'])
            ->assertOk()
            ->assertSessionHas('success');

        $entry = Entry::find($entry->id());
        $this->assertEquals('existing-slug', $entry->slug());
        $this->assertEquals('existing foo', $entry->get('foo'));
        $this->assertFalse($entry->published()); // everything except publish state gets restored
        $this->assertCount(1, $entry->revisions());
    }

    #[Test]
    public function it_keeps_non_revisable_fields_when_restoring_an_unpublished_entrys_contents()
    {
        $this->setTestBlueprint('test', [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'text', 'revisable' => false],
        ]);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        tap((new Revision)
            ->key('collections/blog/en/123')
            ->date(Carbon::createFromTimestamp('1553546421', config('app.timezone')))
            ->attributes([
                'published' => true,
                'slug' => 'existing-slug',
                'data' => ['foo' => 'existing foo'],
            ]))->save();

        $entry = EntryFactory::id('123')
            ->slug('test')
            ->collection('blog')
            ->published(false)
            ->data([
                'blueprint' => 'test',
                'foo' => 'bar',
                'bar' => 'not tracked by revisions',
                'stale' => 'not in the revision',
            ])->create();

        $this
            ->actingAs($user)
            ->restore($entry, ['revision' => '1553546421'])
            ->assertOk()
            ->assertSessionHas('success');

        $entry = Entry::find($entry->id());
        $this->assertEquals('existing foo', $entry->get('foo'));
        $this->assertEquals('not tracked by revisions', $entry->get('bar'));
        $this->assertFalse($entry->has('stale'));
    }

    private function publish($entry, $payload)
    {
        return $this->post($entry->publishUrl(), $payload);
    }

    private function unpublish($entry, $payload)
    {
        return $this->post($entry->unpublishUrl(), $payload);
    }

    private function restore($entry, $payload)
    {
        return $this->post($entry->restoreRevisionUrl(), $payload);
    }

    #[Test]
    public function localized_entry_with_non_localizable_date_gets_origin_date_when_reconstructed_from_revision()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        $this->setTestBlueprint('test', [
            'foo' => ['type' => 'text'],
            'date' => ['type' => 'date', 'localizable' => false],
        ]);
        $this->setTestRoles(['test' => ['access cp', 'publish blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $this->collection->sites(['en', 'fr'])->save();

        $origin = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->locale('en')
            ->published(true)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ])->create();

        $localized = EntryFactory::id('2')
            ->slug('test')
            ->collection('blog')
            ->locale('fr')
            ->origin($origin)
            ->published(true)
            ->data(['blueprint' => 'test'])
            ->create();

        $this->assertEquals('2010-12-25', $origin->date()->format('Y-m-d'));
        $this->assertEquals('2010-12-25', $localized->date()->format('Y-m-d'));

        tap($localized->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['foo'] = 'foo modified in localized working copy';
            $copy->attributes($attrs);
        })->save();

        $this->assertTrue($localized->hasWorkingCopy());
        $this->assertEquals('2010-12-25', $localized->fromWorkingCopy()->date()->format('Y-m-d'));

        tap($origin->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['date'] = Carbon::parse('2020-06-15')->timestamp;
            $copy->attributes($attrs);
        })->save();

        $this
            ->actingAs($user)
            ->publish($origin, ['message' => 'Publish origin with new date'])
            ->assertOk();

        $origin = Entry::find('1');
        $this->assertEquals('2020-06-15', $origin->date()->format('Y-m-d'));

        $localized = Entry::find('2');
        $this->assertEquals('2020-06-15', $localized->date()->format('Y-m-d'));

        $this->assertEquals(
            '2020-06-15',
            $localized->fromWorkingCopy()->date()->format('Y-m-d'),
            'Localized entry reconstructed from working copy should use origin\'s current date when date field is not localizable'
        );
    }

    #[Test]
    public function localized_entry_with_localizable_date_keeps_its_own_date_when_reconstructed_from_revision()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        $this->setTestBlueprint('test', [
            'foo' => ['type' => 'text'],
            'date' => ['type' => 'date', 'localizable' => true],
        ]);
        $this->setTestRoles(['test' => ['access cp', 'publish blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $this->collection->sites(['en', 'fr'])->save();

        $origin = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->locale('en')
            ->published(true)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ])->create();

        $localized = EntryFactory::id('2')
            ->slug('test')
            ->collection('blog')
            ->locale('fr')
            ->origin($origin)
            ->published(true)
            ->date('2015-03-10')
            ->data(['blueprint' => 'test'])
            ->create();

        $this->assertEquals('2010-12-25', $origin->date()->format('Y-m-d'));
        $this->assertEquals('2015-03-10', $localized->date()->format('Y-m-d'));

        tap($localized->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['foo'] = 'foo modified in localized working copy';
            $copy->attributes($attrs);
        })->save();

        $this->assertTrue($localized->hasWorkingCopy());
        $this->assertEquals('2015-03-10', $localized->fromWorkingCopy()->date()->format('Y-m-d'));

        tap($origin->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['date'] = Carbon::parse('2020-06-15')->timestamp;
            $copy->attributes($attrs);
        })->save();

        $this
            ->actingAs($user)
            ->publish($origin, ['message' => 'Publish origin with new date'])
            ->assertOk();

        $origin = Entry::find('1');
        $this->assertEquals('2020-06-15', $origin->date()->format('Y-m-d'));

        $localized = Entry::find('2');
        $this->assertEquals('2015-03-10', $localized->date()->format('Y-m-d'));

        $this->assertEquals(
            '2015-03-10',
            $localized->fromWorkingCopy()->date()->format('Y-m-d'),
            'Localized entry reconstructed from working copy should keep its own date when date field is localizable'
        );
    }

    #[Test]
    public function revision_localizations_only_includes_authorized_sites()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
            'de' => ['url' => 'http://localhost/de/', 'locale' => 'de'],
        ]);

        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => [
            'access cp',
            'view blog entries',
            'access en site',
            'access fr site',
            // Note: no 'access de site' permission
        ]]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $this->collection->sites(['en', 'fr', 'de'])->save();

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->locale('en')
            ->published(true)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Original title',
                'foo' => 'bar',
            ])->create();

        $revision = tap($entry->makeRevision(), function ($copy) {
            $copy->message('Revision one');
            $copy->date(Carbon::parse('2017-02-01'));
        });
        $revision->save();

        $response = $this
            ->actingAs($user)
            ->getJson($entry->revisionsUrl().'/'.$revision->date()->timestamp)
            ->assertOk();

        $localizations = $response->json('localizations');

        // User should only see en and fr sites, not de
        $this->assertCount(2, $localizations);
        $this->assertEquals(['en', 'fr'], array_column($localizations, 'handle'));
    }

    private function setTestBlueprint($handle, $fields)
    {
        $blueprint = Blueprint::makeFromFields($fields)->setHandle($handle);

        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('find')->with('test')->andReturn($blueprint);
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect(['test' => $blueprint]));
    }
}
