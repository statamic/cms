<?php

namespace Statamic\Forms;

use Carbon\Carbon;
use Statamic\Facades\User;

use function Statamic\trans as __;

class Restrictions
{
    public function __construct(private Form $form)
    {
    }

    public function restricted(): bool
    {
        return $this->message() !== null;
    }

    public function message(): ?string
    {
        return $this->isClosed() ?? $this->requiresLogin();
    }

    private function isClosed(): ?string
    {
        if ($this->closingDateHasPassed() || $this->limitReached()) {
            return __($this->form->get('closed_message') ?? 'This form is no longer accepting submissions.');
        }

        return null;
    }

    private function requiresLogin(): ?string
    {
        if ($this->form->get('require_login') && ! User::current()) {
            return __($this->form->get('require_login_message') ?? 'You must be logged in to submit this form.');
        }

        return null;
    }

    public function closingDateHasPassed(): bool
    {
        if (! $date = $this->form->get('close_date')) {
            return false;
        }

        return Carbon::parse($date, config('app.timezone'))->isPast();
    }

    public function limitReached(): bool
    {
        if (! $limit = (int) $this->form->get('submission_limit')) {
            return false;
        }

        return $this->submissionCount() >= $limit;
    }

    private function submissionCount(): int
    {
        $query = $this->form->querySubmissions()->whereNull('partial');

        if ($start = $this->periodStart($this->form->get('submission_limit_period', 'total'))) {
            $query->where('date', '>=', $start);
        }

        return $query->count();
    }

    private function periodStart(string $period): ?Carbon
    {
        return match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => null,
        };
    }
}
