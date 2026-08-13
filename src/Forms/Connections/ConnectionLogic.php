<?php

namespace Statamic\Forms\Connections;

use Statamic\Contracts\Forms\Submission;
use Statamic\Forms\Logic\RuleEvaluator;
use Statamic\Support\Arr;

class ConnectionLogic
{
    public static function normalize(array $conditions): ?array
    {
        $conditions = collect($conditions)
            ->map(fn ($condition) => Arr::only($condition, ['field', 'operator', 'value', 'join']))
            ->filter(fn ($condition) => Arr::get($condition, 'field') && filled(Arr::get($condition, 'value')))
            ->values();

        return $conditions->isNotEmpty() ? $conditions->all() : null;
    }

    public static function passes(array $config, Submission $submission): bool
    {
        if (($config['enabled'] ?? true) === false) {
            return false;
        }

        if (empty($config['conditions'])) {
            return true;
        }

        return (new RuleEvaluator)->passes($config['conditions'], $submission->toArray());
    }
}
