<?php declare(strict_types=1);

namespace Laragraph\Utils;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BadMultipartRequestGraphQLException extends BadRequestHttpException
{
    /**
     * @param array<mixed> $headers
     */
    public function __construct(string $message, \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(
            "{$message} Be sure to conform to the GraphQL multipart request specification (https://github.com/jaydenseric/graphql-multipart-request-spec).",
            $previous,
            $code,
            $headers
        );
    }
}
