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
}
