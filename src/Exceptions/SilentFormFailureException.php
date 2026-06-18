<?php

namespace Statamic\Exceptions;

use Statamic\Contracts\Forms\Submission;

class SilentFormFailureException extends \Exception
{
    public function __construct(protected ?Submission $submission = null)
    {
        parent::__construct();
    }

    public function submission(): ?Submission
    {
        return $this->submission;
    }
}
