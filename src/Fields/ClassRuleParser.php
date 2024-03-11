<?php

namespace Statamic\Fields;

use Statamic\Support\Str;

class ClassRuleParser
{
    public function parse(string $rule): array
    {
        $rule = Str::substr($rule, 4);

        if (! str_contains($rule, '(')) {
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
                    return [$key => str_contains($arg, '.') ? (float) $arg : (int) $arg];
                } elseif (Str::startsWith($arg, '"') && Str::endsWith($arg, '"')) {
                    return [$key => (string) Str::of($arg)->trim('"')->replace('\\"', '"')];
                } elseif (Str::startsWith($arg, "'") && Str::endsWith($arg, "'")) {
                    return [$key => (string) Str::of($arg)->trim("'")->replace("\\'", "'")];
                }

                return [$key => $arg];
            });

        return [$class, $arguments->all()];
    }
}
