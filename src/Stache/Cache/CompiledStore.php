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

    private static array $instances = [];

    private FileStore $fallback;
    private ?array $pointer = null;
    private array $loaded = [];
    private array $values = [];
    private array $entries = [];
    private array $fallbackRead = [];
    private array $fallbackWrites = [];
    private ?array $pointerWas = null;
    private int $pointerCheckedAt = 0;
    private array $seen = [];
    private array $fallbackSeen = [];

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
            [$file, $id] = $entry;

            $this->see($file);

            $row = array_key_exists($id, $this->entries[$file] ?? [])
                ? $this->entries[$file][$id]
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
            [$file, $id] = $entry;

            $row = is_object($value) ? ItemCodec::encode($value) : null;

            $this->see($file);
            $this->entries[$file][$id] = $row ?? false;
            $this->writeToFallback($key, $row === null ? $this->payload($value) : null);

            return true;
        }

        // Whether it can be written as PHP is left until it's written. The Stache puts every index of a
        // collection back when one entry is saved, and most of them turn out not to need writing at all.
        $this->see($name = 'value:'.$key);
        $this->values[$name] = $value;
        $this->writeToFallback($key, $value === null ? $this->payload($value) : null);

        return true;
    }

    public function increment($key, $value = 1)
    {
        $this->forever($key, $new = ((int) $this->get($key)) + $value);

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
        $this->seen = $this->fallbackSeen = [];
        $this->pointer = [];

        (new Filesystem)->deleteDirectory($this->directory);

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

        $this->seen = array_intersect_key($this->seen, $this->entries + $this->values);
        $this->fallbackSeen = array_intersect_key($this->fallbackSeen, $this->fallbackWrites);
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

        @mkdir($this->directory, 0775, true);

        $lock = fopen($this->directory.'/.lock', 'c');
        flock($lock, LOCK_EX);

        try {
            // What this process read, and where from, before reading it again:
            // another process may have written since this one started.
            [$read, $readFrom] = [$this->loaded, $this->pointer ?? []];

            $this->forgetWhatWasRead();

            $pointer = $this->pointer();

            $written = [];

            foreach ($this->entries as $file => $rows) {
                $written[$file] = $this->mergeEntries($pointer, $file, $rows) ?: null;

                $pointer = $this->write($pointer, $file, $written[$file], 'entries');
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

                if ($value !== null && ! ItemCodec::isPlain($value)) {
                    $this->fallbackWrites[substr($name, strlen('value:'))] = $this->payload($value);
                    $value = null;
                }

                $written[$name] = $this->mergeValue($pointer, $name, $value);

                $pointer = $this->write($pointer, $name, $written[$name], 'values');
            }

            $this->persistFallback();

            $this->writeAtomically($this->directory.'/pointer.json', json_encode($pointer, JSON_UNESCAPED_SLASHES));

            // What was read before and hasn't been replaced since is still what's there, and so is what
            // was just written. A process that saves many times doesn't have to read it all back each time.
            $this->loaded = $written + array_filter($read, function ($name) use ($pointer, $readFrom) {
                return ($pointer[$name] ?? null) === ($readFrom[$name] ?? null);
            }, ARRAY_FILTER_USE_KEY);

            $this->pointer = $pointer;
            $this->pointerWas = $this->pointerFileDetails();
            $this->entries = $this->values = $this->fallbackRead = $this->fallbackWrites = [];
            $this->seen = $this->fallbackSeen = [];
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * This process's entries laid over the ones in the file now. An entry that another process
     * has written since this one first looked, and written differently, is left out altogether:
     * either could be the stale one, and the content file says which is right.
     */
    private function mergeEntries(array $pointer, string $file, array $rows): array
    {
        $theirs = $this->load($file) ?? [];

        $moved = ($pointer[$file] ?? null) !== ($this->seen[$file] ?? null);

        $was = $moved ? $this->read($this->seen[$file] ?? null) : null;

        foreach ($rows as $id => $row) {
            $theirRow = $theirs[$id] ?? null;

            $changedByAnother = $moved && (! is_array($was) || $theirRow !== ($was[$id] ?? null));

            if ($changedByAnother && $theirRow !== $row) {
                unset($theirs[$id]);
                $this->fallbackWrites[$this->entryCacheKey($file, $id)] = null;
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
        if ($ours === null || ($pointer[$name] ?? null) === ($this->seen[$name] ?? null)) {
            return $ours;
        }

        $theirs = $this->load($name);

        if ($theirs === $ours || $theirs === null) {
            return $ours;
        }

        $was = $this->read($this->seen[$name] ?? null);

        if (! is_array($ours) || ! is_array($theirs) || ! is_array($was ??= [])) {
            return null;
        }

        return array_is_list($ours) && array_is_list($theirs) && array_is_list($was)
            ? $this->mergeLists($was, $theirs, $ours)
            : $this->mergeKeys($was, $theirs, $ours);
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

    private function mergeLists(array $was, array $theirs, array $ours): ?array
    {
        foreach ([$was, $theirs, $ours] as $list) {
            if (! ItemCodec::isPlain($list) || array_filter($list, 'is_array')) {
                return null;
            }
        }

        $removed = array_diff($was, $ours);
        $added = array_diff($ours, $was, $theirs);

        return array_values(array_merge(array_diff($theirs, $removed), $added));
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

    private function entryCacheKey(string $file, $id): string
    {
        $collection = substr($file, strlen('entries:'), strrpos($file, '#') - strlen('entries:'));

        return self::ENTRY_PREFIX.$collection.'::'.$id;
    }

    /**
     * Deletes the files nothing points at any more. One that was only just written is
     * left, since a process that read the pointer a moment ago may be about to read it.
     */
    public function collectGarbage(int $olderThanSeconds = 600): int
    {
        $live = array_flip($this->pointer());
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
    }

    /**
     * The file an entry is kept in, and its id. A collection's entries are spread over
     * a number of files, so that reading one entry loads a fraction of the collection,
     * and saving one writes a fraction of it.
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

        $id = substr($rest, $position + 2);

        return ['entries:'.substr($rest, 0, $position).'#'.(crc32($id) % $this->buckets), $id];
    }

    private function forgetCompiled(string $key): void
    {
        if ($entry = $this->entryKey($key)) {
            $this->see($entry[0]);
            $this->entries[$entry[0]][$entry[1]] = null;
        } else {
            $this->see($name = 'value:'.$key);
            $this->values[$name] = null;
        }
    }

    private function pointer(): array
    {
        if ($this->pointer === null) {
            $this->pointerWas = $this->pointerFileDetails();

            $contents = @file_get_contents($this->directory.'/pointer.json');

            $this->pointer = $contents ? json_decode($contents, true) : [];
        }

        return $this->pointer;
    }

    /**
     * Every write ends by replacing the pointer file, so a look at the file says whether another
     * process has written anything. It's looked at no more than a hundred times a second, which
     * costs a request nothing, and means a process that runs for a long time is never far behind.
     */
    private function forgetWhatWasReadIfAnotherProcessHasWritten(): void
    {
        if ($this->pointer === null || ($now = hrtime(true)) - $this->pointerCheckedAt < 10_000_000) {
            return;
        }

        $this->pointerCheckedAt = $now;

        if ($this->pointerFileDetails() !== $this->pointerWas) {
            $this->forgetWhatWasRead();
        }
    }

    private function pointerFileDetails(): ?array
    {
        clearstatcache(true, $file = $this->directory.'/pointer.json');

        $details = @stat($file);

        return $details ? [$details['ino'], $details['mtime'], $details['size']] : null;
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
        if ($value === null) {
            unset($pointer[$name]);

            return $pointer;
        }

        $php = '<?php return '.var_export($value, true).";\n";
        $file = $folder.'/'.hash('xxh3', $php).'.php';

        if (! is_file($this->directory.'/'.$file)) {
            $this->writeAtomically($this->directory.'/'.$file, $php);
        }

        $pointer[$name] = $file;

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

    private function persistFallback(): void
    {
        foreach ($this->fallbackWrites as $key => $payload) {
            $path = $this->fallback->path($key);

            // As with entries: written differently by another process since this one first looked.
            $theirs = @file_get_contents($path) ?: '';

            if (isset($this->fallbackSeen[$key]) && hash('xxh3', $theirs) !== $this->fallbackSeen[$key] && $theirs !== (string) $payload) {
                $payload = null;
            }

            if ($payload === null) {
                @unlink($path);

                continue;
            }

            @mkdir(dirname($path), 0777, true);

            file_put_contents($path, $payload, LOCK_EX);
        }
    }
}
