<?php

namespace Statamic\Support;

class MethodDenylist
{
    private const METHODS = [
        'delete', 'deleteFile', 'deleteQuietly',
        'destroy', 'forceDelete', 'save', 'saveQuietly',
        'truncate', 'update', 'updateQuietly', 'write', 'writeFile',
    ];

    public static function blocks(string $method): bool
    {
        return in_array(strtolower($method), array_map('strtolower', self::METHODS), true);
    }
}
