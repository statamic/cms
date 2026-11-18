<?php

namespace Statamic\Forms\Connectors\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Contracts\Forms\Form;
use Statamic\Facades\User;
use Statamic\Fieldtypes\AutocompleteEditor\Mentions;
use Statamic\Forms\Connectors\Email;
use Statamic\Support\Arr;
use Statamic\Support\Str;

// Email config values are parsed for env vars, config and Antlers when sent, so only trusted users may author them.
class EmailConnectionTemplateSyntax implements DataAwareRule, ValidationRule
{
    private array $data = [];

    public function __construct(private Form $form)
    {
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->userCanAuthorTemplates()) {
            return;
        }

        [$index, $key] = explode('.', $attribute, 2);

        $stored = $this->storedValues(Arr::get($this->data, "{$index}.id"), $key);

        foreach (Arr::flatten(Arr::wrap($value)) as $item) {
            if (is_string($item) && $this->containsTemplateSyntax($item) && ! in_array($item, $stored, true)) {
                $fail('statamic::validation.email_connection_template_syntax')->translate();

                return;
            }
        }
    }

    private function userCanAuthorTemplates(): bool
    {
        $user = User::current();

        return $user && ($user->isSuper() || $user->hasPermission('configure forms'));
    }

    private function containsTemplateSyntax(string $value): bool
    {
        return Str::contains($value, $syntax = ['{{', '{env:'])
            || Str::contains(Mentions::replace($value, []), $syntax);
    }

    private function storedValues(mixed $id, string $key): array
    {
        if (! is_string($id)) {
            return [];
        }

        $connection = collect(Email::normalizeConnections($this->form->connections()->get(Email::handle(), [])))
            ->first(fn (array $connection) => Arr::get($connection, 'id') === $id);

        if (! $connection || ! array_key_exists($key, $connection)) {
            return [];
        }

        $values = array_filter(Arr::flatten(Arr::wrap($connection[$key])), is_string(...));

        $legacyAddresses = collect($values)->flatMap(fn (string $value) => array_map(trim(...), explode(',', $value)));

        return [...$values, ...$legacyAddresses];
    }
}
