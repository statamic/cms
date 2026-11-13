<?php

namespace Statamic\Stache\Cache;

use Illuminate\Cache\FileStore;
use Illuminate\Cache\RetrievesMultipleKeys;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Filesystem\Filesystem;
use WeakReference;

/**
 * A cache store for the Stache that keeps what it can as PHP files, which OPcache
 * holds in shared memory, so reading them costs neither a file read nor unserializing.
 *
 * - Entries go into a number of files per collection, each returning [id => entry as an array].
 *   How many depends on how many entries the collection had when it was built, so that
 *   saving an entry rewrites a handful of others rather than a fixed share of them all.
 * - Plain values (indexes, paths, keys, timestamps) get a file each, returning the value.
 * - Anything else, and anything with a time to live, goes to an ordinary file store underneath.
 *
 * A file is named after a hash of its contents and is never written to again, so a
 * file OPcache has already compiled is never stale. A pointer file maps each name to
 * its current file. It is read as plain JSON, so what one process writes is seen by
 * the others on their next request, whatever OPcache is set to do about timestamps.
 *
 * Writes are held until persist() is called, at the end of the request, then merged
 * under a lock with whatever other processes have written in the meantime. Where two
 * processes changed the same thing, what they wrote is combined if it can be, and
 * otherwise forgotten, so that the Stache works it out again from the content files
 * rather than keeping whichever of the two happened to be written last.
 */
class CompiledStore implements Store
{
    use RetrievesMultipleKeys;

    private const ENTRY_PREFIX = 'stache::items::entries::';
    private const FOREVER = 9999999999;
    private const ENTRIES_PER_FILE = 8;
    private const MOST_FILES_PER_COLLECTION = 1024;
    private const GENERATION_LENGTH = 40;

    private static array $instances = [];

    private FileStore $fallback;
    private ?array $pointer = null;
    private array $loaded = [];
    private array $values = [];
    private array $entries = [];
    private array $fallbackRead = [];
    private array $fallbackWrites = [];
    private ?string $generation = null;
    private int $generationCheckedAt = 0;
    private array $seen = [];
    private array $fallbackSeen = [];
    private array $fallbackConflicts = [];
    private array $conflicted = [];

    public function __construct(private string $directory, private int $buckets = 32)
    {
        $this->fallback = new FileStore(new Filesystem, $directory.'/fallback');

        self::$instances[] = WeakReference::create($this);
    }

    /**
     * Writes what every store in this process is holding on to.
     */
    public static function persistAll(): void
    {
        foreach (self::instances() as $store) {
            $store->persist();
        }
    }

    /**
     * Has every store in this process forget what it has read, for a process that
     * outlives a request and would otherwise never see what another one writes.
     */
    public static function resetAll(): void
    {
        foreach (self::instances() as $store) {
            $store->resetMemoizedState();
        }
    }

    private static function instances(): array
    {
        self::$instances = array_values(array_filter(self::$instances, fn ($reference) => $reference->get()));

        return array_map(fn ($reference) => $reference->get(), self::$instances);
    }

    public function get($key)
    {
        $this->forgetWhatWasReadIfAnotherProcessHasWritten();

        if ($entry = $this->entryKey($key)) {
            [$collection, $id] = $entry;

            $this->see($file = $this->entriesFile($collection, $id));

            $row = array_key_exists($id, $this->entries[$collection] ?? [])
                ? $this->entries[$collection][$id]
                : ($this->load($file)[$id] ?? null);

            // False marks an entry that couldn't be written as an array, and is in the file store.
            return is_array($row) ? ItemCodec::decode($row) : $this->getFromFallback($key);
        }

        $this->see($name = 'value:'.$key);

        if (array_key_exists($name, $this->values)) {
            return $this->values[$name] ?? $this->getFromFallback($key);
        }

        if (isset($this->pointer()[$name])) {
            return $this->load($name);
        }

        return $this->getFromFallback($key);
    }

