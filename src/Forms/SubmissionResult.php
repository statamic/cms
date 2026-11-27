<?php

namespace Statamic\Forms;

use Statamic\Contracts\Forms\Submission;
use Symfony\Component\HttpFoundation\RedirectResponse;

readonly class SubmissionResult
{
    public function __construct(
        public Submission $submission,
        public ?string $nextPage = null,
        public ?RedirectResponse $redirect = null,
    ) {
    }

    public function isFinalized(): bool
    {
        return $this->submission->status() === 'finalized';
    }
}
