<?php

namespace Statamic\Revisions;

use Illuminate\Support\Carbon;
use Statamic\Contracts\Entries\Entry;
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
        return Revisions::make()
            ->date(Carbon::now())
            ->key($this->revisionKey())
            ->attributes($this->revisionAttributes());
    }

    public function makeWorkingCopy()
    {
        return Revisions::make()
            ->action('working')
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

        return $revision->toWorkingCopy();
    }

    public function deleteWorkingCopy()
    {
        return optional($this->workingCopy())->delete();
    }

    public function publishWorkingCopy($options = [])
    {
        $item = $this->fromWorkingCopy();

        $saved = $item
            ->published(true)
            ->updateLastModified($user = $options['user'] ?? null)
            ->save();

        if (! $saved) {
            return false;
        }

        $item
            ->makeRevision()
            ->user($user)
            ->message($options['message'] ?? null)
            ->action('publish')
            ->save();

        $item->deleteWorkingCopy();

        if ($item instanceof Entry) {
            $item->blueprint()->setParent($item);
        }

        return $item;
    }

    public function unpublishWorkingCopy($options = [])
    {
        $item = $this->fromWorkingCopy();

        $saved = $item
            ->published(false)
            ->updateLastModified($user = $options['user'] ?? null)
            ->save();

        if (! $saved) {
            return false;
        }

        $item
            ->makeRevision()
            ->user($user)
            ->message($options['message'] ?? null)
            ->action('unpublish')
            ->save();

        $item->deleteWorkingCopy();

        if ($item instanceof Entry) {
            $item->blueprint()->setParent($item);
        }

        return $item;
    }

    public function store($options = [])
    {
        $return = $this
            ->published(false)
            ->updateLastModified($user = $options['user'] ?? null)
            ->save();

        if ($this->revisionsEnabled()) {
            $return = $this
                ->makeRevision()
                ->user($user)
                ->message($options['message'] ?? null)
                ->save();
        }

        return $return;
    }

    public function createRevision($options = [])
    {
        $this
            ->fromWorkingCopy()
            ->makeRevision()
            ->user($options['user'] ?? null)
            ->message($options['message'] ?? null)
            ->save();
    }

    public function revisionsEnabled()
    {
        return config('statamic.revisions.enabled') && Statamic::pro();
    }

    abstract protected function revisionKey();

    abstract protected function revisionAttributes();

    abstract public function makeFromRevision($revision);
}