    public function put($key, $value, $seconds)
    {
        if ($seconds <= 0) {
            return $this->forget($key);
        }

        // What's compiled is kept until it's replaced, so anything that expires is left to the file store.
        $this->forgetCompiled($key);
        $this->writeToFallback($key, $this->payload($value, time() + $seconds));

        return true;
    }

    public function forever($key, $value)
    {
        if ($entry = $this->entryKey($key)) {
            [$collection, $id] = $entry;

            $row = is_object($value) ? ItemCodec::encode($value) : null;

            $this->see($this->entriesFile($collection, $id));
            $this->entries[$collection][$id] = $row ?? false;
            $this->writeToFallback($key, $row === null ? $this->payload($value) : null);

            return true;
        }

        $this->see($name = 'value:'.$key);

        // Anything with an object in it is serialized now, as any other store would, so that it's kept
        // as it is at this moment and whoever passed it in can go on to change it. False is serialized
        // too, since it's what reading a compiled file that has gone away looks like.
        $serialized = null;

        $plain = is_array($value)
            ? ItemCodec::isPlain($value, $serialized)
            : is_scalar($value) && $value !== false;

        $this->values[$name] = $plain ? $value : null;
        $this->writeToFallback($key, $plain ? null : self::FOREVER.($serialized ?? serialize($value)));

        return true;
    }

    public function increment($key, $value = 1)
    {
        $new = ((int) $this->get($key)) + $value;

        // Something that was going to expire still does.
        $payload = array_key_exists($key, $this->fallbackWrites) ? $this->fallbackWrites[$key] : $this->readFallback($key);
        $expires = strlen((string) $payload) >= 10 ? (int) substr($payload, 0, 10) : self::FOREVER;

        if ($expires < self::FOREVER && $expires > time()) {
            $this->put($key, $new, $expires - time());
        } else {
            $this->forever($key, $new);
        }

        return $new;
    }

    public function decrement($key, $value = 1)
    {
        return $this->increment($key, $value * -1);
    }

    public function touch($key, $seconds)
    {
        if (($value = $this->get($key)) === null) {
            return false;
        }

        return $this->put($key, $value, $seconds);
    }

    public function forget($key)
    {
        $this->forgetCompiled($key);
        $this->writeToFallback($key, null);

        return true;
    }

    public function flush()
    {
        $this->entries = $this->values = $this->loaded = $this->fallbackRead = $this->fallbackWrites = [];
        $this->seen = $this->fallbackSeen = $this->conflicted = [];
        $this->pointer = [];
        $this->generation = '';

        $lock = $this->lock();

        try {
            (new Filesystem)->deleteDirectory($this->directory);
        } finally {
            $this->unlock($lock);
        }

        return true;
    }

    public function getPrefix()
    {
        return '';
    }

    /**
     * Forgets what has been read, and with it any memory of what the things
     * this process hasn't written to looked like when it first saw them.
     */
    public function resetMemoizedState(): void
    {
        $this->forgetWhatWasRead();

        $this->seen = $this->entries ? $this->seen : array_intersect_key($this->seen, $this->values);
        $this->fallbackSeen = array_intersect_key($this->fallbackSeen, $this->fallbackWrites);
        $this->conflicted = [];
    }

    private function forgetWhatWasRead(): void
    {
        $this->pointer = null;
        $this->loaded = $this->fallbackRead = [];
    }

