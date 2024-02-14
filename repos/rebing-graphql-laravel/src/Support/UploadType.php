<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support;

use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Contracts\TypeConvertible;

class UploadType extends ScalarType implements TypeConvertible
{
    public string $name = 'Upload';
    public ?string $description =
        'The `Upload` special type represents a file to be uploaded in the same HTTP request as specified by
 [graphql-multipart-request-spec](https://github.com/jaydenseric/graphql-multipart-request-spec).';

    public function __construct(string $name = 'Upload')
    {
        $this->name = $name;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($value): void
    {
        throw new InvariantViolation('`Upload` cannot be serialized');
    }

    /**
     * {@inheritdoc}
     */
    public function parseValue($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function parseLiteral(Node $valueNode, ?array $variables = null): void
    {
        throw new Error('`Upload` cannot be hardcoded in query, be sure to conform to GraphQL multipart request specification. Instead got: ' . $valueNode->kind, [$valueNode]);
    }

    public function toType(): Type
    {
        return new static();
    }
}
