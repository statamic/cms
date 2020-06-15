<?php

namespace Statamic\Revisions;

use Statamic\Facades\Revision as Revisions;

class WorkingCopy extends Revision
{
    public function path()
    {
        return vsprintf('%s/%s/working.yaml', [
            Revisions::directory(),
            $this->key(),
        ]);
    }

    public static function fromRevision(Revision $revision)
    {
        return (new self)
            ->id($revision->id() ?? false)
            ->key($revision->key())
            ->date($revision->date())
            ->user($revision->user() ?? false)
            ->message($revision->message() ?? false)
            ->attributes($revision->attributes());
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'working' => true,
        ]);
    }
}