    /**
     * Writes what has been put or forgotten since the last time: new files first,
     * then the pointer to them, so no other process is ever pointed at half a file.
     */
    public function persist(): void
    {
        if (! $this->entries && ! $this->values && ! $this->fallbackWrites) {
            return;
        }

        $lock = $this->lock();

        try {
            // What this process read, and where from, before reading it again:
            // another process may have written since this one started.
            [$read, $readFrom] = [$this->loaded, $this->pointer ?? []];

            // Read again only if another process has written. Usually none has, and
            // what this one read is still what's there.
            if ($this->pointer === null || $this->currentGeneration() !== $this->generation) {
                $this->forgetWhatWasRead();
            }

            $pointer = $this->pointer();

            $written = $combined = [];

            foreach ($this->entries as $collection => $rows) {
                $pointer = $this->decideHowManyFiles($pointer, $collection, $rows);

                $files = [];

                foreach ($rows as $id => $row) {
                    $files[$this->entriesFile($collection, $id, $pointer)][$id] = $row;
                }

                foreach ($files as $file => $rows) {
                    $combined[$file] = ($pointer[$file] ?? null) !== ($this->seen[$file] ?? null);

                    $written[$file] = $this->mergeEntries($pointer, $collection, $file, $rows) ?: null;

                    $pointer = $this->write($pointer, $file, $written[$file], 'entries');
                }
            }

            foreach ($this->values as $name => $value) {
                // The Stache puts every index of a collection back whenever an entry is saved, and most
                // come back just as they were read. Comparing them is far cheaper than writing them out
                // again only to find that the file is already there.
                $unchanged = $value !== null
                    && isset($read[$name], $pointer[$name])
                    && $pointer[$name] === ($readFrom[$name] ?? null)
                    && $read[$name] === $value;

                if ($unchanged) {
                    continue;
                }

                $combined[$name] = $value !== null && ($pointer[$name] ?? null) !== ($this->seen[$name] ?? null);

                $written[$name] = $this->mergeValue($pointer, $name, $value);

                $pointer = $this->write($pointer, $name, $written[$name], 'values');
            }

            // New files, then the pointer to them, then away with what the pointer no longer needs: a
            // process that stops part way through leaves the store as it was, or as it was going to be.
            $unneeded = $this->persistFallback();

            $pointer = ['generation' => bin2hex(random_bytes(8))] + array_diff_key($pointer, ['generation' => true]);

            $this->writeAtomically($this->directory.'/pointer.json', $json = json_encode($pointer, JSON_UNESCAPED_SLASHES));

            array_map(fn ($path) => @unlink($path), $unneeded);

            // What was read before and hasn't been replaced since is still what's there, and so is what
            // was just written. A process that saves many times doesn't have to read it all back each time.
            $this->loaded = $written + array_filter($read, function ($name) use ($pointer, $readFrom) {
                return ($pointer[$name] ?? null) === ($readFrom[$name] ?? null);
            }, ARRAY_FILTER_USE_KEY);

            // What this process first saw is still what the Stache, which remembers what it has read, is
            // working from, and so still what its next write should be compared with. Unless that write
            // went in just as it was: then what's there now is exactly what the Stache has.
            foreach ($combined as $name => $wasCombined) {
                if (! $wasCombined) {
                    $this->seen[$name] = $pointer[$name] ?? null;
                }
            }

            foreach (array_diff_key($this->fallbackWrites, $this->fallbackConflicts) as $key => $payload) {
                $this->fallbackSeen[$key] = hash('xxh3', (string) $payload);
            }

            $this->pointer = $pointer;
            $this->generation = substr($json, 0, self::GENERATION_LENGTH);
            $this->entries = $this->values = $this->fallbackRead = $this->fallbackWrites = [];
        } finally {
            $this->unlock($lock);
        }
    }

    /**
     * Held by whoever is writing, clearing out or flushing. It's kept beside the
     * store's directory and not in it, so that flushing doesn't take it away.
     */
    private function lock()
    {
        @mkdir($this->directory, 0775, true);

        $lock = fopen($this->directory.'.lock', 'c');
        flock($lock, LOCK_EX);

        return $lock;
    }

    private function unlock($lock): void
    {
        flock($lock, LOCK_UN);
        fclose($lock);
    }

