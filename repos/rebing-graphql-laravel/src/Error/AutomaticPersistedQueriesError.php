<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Error;

use GraphQL\Error\Error;

class AutomaticPersistedQueriesError extends Error implements ProvidesErrorCategory
{
    public const CODE_PERSISTED_QUERY_NOT_SUPPORTED = 'PERSISTED_QUERY_NOT_SUPPORTED';
    public const CODE_PERSISTED_QUERY_NOT_FOUND = 'PERSISTED_QUERY_NOT_FOUND';
    public const CODE_INTERNAL_SERVER_ERROR = 'INTERNAL_SERVER_ERROR';
    public const MESSAGE_PERSISTED_QUERY_NOT_SUPPORTED = 'PersistedQueryNotSupported';
    public const MESSAGE_PERSISTED_QUERY_NOT_FOUND = 'PersistedQueryNotFound';
    public const MESSAGE_INVALID_HASH = 'provided sha does not match query';
    public const CATEGORY_APQ = 'apq';

    public static function persistedQueriesNotSupported(): self
    {
        return new self(
            self::MESSAGE_PERSISTED_QUERY_NOT_SUPPORTED,
            $nodes = null,
            $source = null,
            $positions = [],
            $path = null,
            $previous = null,
            $extensions = [
                'code' => self::CODE_PERSISTED_QUERY_NOT_SUPPORTED,
            ]
        );
    }

    public static function persistedQueriesNotFound(): self
    {
        return new self(
            self::MESSAGE_PERSISTED_QUERY_NOT_FOUND,
            $nodes = null,
            $source = null,
            $positions = [],
            $path = null,
            $previous = null,
            $extensions = [
                'code' => self::CODE_PERSISTED_QUERY_NOT_FOUND,
            ]
        );
    }

    /**
     * @param string|null $message
     */
    public static function internalServerError($message = null): self
    {
        return new self(
            $message ?? '',
            $nodes = null,
            $source = null,
            $positions = [],
            $path = null,
            $previous = null,
            $extensions = [
                'code' => self::CODE_INTERNAL_SERVER_ERROR,
            ]
        );
    }

    public static function invalidHash(): self
    {
        return self::internalServerError(self::MESSAGE_INVALID_HASH);
    }

    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return self::CATEGORY_APQ;
    }
}
