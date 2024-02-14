<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support;

use GraphQL\Error\Error;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;
use GraphQL\Server\OperationParams as BaseOperationParams;

class OperationParams extends BaseOperationParams
{
    /** @var DocumentNode|null */
    protected $parsedQuery;

    /** @var BaseOperationParams */
    protected $baseOperationParams;

    public function __construct(BaseOperationParams $baseOperationParams)
    {
        $this->init($baseOperationParams);
    }

    protected function init(BaseOperationParams $baseOperationParams): void
    {
        $this->queryId = $baseOperationParams->queryId;
        $this->query = $baseOperationParams->query;
        $this->operation = $baseOperationParams->operation;
        $this->variables = $baseOperationParams->variables;
        $this->extensions = $baseOperationParams->extensions;

        $this->baseOperationParams = $baseOperationParams;
    }

    /**
     * @return mixed|null
     */
    public function getOriginalInput(string $key)
    {
        return $this->baseOperationParams->originalInput[$key] ?? null;
    }

    public function isReadOnly(): bool
    {
        return $this->baseOperationParams->readOnly;
    }

    public function getParsedQuery(): DocumentNode
    {
        if (!$this->parsedQuery) {
            if (!$this->query) {
                throw new Error('No GraphQL query available');
            }

            $this->parsedQuery = Parser::parse($this->query);
        }

        return $this->parsedQuery;
    }

    /**
     * @return static
     */
    public function setParsedQuery(DocumentNode $parsedQuery)
    {
        $this->parsedQuery = $parsedQuery;

        return $this;
    }
}
