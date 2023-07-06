<?php

namespace Statamic\Search\Null;

use Statamic\Search\Documents;
use Statamic\Search\Index;

class NullIndex extends Index
{
    public function search($query)
    {
        return new NullQuery($this);
    }

    public function delete($document)
    {
        //
    }

    public function exists()
    {
        return true;
    }

    protected function insertDocuments(Documents $documents)
    {
        //
    }

    protected function deleteIndex()
    {
        //
    }

    public function searchables()
    {
        return new NullSearchables($this);
    }
}
