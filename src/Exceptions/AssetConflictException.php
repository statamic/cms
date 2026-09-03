<?php

namespace Statamic\Exceptions;

use Exception;

class AssetConflictException extends Exception
{
    public function __construct(
        string $message,
        protected array $context = []
    ) {
        parent::__construct($message);
    }

    public function context(): array
    {
        return $this->context;
    }
}
