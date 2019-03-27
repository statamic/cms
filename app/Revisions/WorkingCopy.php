<?php

namespace Statamic\Revisions;

use Facades\Statamic\Revisions\Repository as Revisions;

class WorkingCopy extends Revision
{
    public function path()
    {
        return vsprintf('%s/%s/working.yaml', [
            Revisions::directory(),
            $this->key()
        ]);
    }

    public static function fromRevision(Revision $revision)
    {
        return (new self)
            ->key($revision->key())
            ->date($revision->date())
            ->message($revision->message() ?? false)
            ->attributes($revision->attributes());
    }
}
