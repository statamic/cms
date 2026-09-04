<?php

namespace Statamic\Forms;

use Statamic\Contracts\Forms\Submission;

readonly class SubmissionResult
{
    public function __construct(
        public Submission $submission,
        public ?string $nextPage = null
    ) {
    }

    public function isFinalized(): bool
    {
        return $this->submission->status() === 'finalized';
    }
}
