<?php

namespace Statamic\Fields;

use Statamic\Support\Str;

class ClassRuleParser
{
    public function parse(string $rule): array
    {
        $rule = Str::substr($rule, 4);

        if (! Str::contains($rule, '(')) {
            return [$rule, []];
        }

        $class = Str::before($rule, '(');

        $arguments = Str::of($rule)
            ->between('(', ')')
            ->explode(',')
            ->mapWithKeys(function ($arg, $key) {
                $arg = trim($arg);

                if (preg_match('/^[a-zA-Z]+: ?/', $arg)) {
                    [$key, $arg] = explode(':', $arg, 2);
                    $key = trim($key);
                    $arg = trim($arg);
                }

                if (is_numeric($arg)) {
                    return [$key => Str::contains($arg, '.') ? (float) $arg : (int) $arg];
                } elseif (Str::startsWith($arg, '"') && Str::endsWith($arg, '"')) {
                    return [$key => (string) Str::of($arg)->trim('"')->replace('\\"', '"')];
                } elseif (Str::startsWith($arg, "'") && Str::endsWith($arg, "'")) {
                    return [$key => (string) Str::of($arg)->trim("'")->replace("\\'", "'")];
                } elseif ($arg === 'null') {
                    return [$key => null];
                } elseif ($arg === 'true') {
                    return [$key => true];
                } elseif ($arg === 'false') {
                    return [$key => false];
                }

                return [$key => $arg];
            });

        return [$class, $arguments->all()];
    }
}
