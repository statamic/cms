<?php

namespace Statamic\Exceptions;

class DictionaryNotFoundException extends \Exception
{
    public function __construct(public string $dictionaryHandle)
    {
        parent::__construct("Dictionary [{$this->dictionaryHandle}] not found");
    }
}
