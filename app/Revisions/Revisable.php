<?php

namespace Statamic\Revisions;

use Illuminate\Support\Carbon;
use Facades\Statamic\Revisions\Repository as Revisions;

trait Revisable
{
    protected $published = true;

    public function revision(string $reference)
    {
        return $this->revisions()->get($reference);
    }

    public function revisions()
    {
        return Revisions::whereKey($this->revisionKey());
    }

    public function latestRevision()
    {
        return $this->revisions()->last();
    }

    public function makeRevision()
    {
        return (new Revision)
            ->date(Carbon::now())
            ->key($this->revisionKey())
            ->attributes($this->revisionAttributes());
    }

    public function makeWorkingCopy()
    {
        return (new WorkingCopy)
            ->date(Carbon::now())
            ->key($this->revisionKey())
            ->attributes($this->revisionAttributes());
    }

    public function fromWorkingCopy()
    {
        return $this->makeFromRevision($this->workingCopy());
    }

    public function hasWorkingCopy()
    {
        return $this->workingCopy() !== null;
    }

    public function workingCopy()
    {
        if (! $revision = Revisions::findWorkingCopyByKey($this->revisionKey())) {
            return null;
        }

        return WorkingCopy::fromRevision($revision);
    }

    public function published($published = null)
    {
        if (func_num_args() === 0) {
            return $this->published;
        }

        $this->published = $published;

        return $this;
    }

    public function publish($options = [])
    {
        $this->createRevisionAndSave($options, function ($item) {
            $item->published(true);
        });
    }

    public function unpublish($options = [])
    {
        $this->createRevisionAndSave($options, function ($item) {
            $item->published(false);
        });
    }

    public function draft($options = [])
    {
        return $this->unpublish($options);
    }

    protected function createRevisionAndSave($options, $callback)
    {
        $item = $this->fromWorkingCopy();

        $callback($item);

        $item->save();

        $item
            ->makeRevision()
            ->user($options['user'] ?? false)
            ->message($options['message'] ?? false)
            ->save();

        optional($item->workingCopy())->delete();
    }

    abstract protected function revisionKey();
    abstract protected function revisionAttributes();
    abstract public function makeFromRevision($revision);
}
