<?php

namespace Tests\Stache\Cache;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Stache\Cache\CompiledStore;
use Statamic\Stache\Cache\ItemCodec;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CompiledStoreTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private string $directory;

    public function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().'/statamic-compiled-store-'.bin2hex(random_bytes(6));

        Collection::make('blog')->dated(true)->save();
    }

    public function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->directory);

        parent::tearDown();
    }

    /** A store as another process would have it: nothing read yet, nothing waiting to be written. */
    private function store(int $buckets = 32): CompiledStore
    {
        return new CompiledStore($this->directory, $buckets);
    }

    private function entry($id, $title = 'Title')
    {
        return EntryFactory::id($id)->collection('blog')->slug($id)->date('2024-03-01')->data([
            'title' => $title,
            'tags' => ['one', 'two'],
            'nested' => ['a' => ['b' => [1, 2.5, true, null, "it's \"quoted\" \\ and\nmultiline"]]],
        ])->make();
    }

    #[Test]
    public function it_keeps_plain_values_entries_and_other_objects()
    {
        $store = $this->store();

        $store->forever('stache::indexes::entries::blog::title', ['a' => 'Alfa', 'b' => 'Bravo']);
        $store->forever('stache::items::entries::blog::one', $this->entry('one'));
        $store->forever('stache::items::terms::tags::en::news', $object = (object) ['title' => 'News']);
        $store->forever('counter', 3);

        foreach ([$store, tap($store)->persist(), $this->store()] as $reader) {
            $this->assertSame(['a' => 'Alfa', 'b' => 'Bravo'], $reader->get('stache::indexes::entries::blog::title'));
            $this->assertEquals(unserialize(serialize($this->entry('one'))), $reader->get('stache::items::entries::blog::one'));
            $this->assertEquals($object, $reader->get('stache::items::terms::tags::en::news'));
            $this->assertSame(3, $reader->get('counter'));
            $this->assertNull($reader->get('missing'));
        }

        $this->assertSame(5, $store->increment('counter', 2));
        $this->assertSame(4, $store->decrement('counter'));
    }

    #[Test]
    public function what_it_is_given_is_kept_as_it_was_when_given()
    {
        $store = $this->store();

        $store->forever('object', $object = (object) ['title' => 'Before']);
        $store->forever('stache::items::entries::blog::one', $entry = $this->entry('one', 'Before'));

        $object->title = 'After';
        $entry->set('title', 'After');

        $this->assertSame('Before', $store->get('object')->title);
        $this->assertSame('Before', $store->get('stache::items::entries::blog::one')->get('title'));
        $this->assertNotSame($store->get('object'), $store->get('object'));

        $store->persist();

        $this->assertSame('Before', $this->store()->get('object')->title);
        $this->assertSame('Before', $this->store()->get('stache::items::entries::blog::one')->get('title'));
    }

    #[Test]
    public function nothing_is_written_until_it_persists()
    {
        $store = $this->store();
        $store->forever('one', ['a' => 1]);

        $this->assertNull($this->store()->get('one'));
        $this->assertDirectoryDoesNotExist($this->directory);

        $store->persist();

        $this->assertSame(['a' => 1], $this->store()->get('one'));
    }

    #[Test]
    public function values_written_as_php_are_the_plain_ones()
    {
        $store = $this->store();
        $store->forever('plain', ['a' => [1, 'two', null, false, 3.5]]);
        $store->forever('object', collect([1]));
        $store->forever('nothing', null);
        $store->forever('stache::items::entries::blog::closure', (object) ['not' => 'an entry']);
        $store->persist();

        $pointer = json_decode(file_get_contents($this->directory.'/pointer.json'), true);

        $this->assertEqualsCanonicalizing(['generation', 'value:plain', 'files:blog', 'entries:blog#'.(crc32('closure') % 32)], array_keys($pointer));
        $this->assertSame(['a' => [1, 'two', null, false, 3.5]], include $this->directory.'/'.$pointer['value:plain']);
        $this->assertEquals(collect([1]), $this->store()->get('object'));
        $this->assertNull($this->store()->get('nothing'));
        $this->assertEquals((object) ['not' => 'an entry'], $this->store()->get('stache::items::entries::blog::closure'));
    }

    #[Test]
    public function it_forgets_and_flushes()
    {
        $store = $this->store();
        $store->forever('one', 1);
        $store->forever('object', collect([1]));
        $store->forever('stache::items::entries::blog::one', $this->entry('one'));
        $store->persist();

        $store->forget('one');
        $store->forget('object');
        $store->forget('stache::items::entries::blog::one');

        $this->assertNull($store->get('one'));
        $this->assertNull($store->get('object'));
        $this->assertNull($store->get('stache::items::entries::blog::one'));
        $this->assertSame(1, $this->store()->get('one'));

        $store->persist();

        $this->assertNull($this->store()->get('one'));
        $this->assertNull($this->store()->get('object'));
        $this->assertNull($this->store()->get('stache::items::entries::blog::one'));

        $store->forever('two', 2);
        $store->persist();
        $store->flush();

        $this->assertNull($store->get('two'));
        $this->assertNull($this->store()->get('two'));
    }

    #[Test]
    public function something_with_a_time_to_live_expires()
    {
        $store = $this->store();
        $store->put('short', ['a' => 1], 60);
        $store->put('gone', 'x', 0);
        $store->persist();

        $this->assertSame(['a' => 1], $this->store()->get('short'));
        $this->assertNull($this->store()->get('gone'));
        $this->assertArrayNotHasKey('value:short', json_decode(file_get_contents($this->directory.'/pointer.json'), true));

        $path = (fn () => $this->fallback->path('short'))->call($store);
        file_put_contents($path, (time() - 1).serialize(['a' => 1]));

        $this->assertNull($this->store()->get('short'));
    }

    #[Test]
    public function a_process_sees_what_another_writes_without_being_told()
    {
        $reader = $this->store();
        $writer = $this->store();

        $writer->forever('title', 'First');
        $writer->forever('stache::items::entries::blog::one', $this->entry('one', 'First'));
        $writer->persist();

        $this->assertSame('First', $reader->get('title'));

        $writer->forever('title', 'Second');
        $writer->forever('stache::items::entries::blog::one', $this->entry('one', 'Second'));
        $writer->persist();

        // It looks at most a hundred times a second.
        usleep(15000);

        $this->assertSame('Second', $reader->get('title'));
        $this->assertSame('Second', $reader->get('stache::items::entries::blog::one')->get('title'));
    }

    #[Test]
    public function two_processes_saving_different_entries_keep_both()
    {
        $one = $this->store(1);
        $two = $this->store(1);

        $one->get('stache::items::entries::blog::a');
        $two->get('stache::items::entries::blog::b');

        $one->forever('stache::items::entries::blog::a', $this->entry('a'));
        $two->forever('stache::items::entries::blog::b', $this->entry('b'));

        $two->persist();
        $one->persist();

        $this->assertSame('a', $this->store(1)->get('stache::items::entries::blog::a')->id());
        $this->assertSame('b', $this->store(1)->get('stache::items::entries::blog::b')->id());
    }

    #[Test]
    public function an_entry_another_process_has_since_saved_is_forgotten_rather_than_overwritten()
    {
        $key = 'stache::items::entries::blog::one';

        // A process builds its copy of an entry from the file...
        $stale = $this->store();
        $stale->forever($key, $this->entry('one', 'As it was'));

        // ...another saves the entry, and finishes first.
        $saver = $this->store();
        $saver->forever($key, $this->entry('one', 'Saved'));
        $saver->persist();

        $stale->persist();

        // Neither copy is trusted, so the Stache will read the file again.
        $this->assertNull($this->store()->get($key));
    }

    #[Test]
    public function an_entry_two_processes_made_the_same_is_kept()
    {
        $key = 'stache::items::entries::blog::one';

        $one = $this->store();
        $two = $this->store();
        $one->forever($key, $this->entry('one'));
        $two->forever($key, $this->entry('one'));
        $two->persist();
        $one->persist();

        $this->assertSame('one', $this->store()->get($key)->id());
    }

    #[Test]
    public function an_entry_read_then_saved_overwrites_what_it_read()
    {
        $key = 'stache::items::entries::blog::one';

        $first = $this->store();
        $first->forever($key, $this->entry('one', 'First'));
        $first->persist();

        $second = $this->store();
        $this->assertSame('First', $second->get($key)->get('title'));
        $second->forever($key, $this->entry('one', 'Second'));
        $second->persist();

        $this->assertSame('Second', $this->store()->get($key)->get('title'));
    }

    #[Test]
    public function changes_two_processes_make_to_different_keys_of_an_index_are_combined()
    {
        $index = 'stache::indexes::entries::blog::title';

        tap($this->store())->forever($index, ['a' => 'Alfa', 'b' => 'Bravo', 'c' => 'Charlie'])->persist();

        $one = $this->store();
        $two = $this->store();

        $one->forever($index, array_merge($one->get($index), ['d' => 'Delta', 'a' => 'Alfa!']));
        $two->forever($index, array_merge(array_diff_key($two->get($index), ['c' => true]), ['e' => 'Echo']));

        $two->persist();
        $one->persist();

        $this->assertSame(['a' => 'Alfa!', 'b' => 'Bravo', 'e' => 'Echo', 'd' => 'Delta'], $this->store()->get($index));
    }

    #[Test]
    public function an_index_two_processes_changed_differently_at_the_same_key_is_forgotten()
    {
        $index = 'stache::indexes::entries::blog::title';

        tap($this->store())->forever($index, ['a' => 'Alfa', 'b' => 'Bravo'])->persist();

        $one = $this->store();
        $two = $this->store();

        $one->forever($index, ['a' => 'One'] + $one->get($index));
        $two->forever($index, ['a' => 'Two'] + $two->get($index));

        $two->persist();
        $one->persist();

        $this->assertNull($this->store()->get($index));
    }

    #[Test]
    public function an_index_built_from_nothing_while_another_process_changed_it_is_forgotten()
    {
        $index = 'stache::indexes::entries::blog::title';

        $builder = $this->store();
        $this->assertNull($builder->get($index));
        $builder->forever($index, ['a' => 'As it was']);

        tap($this->store())->forever($index, ['a' => 'Saved'])->persist();

        $builder->persist();

        $this->assertNull($this->store()->get($index));
    }

    #[Test]
    public function a_list_is_combined_position_by_position()
    {
        $key = 'stache::indexes::entries::blog::positions';

        tap($this->store())->forever($key, ['id', 'path', 'old'])->persist();

        $one = $this->store();
        $two = $this->store();

        // One changes what's at a position, the other adds a position.
        $one->forever($key, ['id', 'path', 'title']);
        $two->forever($key, ['id', 'path', 'old', 'date']);

        $two->persist();
        $one->persist();

        $this->assertSame(['id', 'path', 'title', 'date'], $this->store()->get($key));

        // Two that put different things at the same position can't both be right.
        $three = $this->store();
        $four = $this->store();
        $three->forever($key, [...$three->get($key), 'slug']);
        $four->forever($key, [...$four->get($key), 'uri']);
        $four->persist();
        $three->persist();

        $this->assertNull($this->store()->get($key));
    }

    #[Test]
    public function another_object_a_process_has_since_saved_is_forgotten_rather_than_overwritten()
    {
        $key = 'stache::items::terms::tags::en::news';

        $stale = $this->store();
        $this->assertNull($stale->get($key));
        $stale->forever($key, (object) ['title' => 'As it was']);

        tap($this->store())->forever($key, (object) ['title' => 'Saved'])->persist();

        $stale->persist();

        $this->assertNull($this->store()->get($key));
    }

    #[Test]
    public function a_collection_is_spread_over_more_files_the_more_entries_it_is_built_with()
    {
        $pointer = fn () => json_decode(file_get_contents($this->directory.'/pointer.json'), true);
        $row = fn ($i) => (object) ['id' => $i];

        $store = $this->store();

        foreach (range(1, 40) as $i) {
            $store->forever("stache::items::entries::small::$i", $row($i));
        }

        foreach (range(1, 600) as $i) {
            $store->forever("stache::items::entries::large::$i", $row($i));
        }

        $store->persist();

        $this->assertSame(32, $pointer()['files:small']);
        $this->assertSame(128, $pointer()['files:large']);
        $this->assertEquals($row(600), $this->store()->get('stache::items::entries::large::600'));

        // It stays as it is while the collection is added to...
        $another = $this->store();

        foreach (range(601, 2000) as $i) {
            $another->forever("stache::items::entries::large::$i", $row($i));
        }

        $another->persist();

        $this->assertSame(128, $pointer()['files:large']);
        $this->assertEquals($row(1), $this->store()->get('stache::items::entries::large::1'));
        $this->assertEquals($row(2000), $this->store()->get('stache::items::entries::large::2000'));

        // ...and is settled again when it's built from nothing.
        $rebuilt = $this->store();

        foreach (range(1, 2000) as $i) {
            $rebuilt->forget("stache::items::entries::large::$i");
        }

        $rebuilt->persist();

        foreach (range(1, 2000) as $i) {
            $rebuilt->forever("stache::items::entries::large::$i", $row($i));
        }

        $rebuilt->persist();

        $this->assertSame(256, $pointer()['files:large']);
        $this->assertEquals($row(1234), $this->store()->get('stache::items::entries::large::1234'));
        $this->assertNull($this->store()->get('stache::items::entries::large::2001'));
    }

    #[Test]
    public function something_put_back_as_it_was_isnt_written_again()
    {
        $store = $this->store();
        $store->forever('plain', ['a' => 1]);
        $store->forever('dates', ['a' => Carbon::parse('2024-03-01')]);
        $store->persist();

        $files = fn () => collect((new Filesystem)->allFiles($this->directory))
            ->reject(fn ($file) => $file->getFilename() === 'pointer.json')
            ->mapWithKeys(fn ($file) => [$file->getRelativePathname() => [$file->getInode(), $file->getMTime()]])
            ->all();

        $before = $files();

        sleep(1);

        $again = $this->store();
        $again->forever('plain', $again->get('plain'));
        $again->forever('dates', $again->get('dates'));
        $again->persist();

        $this->assertSame($before, $files());
    }

    #[Test]
    public function a_second_write_in_one_process_is_still_compared_with_what_it_first_saw()
    {
        $index = 'stache::indexes::entries::blog::title';

        tap($this->store())->forever($index, ['a' => 'Alfa', 'b' => 'Bravo'])->persist();

        // A process reads an index, as the Stache does, and keeps it...
        $process = $this->store();
        $held = $process->get($index);

        // ...another process changes one key...
        $other = $this->store();
        $other->forever($index, ['a' => 'Changed elsewhere'] + $other->get($index));
        $other->persist();

        // ...and the first writes twice, each time from what it's still holding.
        $held['c'] = 'Charlie';
        $process->forever($index, $held);
        $process->persist();

        $held['d'] = 'Delta';
        $process->forever($index, $held);
        $process->persist();

        $this->assertSame(['a' => 'Changed elsewhere', 'b' => 'Bravo', 'c' => 'Charlie', 'd' => 'Delta'], $this->store()->get($index));
    }

    #[Test]
    public function a_second_write_of_something_serialized_is_still_compared_with_what_it_first_saw()
    {
        $key = 'stache::items::terms::tags::en::news';

        tap($this->store())->forever($key, (object) ['title' => 'First'])->persist();

        // A process reads a term and holds on to it...
        $process = $this->store();
        $held = $process->get($key);

        // ...another process saves it...
        tap($this->store())->forever($key, (object) ['title' => 'Saved elsewhere'])->persist();

        // ...and the first writes what it's holding, twice. Neither write is to be trusted.
        $held->title = 'Stale';
        $process->forever($key, $held);
        $process->persist();

        $this->assertNull($this->store()->get($key));

        $held->title = 'Still stale';
        $process->forever($key, $held);
        $process->persist();

        $this->assertNull($this->store()->get($key));
    }

    #[Test]
    public function after_a_disagreement_nothing_more_a_process_writes_for_that_thing_is_kept()
    {
        $term = 'stache::items::terms::tags::en::news';
        $entry = 'stache::items::entries::blog::one';
        $index = 'stache::indexes::entries::blog::title';

        // None of them is there when the process first looks.
        $process = $this->store();
        $this->assertNull($process->get($term));
        $this->assertNull($process->get($entry));
        $this->assertNull($process->get($index));

        $stale = $this->entry('one', 'Stale');
        $saved = $this->entry('one', 'Saved elsewhere');

        $other = $this->store();
        $other->forever($term, (object) ['title' => 'Saved elsewhere']);
        $other->forever($entry, $saved);
        $other->forever($index, ['one' => 'Saved elsewhere']);
        $other->persist();

        foreach (['Stale', 'Still stale'] as $title) {
            $process->forever($term, (object) ['title' => $title]);
            $process->forever($entry, $stale);
            $process->forever($index, ['one' => $title]);
            $process->persist();

            $this->assertNull($this->store()->get($term), $title);
            $this->assertNull($this->store()->get($entry), $title);
            $this->assertNull($this->store()->get($index), $title);
        }

        // Until it starts afresh, as it does for its next request or job.
        $process->resetMemoizedState();
        $process->forever($index, ['one' => 'Fresh']);
        $process->persist();

        $this->assertSame(['one' => 'Fresh'], $this->store()->get($index));
    }

    #[Test]
    public function an_array_that_refers_to_part_of_itself_is_serialized()
    {
        $store = $this->store();
        $store->forever('plain', ['a' => ['x' => 1], 'b' => ['x' => 1]]);
        $store->persist();

        $again = $this->store();
        $value = $again->get('plain');
        $value['b'] = &$value['a'];
        $again->forever('plain', $value);
        $again->persist();

        $this->assertArrayNotHasKey('value:plain', json_decode(file_get_contents($this->directory.'/pointer.json'), true));

        $read = $this->store()->get('plain');
        $read['a']['x'] = 2;

        $this->assertSame(2, $read['b']['x']);
    }

    #[Test]
    public function the_list_of_a_stores_indexes_is_added_to_and_never_forgotten()
    {
        $key = 'stache::indexes::entries::blog::_indexes';

        tap($this->store())->forever($key, ['id', 'path'])->persist();

        $one = $this->store();
        $two = $this->store();
        $three = $this->store();

        $one->forever($key, ['id', 'path', 'title']);
        $two->forever($key, ['id', 'path', 'date']);
        $three->forever($key, ['author', 'id', 'path']);

        $two->persist();
        $one->persist();
        $three->persist();

        $this->assertSame(['author', 'date', 'id', 'path', 'title'], $this->store()->get($key));

        // And again from the one that has already had to combine its list with another's.
        $one->forever($key, ['id', 'path', 'title', 'slug']);
        $one->persist();

        $this->assertSame(['author', 'date', 'id', 'path', 'slug', 'title'], $this->store()->get($key));
    }

    #[Test]
    public function duplicate_ids_two_processes_found_are_all_kept()
    {
        $key = 'stache::duplicates';

        $one = $this->store();
        $two = $this->store();
        $this->assertNull($one->get($key));
        $this->assertNull($two->get($key));

        $one->forever($key, ['entries::blog' => ['a' => ['one.md']], 'entries::pages' => ['c' => ['three.md']]]);
        $two->forever($key, ['entries::blog' => ['a' => ['two.md'], 'b' => ['four.md']]]);

        $two->persist();
        $one->persist();

        $this->assertSame([
            'entries::blog' => ['a' => ['two.md', 'one.md'], 'b' => ['four.md']],
            'entries::pages' => ['c' => ['three.md']],
        ], $this->store()->get($key));
    }

    #[Test]
    public function a_disagreement_is_remembered_whichever_way_the_value_is_kept()
    {
        $key = 'stache::indexes::entries::blog::date';

        foreach ([[['a' => 'plain'], ['a' => Carbon::parse('2024-03-01')]], [['a' => Carbon::parse('2024-03-01')], ['a' => 'plain']]] as [$first, $second]) {
            (new Filesystem)->deleteDirectory($this->directory);

            $process = $this->store();
            $this->assertNull($process->get($key));

            tap($this->store())->forever($key, ['a' => 'saved elsewhere'])->persist();

            // Turned down as one kind of value...
            $process->forever($key, $first);
            $process->persist();

            $this->assertNull($this->store()->get($key));

            // ...and so turned down as the other.
            $process->forever($key, $second);
            $process->persist();

            $this->assertNull($this->store()->get($key));
        }
    }

    public static function kinds(): array
    {
        return [
            'plain, then plain' => [['a' => 1], ['a' => 2]],
            'serialized, then plain' => ['object', ['a' => 2]],
            'plain, then serialized' => [['a' => 1], 'object'],
            'serialized, then serialized' => ['object', 'another object'],
        ];
    }

    private function kind($value)
    {
        return is_string($value) ? (object) ['is' => $value] : $value;
    }

    #[Test]
    #[DataProvider('kinds')]
    public function something_another_process_has_since_forgotten_isnt_put_back($was, $stale)
    {
        $first = $this->store();
        $first->forever('thing', $this->kind($was));
        $first->persist();

        $holder = $this->store();
        $holder->get('thing');
        $holder->forever('thing', $this->kind($stale));

        $forgetter = $this->store();
        $forgetter->forget('thing');
        $forgetter->persist();

        $holder->persist();

        $this->assertNull($this->store()->get('thing'));
    }

    #[Test]
    #[DataProvider('kinds')]
    public function forgetting_something_that_wasnt_there_still_shows($ignored, $stale)
    {
        // A process finds nothing there, and works the thing out from the content files...
        $holder = $this->store();
        $this->assertNull($holder->get('thing'));
        $holder->forever('thing', $this->kind($stale));

        // ...which another process then changes, forgetting what was never kept.
        $forgetter = $this->store();
        $forgetter->forget('thing');
        $forgetter->persist();

        $holder->persist();

        $this->assertNull($this->store()->get('thing'));
    }

    #[Test]
    public function forgetting_an_entry_that_wasnt_there_still_shows()
    {
        $key = 'stache::items::entries::blog::one';

        // Another entry in the same file, so that the file is there before and after.
        $first = $this->store(1);
        $first->forever('stache::items::entries::blog::two', $this->entry('two'));
        $first->persist();

        $holder = $this->store(1);
        $this->assertNull($holder->get($key));
        $holder->forever($key, $this->entry('one', 'As it was'));

        $forgetter = $this->store(1);
        $forgetter->forget($key);
        $forgetter->persist();

        $holder->persist();

        $this->assertNull($this->store(1)->get($key));
        $this->assertSame('two', $this->store(1)->get('stache::items::entries::blog::two')->id());
    }

    #[Test]
    public function forgetting_the_only_entry_of_a_file_that_wasnt_there_still_shows()
    {
        $key = 'stache::items::entries::blog::one';

        $holder = $this->store();
        $this->assertNull($holder->get($key));
        $holder->forever($key, $this->entry('one', 'As it was'));

        $forgetter = $this->store();
        $forgetter->forget($key);
        $forgetter->persist();

        $holder->persist();

        $this->assertNull($this->store()->get($key));
    }

    public static function rebuilds(): array
    {
        return [
            'into more files' => [100, 2000],
            'into fewer files' => [2000, 100],
        ];
    }

    #[Test]
    #[DataProvider('rebuilds')]
    public function an_entry_held_while_its_collection_was_built_again_differently_isnt_kept($before, $after)
    {
        $row = fn ($i) => ['title' => "Entry $i"];
        $key = fn ($i) => "stache::items::entries::large::$i";

        $build = function ($count) use ($row, $key) {
            $store = $this->store();

            foreach (range(1, 2000) as $i) {
                $store->forget($key($i));
            }

            $store->persist();

            foreach (range(1, $count) as $i) {
                $store->forever($key($i), (object) $row($i));
            }

            $store->persist();
        };

        $build($before);

        $pointer = fn () => json_decode(file_get_contents($this->directory.'/pointer.json'), true);
        $files = $pointer()['files:large'];

        // Looks for an entry that isn't there, and works it out from the content file.
        $holder = $this->store();
        $this->assertNull($holder->get($key(5000)));
        $holder->forever($key(5000), (object) ['title' => 'As it was']);

        $build($after);

        $this->assertNotSame($files, $pointer()['files:large']);

        $holder->persist();

        $this->assertNull($this->store()->get($key(5000)));
        $this->assertEquals((object) $row(1), $this->store()->get($key(1)));

        // Nor is anything else it goes on to write for that collection.
        $holder->forever($key(5001), (object) ['title' => 'As it was']);
        $holder->persist();

        $this->assertNull($this->store()->get($key(5001)));
    }

    #[Test]
    public function without_what_it_first_saw_two_versions_arent_combined()
    {
        $first = $this->store();
        $first->forever('index', ['a' => 1, 'b' => 2]);
        $first->persist();

        $holder = $this->store();
        $holder->get('index');
        $holder->forever('index', ['a' => 1, 'b' => 2, 'c' => 3]);

        // Another process takes a key away, and what was there before is cleared out.
        $another = $this->store();
        $another->forever('index', ['a' => 1]);
        $another->persist();
        $another->collectGarbage(-1);

        $holder->persist();

        // Combining them from nothing would have put "b" back.
        $this->assertNull($this->store()->get('index'));
    }

    #[Test]
    public function false_and_zero_come_back_as_they_went_in()
    {
        $store = $this->store();
        $store->forever('false', false);
        $store->forever('zero', 0);
        $store->forever('empty', []);
        $store->forever('string', '');
        $store->persist();

        $this->assertFalse($this->store()->get('false'));
        $this->assertSame(0, $this->store()->get('zero'));
        $this->assertSame([], $this->store()->get('empty'));
        $this->assertSame('', $this->store()->get('string'));
    }

    #[Test]
    public function an_array_of_objects_is_kept_as_it_was_when_given()
    {
        $store = $this->store();
        $store->forever('dates', $dates = ['a' => Carbon::parse('2024-03-01')->toMutable()]);

        $store->forever('nested', $nested = ['a' => ['b' => ['c' => Carbon::parse('2024-03-01')->toMutable()]]]);

        $dates['a']->addYear();
        $nested['a']['b']['c']->addYear();

        $store->persist();

        $this->assertSame('2024-03-01', $this->store()->get('dates')['a']->format('Y-m-d'));
        $this->assertSame('2024-03-01', $this->store()->get('nested')['a']['b']['c']->format('Y-m-d'));
    }

    #[Test]
    public function incrementing_something_that_expires_leaves_it_expiring()
    {
        $store = $this->store();
        $store->put('hits', 1, 60);
        $store->persist();

        $again = $this->store();
        $this->assertSame(2, $again->increment('hits'));
        $again->persist();

        $path = (fn () => $this->fallback->path('hits'))->call($again);

        $this->assertSame(2, $this->store()->get('hits'));
        $this->assertEqualsWithDelta(time() + 60, (int) substr(file_get_contents($path), 0, 10), 2);
    }

    #[Test]
    public function a_process_stopped_before_it_replaced_the_pointer_leaves_the_store_as_it_was()
    {
        $store = $this->store();
        $store->forever('one', ['version' => 1]);
        $store->persist();

        $pointer = file_get_contents($this->directory.'/pointer.json');

        // Everything a second write does, and then the pointer put back: as if it never got that far.
        $writer = $this->store();
        $writer->forever('one', ['version' => 2]);
        $writer->persist();
        file_put_contents($this->directory.'/pointer.json', $pointer);

        usleep(15000);

        $this->assertSame(['version' => 1], $store->get('one'));
        $this->assertSame(['version' => 1], $this->store()->get('one'));
    }

    #[Test]
    public function flushing_leaves_the_lock_where_it_is()
    {
        $store = $this->store();
        $store->forever('one', 1);
        $store->persist();

        $this->assertFileExists($this->directory.'.lock');

        $store->flush();

        $this->assertFileExists($this->directory.'.lock');
        $this->assertDirectoryDoesNotExist($this->directory);

        $store->forever('two', 2);
        $store->persist();

        $this->assertSame(2, $this->store()->get('two'));
        $this->assertNull($this->store()->get('one'));
    }

    #[Test]
    public function a_file_is_cleared_out_some_time_after_it_was_last_needed_however_old_it_is()
    {
        $store = $this->store();
        $store->forever('one', ['version' => 1]);
        $store->persist();

        foreach (glob($this->directory.'/values/*.php') as $file) {
            touch($file, time() - 86400);
        }

        // Replaced a moment ago, so a process that read the pointer before that may still want it.
        $store->forever('one', ['version' => 2]);
        $store->persist();

        $this->assertSame(0, $store->collectGarbage());
        $this->assertCount(2, glob($this->directory.'/values/*.php'));
    }

    #[Test]
    public function files_nothing_points_at_are_cleared_out_once_they_are_old_enough()
    {
        $store = $this->store();
        $store->forever('one', ['version' => 1]);
        $store->persist();
        $store->forever('one', ['version' => 2]);
        $store->persist();

        $files = fn () => count(glob($this->directory.'/values/*.php'));

        $this->assertSame(2, $files());
        $this->assertSame(0, $store->collectGarbage());

        foreach (glob($this->directory.'/values/*.php') as $file) {
            touch($file, time() - 3600);
        }

        $this->assertSame(1, $store->collectGarbage());
        $this->assertSame(1, $files());
        $this->assertSame(['version' => 2], $this->store()->get('one'));
    }

    #[Test]
    public function a_file_that_has_gone_is_a_miss()
    {
        $writer = $this->store();
        $writer->forever('one', ['a' => 1]);
        $writer->forever('stache::items::entries::blog::one', $this->entry('one'));
        $writer->persist();

        $reader = $this->store();
        (fn () => $this->pointer())->call($reader);

        (new Filesystem)->deleteDirectory($this->directory.'/values');
        (new Filesystem)->deleteDirectory($this->directory.'/entries');

        $this->assertNull($reader->get('one'));
        $this->assertNull($reader->get('stache::items::entries::blog::one'));
    }

    #[Test]
    public function an_entry_comes_back_as_unserializing_it_would_give_it()
    {
        $entry = $this->entry('one');
        $entry->date(Carbon::parse('2024-03-01 10:30:15.123456', 'Europe/London'));

        $row = ItemCodec::encode($entry);

        $this->assertTrue(ItemCodec::isPlain($row));
        $this->assertEquals(unserialize(serialize($entry)), $decoded = ItemCodec::decode($row));
        $this->assertSame($entry->date()->format('Y-m-d H:i:s.u e'), $decoded->date()->format('Y-m-d H:i:s.u e'));
        $this->assertSame('15.123456', $decoded->date()->format('s.u'));
        $this->assertSame(get_class($entry->date()), get_class($decoded->date()));
        $this->assertSame($row, ItemCodec::decode(eval('return '.var_export($row, true).';')) ? ItemCodec::encode(ItemCodec::decode($row)) : null);
    }

    #[Test]
    public function it_tells_plain_values_from_ones_holding_objects()
    {
        $plain = [1, 2.5, 'three', true, null, ['nested' => ['deeper' => ['x' => "a\0b", 'y' => 'O:8:"stdClass":0:{}']]]];

        foreach ([null, 1, 'string', [], $plain, ['a' => ['b' => ['c' => NAN]]]] as $value) {
            $this->assertTrue(ItemCodec::isPlain($value));
        }

        $shared = ['x' => 1];
        $referencing = ['a' => &$shared, 'b' => &$shared];

        foreach ([new \stdClass, ['a' => new \stdClass], ['a' => ['b' => Carbon::now()]], ['a' => fn () => true], [\Statamic\Fields\Value::class => collect()], $referencing] as $value) {
            $this->assertFalse(ItemCodec::isPlain($value));
        }

        // A string that looks like the start of a serialized object, right where one could begin, is
        // taken for one. That only means it's kept the way any other store would keep it.
        $store = $this->store();
        $store->forever('lookalike', ['a' => 'x', 'b' => 'O:8:"stdClass":0:{}']);
        $store->forever('tricky', $tricky = ['a";O:8:"stdClass":0:{}' => 1]);
        $store->persist();

        $this->assertSame(['a' => 'x', 'b' => 'O:8:"stdClass":0:{}'], $this->store()->get('lookalike'));
        $this->assertSame($tricky, $this->store()->get('tricky'));
    }

    #[Test]
    public function a_date_keeps_everything_serializing_it_would()
    {
        $entry = $this->entry('one');
        $entry->date(Carbon::parse('2024-03-01 10:30', 'Europe/Paris')->locale('fr'));

        $decoded = ItemCodec::decode(ItemCodec::encode($entry));

        $this->assertEquals(unserialize(serialize($entry)), $decoded);
        $this->assertSame(unserialize(serialize($entry))->date()->locale, $decoded->date()->locale);
    }

    #[Test]
    public function an_item_that_takes_a_hand_in_its_own_serializing_isnt_encoded()
    {
        $this->assertNull(ItemCodec::encode(new class
        {
            public $a = 1;

            public function __sleep()
            {
                return ['a'];
            }

            public function __wakeup()
            {
                $this->a = 2;
            }
        }));

        $this->assertNull(ItemCodec::encode(new class implements \Serializable
        {
            public $a = 1;

            public function __sleep()
            {
                return ['a'];
            }

            public function serialize()
            {
                return 'custom';
            }

            public function unserialize($data)
            {
                $this->a = 2;
            }
        }));

        $this->assertNull(ItemCodec::encode(new class
        {
            public int $unset;

            public function __sleep()
            {
                return ['unset'];
            }
        }));
    }

    #[Test]
    public function an_entry_holding_something_that_isnt_plain_isnt_encoded()
    {
        $this->assertNull(ItemCodec::encode($this->entry('one')->set('object', new \stdClass)));
        $this->assertNull(ItemCodec::encode($this->entry('one')->set('closure', fn () => true)));
        $this->assertNotNull(ItemCodec::encode($this->entry('one')->set('lookalike', ['__serialized' => 'not a date'])));
    }
}
