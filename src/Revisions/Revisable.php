<?php

namespace Statamic\Revisions;

use Illuminate\Support\Carbon;
use Statamic\Facades\Revision as Revisions;
use Statamic\Statamic;

trait Revisable
{
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
        if (! $this->revisionsEnabled()) {
            return $this;
        }

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

    public function publishWorkingCopy($options = [])
    {
        $item = $this->fromWorkingCopy();

        $item
            ->published(true)
            ->updateLastModified($user = $options['user'] ?? false)
            ->save();

        $item
            ->makeRevision()
            ->user($user)
            ->message($options['message'] ?? false)
            ->action('publish')
            ->save();

        $item->deleteWorkingCopy();

        return $item;
    }

    public function unpublishWorkingCopy($options = [])
    {
        $item = $this->fromWorkingCopy();

        $item
            ->published(false)
            ->updateLastModified($user = $options['user'] ?? false)
            ->save();

        $item
            ->makeRevision()
            ->user($user)
            ->message($options['message'] ?? false)
            ->action('unpublish')
            ->save();

        $item->deleteWorkingCopy();

        return $item;
    }

    public function store($options = [])
    {
        $this
            ->published(false)
            ->updateLastModified($user = $options['user'] ?? false)
            ->save();

        $this
            ->makeRevision()
            ->user($user)
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

    public function revisionsEnabled()
    {
        return config('statamic.revisions.enabled') || ! Statamic::pro();
    }

    abstract protected function revisionKey();

    abstract protected function revisionAttributes();

    abstract public function makeFromRevision($revision);
}
