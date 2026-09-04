<?php

namespace Statamic\Forms;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Facades\Blink;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Instance
{
    public function __construct(
        protected FormContract $form,
        protected ?string $entry = null,
    ) {
    }

    public function form(): FormContract
    {
        return $this->form;
    }

    public function entry(): ?string
    {
        return $this->entry;
    }

    public function status(): string
    {
        return Blink::once('form-status-'.$this->form->handle().'-'.$this->entry, fn () => match (true) {
            $this->closingDateHasPassed() => 'closed',
            $this->submissionLimitReached() => 'limit_reached',
            default => 'open',
        });
    }

    public function restricted(): bool
    {
        return $this->restrictionMessage() !== null;
    }

    public function restrictionMessage(): ?string
    {
        if ($this->closingDateHasPassed() || $this->submissionLimitReached()) {
            return ($msg = $this->config('closed_message')) ? __($msg) : __('statamic::messages.form_closed_message');
        }

        if ($this->config('require_login') && ! User::current()) {
            return ($msg = $this->config('require_login_message')) ? __($msg) : __('statamic::messages.form_require_login_message');
        }

        return null;
    }

    public function config(string $key): mixed
    {
        return $this->overrides()[$key] ?? $this->form->get($key);
    }

    public function connections(): Collection
    {
        if ($overrides = $this->overrides()['connections'] ?? null) {
            return collect($overrides);
        }

        return $this->form->connections();
    }

    private function overrides(): array
    {
        if (! $this->entry) {
            return [];
        }

        return Blink::once('form-instance-overrides-'.$this->form->handle().'-'.$this->entry, function () {
            if (! $entry = Entry::find($this->entry)) {
                return [];
            }

            $value = FormFieldValues::on($entry)
                ->referencing($this->form->handle())
                ->first(fn ($value) => is_array($value) && Arr::isAssoc($value));

            return Arr::get($value, 'config', []);
        });
    }

    private function closingDateHasPassed(): bool
    {
        if (! $date = $this->config('close_date')) {
            return false;
        }

        return Carbon::parse($date, config('app.timezone'))->isPast();
    }

    private function submissionLimitReached(): bool
    {
        if (! $limit = (int) $this->config('submission_limit')) {
            return false;
        }

        return $this->submissionCount() >= $limit;
    }

    private function submissionCount(): int
    {
        $query = $this->form->querySubmissions()->whereNull('partial');

        if ($this->entry) {
            $query->where('entry', $this->entry);
        }

        if ($start = $this->submissionLimitPeriodStart()) {
            $query->where('date', '>=', $start);
        }

        return $query->count();
    }

    private function submissionLimitPeriodStart(): ?Carbon
    {
        return match ($this->config('submission_limit_period')) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => null,
        };
    }
}