    /**
     * This process's entries laid over the ones in the file now. An entry that another process
     * has written since this one first looked, and written differently, is left out altogether:
     * either could be the stale one, and the content file says which is right.
     */
    private function mergeEntries(array $pointer, string $collection, string $file, array $rows): array
    {
        $theirs = $this->load($file) ?? [];

        $moved = ($pointer[$file] ?? null) !== ($this->seen[$file] ?? null);

        $was = $moved ? $this->read($this->seen[$file] ?? null) : null;

        foreach ($rows as $id => $row) {
            $theirRow = $theirs[$id] ?? null;

            $changedByAnother = $moved && (! is_array($was) || $theirRow !== ($was[$id] ?? null));

            $key = self::ENTRY_PREFIX.$collection.'::'.$id;

            // Once there has been a disagreement about an entry, nothing this process goes on to write for
            // it is trusted: what it's holding is as likely to be the stale copy the second time as the first.
            if (($changedByAnother && $theirRow !== $row) || isset($this->conflicted[$key])) {
                $this->conflicted[$key] = true;

                unset($theirs[$id]);
                $this->fallbackWrites[$key] = null;
            } elseif ($row === null) {
                unset($theirs[$id]);
            } else {
                $theirs[$id] = $row;
            }
        }

        return $theirs;
    }

    /**
     * This process's value, or, when another process has written the same value since this one
     * first looked, the two combined. Null, which forgets it, when they can't be.
     */
    private function mergeValue(array $pointer, string $name, $ours)
    {
        if ($ours === null) {
            return null;
        }

        if (($pointer[$name] ?? null) === ($this->seen[$name] ?? null) && ! isset($this->conflicted[$name])) {
            return $ours;
        }

        $theirs = $this->load($name);

        // The list of a store's indexes is the one thing that can't be forgotten and worked out again:
        // an index the Stache doesn't know it has is never updated or cleared. It's a list of names
        // that only grows, so two versions of it are both right, and it's kept as the Stache keeps it.
        if (str_ends_with($name, '::_indexes') && is_array($ours) && is_array($theirs)) {
            $names = array_values(array_unique([...array_values($theirs), ...array_values($ours)]));

            sort($names);

            return $names;
        }

        // As with an entry: after one disagreement, nothing more this process writes is trusted.
        if (isset($this->conflicted[$name])) {
            return null;
        }

        if ($theirs === $ours || $theirs === null) {
            return $ours;
        }

        $was = $this->read($this->seen[$name] ?? null);

        $merged = is_array($ours) && is_array($theirs) && is_array($was ??= [])
            ? $this->mergeKeys($was, $theirs, $ours)
            : null;

        if ($merged === null) {
            $this->conflicted[$name] = true;
        }

        return $merged;
    }

    private function mergeKeys(array $was, array $theirs, array $ours): ?array
    {
        $merged = $theirs;

        foreach ($ours as $key => $value) {
            $wasThere = array_key_exists($key, $was);

            if ($wasThere && $was[$key] === $value) {
                continue;
            }

            $theyChangedIt = array_key_exists($key, $theirs) !== $wasThere || ($wasThere && $theirs[$key] !== $was[$key]);

            if ($theyChangedIt && (! array_key_exists($key, $theirs) || $theirs[$key] !== $value)) {
                return null;
            }

            $merged[$key] = $value;
        }

        foreach (array_diff_key($was, $ours) as $key => $value) {
            if (array_key_exists($key, $theirs) && $theirs[$key] !== $value) {
                return null;
            }

            unset($merged[$key]);
        }

        return $merged;
    }

    /**
     * Notes which file something was in the first time this process had anything to
     * do with it, so that it can tell later whether another process has replaced it.
     */
    private function see(string $name): void
    {
        if (! array_key_exists($name, $this->seen)) {
            $this->seen[$name] = $this->pointer()[$name] ?? null;
        }
    }

    private function read(?string $file)
    {
        if ($file === null) {
            return [];
        }

        $value = @include $this->directory.'/'.$file;

        return $value === false ? null : $value;
    }

