<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\ResourceAuthorizer;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\GraphQL\Types\DynamicTermUnionType;
use Statamic\GraphQL\Types\TermType;
use Statamic\Support\Str;

class SpecificTermQuery extends Query
{
    public function __construct(protected string $taxonomyHandle)
    {
        $name = Str::camel(Str::singular($taxonomyHandle));

        if ($name === Str::camel($taxonomyHandle)) {
            $name .= 'Term';
        }

        $this->attributes['name'] = $name;

        parent::__construct();
    }

    public function type(): Type
    {
        $taxonomy = Taxonomy::findByHandle($this->taxonomyHandle);
        $blueprints = $taxonomy->termBlueprints();

        $combinations = $blueprints->map(fn ($blueprint) => [
            'taxonomy' => $taxonomy,
            'blueprint' => $blueprint,
        ])->values()->all();

        if (count($combinations) === 1) {
            return GraphQL::type(TermType::buildName($combinations[0]['taxonomy'], $combinations[0]['blueprint']));
        }

        $unionType = new DynamicTermUnionType($combinations);
        GraphQL::addType($unionType);

        return GraphQL::type($unionType->name);
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

        $query->where('taxonomy', $this->taxonomyHandle);

        if ($id = $args['id'] ?? null) {
            $query->where('id', $id);
        }

        $term = $query->limit(1)->get()->first();

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
