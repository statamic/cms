<?php

namespace Statamic\Revisions;

use Illuminate\Support\Carbon;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Revision as Revisions;
use Statamic\Statamic;

trait Revisable
{
    public function hasRevisions(): bool
    {
        return $this->revisions()->isNotEmpty();
    }

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

        if ($item instanceof Entry) {
            $parent = $item->get('parent');

            $item->remove('parent');
        }

        $saved = $item
            ->published(true)
            ->updateLastModified($user = $options['user'] ?? false)
            ->save();

        if (! $saved) {
            return false;
        }

        if ($item instanceof Entry && $item->collection()->hasStructure() && $parent) {
            $tree = $item->collection()->structure()->in($item->locale());

            if (optional($tree->find($parent))->isRoot()) {
                $parent = null;
            }

            $tree
                ->move($this->id(), $parent)
                ->save();
        }

        $item
            ->makeRevision()
            ->user($user)
            ->message($options['message'] ?? false)
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
            ->updateLastModified($user = $options['user'] ?? false)
            ->save();

        if (! $saved) {
            return false;
        }

        $item
            ->makeRevision()
            ->user($user)
            ->message($options['message'] ?? false)
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
            ->updateLastModified($user = $options['user'] ?? false)
            ->save();

        if ($this->revisionsEnabled()) {
            $return = $this
                ->makeRevision()
                ->user($user)
                ->message($options['message'] ?? false)
                ->save();
        }

        return $return;
    }

    public function createRevision($options = [])
    {
        $this
            ->fromWorkingCopy()
            ->makeRevision()
            ->user($options['user'] ?? false)
            ->message($options['message'] ?? false)
            ->publishAt($options['publish_at'] ?? null)
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