    /**
     * Deletes the files nothing points at any more. One that was only just written is
     * left, since a process that read the pointer a moment ago may be about to read it.
     */
    public function collectGarbage(int $olderThanSeconds = 600): int
    {
        $lock = $this->lock();

        try {
            $this->forgetWhatWasRead();

            $live = array_flip(array_filter($this->pointer(), 'is_string'));
            $deleted = 0;

            foreach (['entries', 'values'] as $folder) {
                foreach (glob($this->directory.'/'.$folder.'/*.php') ?: [] as $file) {
                    if (isset($live[$folder.'/'.basename($file)]) || filemtime($file) > time() - $olderThanSeconds) {
                        continue;
                    }

                    @unlink($file);
                    $deleted++;
                }
            }

            return $deleted;
        } finally {
            $this->unlock($lock);
        }
    }

    /**
     * The collection and id of an entry, from the key the Stache keeps it under.
     */
    private function entryKey(string $key): ?array
    {
        if (! str_starts_with($key, self::ENTRY_PREFIX)) {
            return null;
        }

        $rest = substr($key, strlen(self::ENTRY_PREFIX));

        if (($position = strpos($rest, '::')) === false) {
            return null;
        }

        return [substr($rest, 0, $position), substr($rest, $position + 2)];
    }

    /**
     * The file an entry is kept in. A collection's entries are spread over a number of
     * files, so that reading one loads a fraction of the collection and saving one
     * writes a fraction of it.
     */
    private function entriesFile(string $collection, string $id, ?array $pointer = null): string
    {
        $files = ($pointer ?? $this->pointer())['files:'.$collection] ?? $this->buckets;

        return 'entries:'.$collection.'#'.(crc32($id) % $files);
    }

    /**
     * Settles how many files a collection's entries are spread over, when it has none yet.
     * That's when the collection is being built, so all of its entries are here to be counted.
     * It then stays as it is until the collection is built again.
     */
    private function decideHowManyFiles(array $pointer, string $collection, array $rows): array
    {
        $prefix = 'entries:'.$collection.'#';

        foreach ($pointer as $name => $file) {
            if (str_starts_with($name, $prefix)) {
                return $pointer;
            }
        }

        $files = $this->buckets;
        $entries = count(array_filter($rows, fn ($row) => $row !== null));

        while ($files < self::MOST_FILES_PER_COLLECTION && $files * self::ENTRIES_PER_FILE < $entries) {
            $files *= 2;
        }

        $pointer['files:'.$collection] = $files;

        return $pointer;
    }

    private function forgetCompiled(string $key): void
    {
        if ($entry = $this->entryKey($key)) {
            $this->see($this->entriesFile(...$entry));
            $this->entries[$entry[0]][$entry[1]] = null;
        } else {
            $this->see($name = 'value:'.$key);
            $this->values[$name] = null;
        }
    }

    private function pointer(): array
    {
        if ($this->pointer === null) {
            $contents = (string) @file_get_contents($this->directory.'/pointer.json');

            $this->generation = substr($contents, 0, self::GENERATION_LENGTH);
            $this->pointer = $contents ? json_decode($contents, true) : [];
        }

        return $this->pointer;
    }

    /**
     * Every write ends by replacing the pointer file, which begins differently each time, so reading the
     * start of it says whether another process has written anything. It's read no more than a hundred times
     * a second, which costs a request nothing, and means a process that runs for a long time is never far behind.
     */
    private function forgetWhatWasReadIfAnotherProcessHasWritten(): void
    {
        if ($this->pointer === null || ($now = hrtime(true)) - $this->generationCheckedAt < 10_000_000) {
            return;
        }

        $this->generationCheckedAt = $now;

        if ($this->currentGeneration() !== $this->generation) {
            $this->forgetWhatWasRead();
        }
    }

    /**
     * The pointer file begins with something that is different every time it's written.
     */
    private function currentGeneration(): string
    {
        return (string) @file_get_contents($this->directory.'/pointer.json', false, null, 0, self::GENERATION_LENGTH);
    }

