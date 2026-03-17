<?php

namespace Statamic\Revisions;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Statamic\Contracts\Revisions\Revision as RevisionContract;
use Statamic\Contracts\Revisions\RevisionQueryBuilder;
use Statamic\Contracts\Revisions\RevisionRepository as Contract;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Stache\Stache;
use Statamic\Support\Str;

class RevisionRepository implements Contract
{
    protected $stache;

    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('revisions');
    }

    public function directory()
    {
        return Str::removeRight($this->store->directory(), '/');
    }

    public function make(): RevisionContract
    {
        return app(RevisionContract::class);
    }

    public function whereKey($key)
    {
        return $this->query()
            ->where('key', $key)
            ->where('action', '!=', 'working')
            ->get()
            ->keyBy(fn ($revision) => $revision->date()->timestamp);
    }

    public function findWorkingCopyByKey($key)
    {
        return $this
            ->query()
            ->where('key', $key)
            ->where('action', 'working')
            ->first();
    }

    public function save(RevisionContract $revision)
    {
        $this->store->save($revision);
    }

    public function delete(RevisionContract $revision)
    {
        $this->store->delete($revision);
    }

    protected function makeRevisionFromFile($key, $path)
    {
        $yaml = YAML::parse(File::get($path));

        $revision = (new Revision)
            ->key($key)
            ->action($yaml['action'] ?? false)
            ->id($date = $yaml['date'])
            ->date(Carbon::createFromTimestamp($date, config('app.timezone')))
            ->user($yaml['user'] ?? false)
            ->message($yaml['message'] ?? false)
            ->attributes($yaml['attributes']);

        if (! is_null($timestamp = Arr::get($yaml, 'publish_at'))) {
            $revision->publishAt(Carbon::createFromTimestamp($timestamp));
        }

        return $revision;
    }

    public function query()
    {
        return app(RevisionQueryBuilder::class);
    }

    public static function bindings(): array
    {
        return [
            RevisionContract::class => Revision::class,
            RevisionQueryBuilder::class => \Statamic\Stache\Query\RevisionQueryBuilder::class,
        ];
    }
}
