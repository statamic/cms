<?php

namespace Statamic\Revisions;

use Statamic\Contracts\Revisions\Revision as RevisionContract;
use Statamic\Contracts\Revisions\RevisionQueryBuilder;
use Statamic\Contracts\Revisions\RevisionRepository as Contract;
use Statamic\Facades\File;
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
        return app(Revision::class);
    }

    public function whereKey($key)
    {
        return $this->query()
            ->where('key', $key)
            ->where('action', '!=', 'working')
            ->get()
            ->keyBy(function ($revision) {
                return $revision->date()->timestamp;
            });
    }

    public function findWorkingCopyByKey($key)
    {
        $path = $this->directory().'/'.$key.'/working.yaml';

        if (! File::exists($path)) {
            return null;
        }

        return $this->store->makeItemFromFile($path, '');
    }

    public function save(RevisionContract $revision)
    {
        $revision->id($revision->date()->timestamp);

        $this->store->save($revision);
    }

    public function delete(RevisionContract $revision)
    {
        $this->store->delete($revision);
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
