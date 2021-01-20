<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Term;
use Statamic\GraphQL\Types\TermInterface;

class TermQuery extends Query
{
    public function type(): Type
    {
        return GraphQL::type(TermInterface::NAME);
    }

    public function args(): array
    {
        return [
            'id' => GraphQL::string(),
        ];
    }

    public function resolve($root, $args)
    {
        $query = Term::query();

        if ($id = $args['id']) {
            $query->where('id', $id);
        }

        return $query->limit(1)->get()->first();
    }
}
