<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\ResourceAuthorizer;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Term;
use Statamic\GraphQL\Types\TermInterface;

class TermQuery extends Query
{
    protected $attributes = [
        'name' => 'term',
    ];

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

        $term = $query->limit(1)->get()->first();

        // Since the term `id` contains the taxonomy, we don't need `AuthorizesSubResources`
        // middleware, but should still validate whether or not the taxonomy is allowed.
        if ($term && ! in_array($taxonomy = $term->taxonomy()->handle(), $this->allowedSubResources())) {
            throw ValidationException::withMessages([
                'id' => 'Forbidden: '.$taxonomy,
            ]);
        }

        return $term;
    }

    public function allowedSubResources()
    {
        return ResourceAuthorizer::allowedSubResources('graphql', 'taxonomies');
    }
}
