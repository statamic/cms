<?php

namespace Statamic\Contracts\Revisions;

interface RevisionRepository
{
    public function whereKey($key);

    public function findWorkingCopyByKey($key);

    public function save(Revision $revision);

    public function delete(Revision $revision);

    public function make(): Revision;
}