    private function load(string $name)
    {
        if (array_key_exists($name, $this->loaded)) {
            return $this->loaded[$name];
        }

        if (! $file = $this->pointer()[$name] ?? null) {
            return $this->loaded[$name] = null;
        }

        // A file that has gone since the pointer was read (the store was flushed) is a miss.
        $value = @include $this->directory.'/'.$file;

        return $this->loaded[$name] = $value === false ? null : $value;
    }

    private function write(array $pointer, string $name, $value, string $folder): array
    {
        $was = $pointer[$name] ?? null;

        if ($value === null) {
            unset($pointer[$name]);
        } else {
            $php = '<?php return '.var_export($value, true).";\n";

            $pointer[$name] = $folder.'/'.hash('xxh3', $php).'.php';

            if (! is_file($this->directory.'/'.$pointer[$name])) {
                $this->writeAtomically($this->directory.'/'.$pointer[$name], $php);
            }
        }

        // A file is cleared out some time after it was last needed, so that's when its clock starts.
        if ($was !== null && $was !== ($pointer[$name] ?? null)) {
            @touch($this->directory.'/'.$was);
        }

        return $pointer;
    }

    private function writeAtomically(string $file, string $contents): void
    {
        @mkdir(dirname($file), 0775, true);

        $temporary = $file.'.'.getmypid().'.'.bin2hex(random_bytes(4)).'.tmp';

        file_put_contents($temporary, $contents);
        rename($temporary, $file);
    }

    /**
     * What the file store would write for a value: when it expires, then the value serialized.
     */
    private function payload($value, int $expires = self::FOREVER): string
    {
        return min($expires, self::FOREVER).serialize($value);
    }

    private function getFromFallback(string $key)
    {
        if (array_key_exists($key, $this->fallbackWrites)) {
            $payload = $this->fallbackWrites[$key];
        } else {
            $payload = $this->readFallback($key);
        }

        if ($payload === null || strlen($payload) < 10 || time() >= (int) substr($payload, 0, 10)) {
            return null;
        }

        return unserialize(substr($payload, 10));
    }

    private function readFallback(string $key): string
    {
        if (! array_key_exists($key, $this->fallbackRead)) {
            $this->fallbackRead[$key] = @file_get_contents($this->fallback->path($key)) ?: '';

            $this->fallbackSeen[$key] ??= hash('xxh3', $this->fallbackRead[$key]);
        }

        return $this->fallbackRead[$key];
    }

    private function writeToFallback(string $key, ?string $payload): void
    {
        $this->readFallback($key);

        $this->fallbackWrites[$key] = $payload;
    }

    /**
     * Writes what belongs in the file store, and returns the paths of what no longer does,
     * for deleting once the pointer has been replaced.
     */
    private function persistFallback(): array
    {
        $unneeded = $this->fallbackConflicts = [];

        foreach ($this->fallbackWrites as $key => $payload) {
            $path = $this->fallback->path($key);

            // As with entries: written differently by another process since this one first looked.
            $theirs = @file_get_contents($path) ?: '';

            $conflicts = isset($this->fallbackSeen[$key]) && hash('xxh3', $theirs) !== $this->fallbackSeen[$key] && $theirs !== (string) $payload;

            if (($conflicts && $payload !== null) || isset($this->conflicted[$key])) {
                $payload = null;
                $this->fallbackConflicts[$key] = $this->conflicted[$key] = true;
            } elseif ($conflicts) {
                $this->fallbackConflicts[$key] = true;
            }

            if ($payload === null) {
                if ($theirs !== '') {
                    $unneeded[] = $path;
                }

                continue;
            }

            // Put back just as it was, which is what happens to most indexes when an entry is saved.
            if ($payload === $theirs) {
                continue;
            }

            @mkdir(dirname($path), 0777, true);

            file_put_contents($path, $payload, LOCK_EX);
        }

        return $unneeded;
    }
}
