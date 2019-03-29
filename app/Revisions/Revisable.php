<?php

namespace Statamic\Revisions;

use Illuminate\Support\Carbon;
use Statamic\API\Revision as Revisions;

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

    public function deleteWorkingCopy()
    {
        return optional($this->workingCopy())->delete();
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
        $item = $this->fromWorkingCopy();

        $item->published(true)->save();

        $item
            ->makeRevision()
            ->user($options['user'] ?? false)
            ->message($options['message'] ?? false)
            ->action('publish')
            ->save();

        $item->deleteWorkingCopy();
    }

    public function unpublish($options = [])
    {
        $item = $this->fromWorkingCopy();

        $item->published(false)->save();

        $item
            ->makeRevision()
            ->user($options['user'] ?? false)
            ->message($options['message'] ?? false)
            ->action('unpublish')
            ->save();

        $item->deleteWorkingCopy();
    }

    public function store($options = [])
    {
        $this->published(false)->save();

        $this
            ->makeRevision()
            ->user($options['user'] ?? false)
            ->message($options['message'] ?? false)
            ->save();
    }

    public function createRevision($options = [])
    {
        $this
            ->fromWorkingCopy()
            ->makeRevision()
            ->user($options['user'] ?? false)
            ->message($options['message'] ?? false)
            ->save();
    }

    abstract protected function revisionKey();
    abstract protected function revisionAttributes();
    abstract public function makeFromRevision($revision);
}
