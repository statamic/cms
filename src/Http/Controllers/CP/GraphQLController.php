<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\Http\Middleware\RequireStatamicPro;

class GraphQLController extends CpController
{
    public function __construct()
    {
        $this->middleware(RequireStatamicPro::class);
    }

    public function index()
    {
        return redirect()->action([self::class, 'graphiql']);
    }

    public function graphiql()
    {
        $this->authorize('view graphql');

        $configKey = $this->isLegacyRebingGraphql()
            ? 'graphql.prefix'
            : 'graphql.route.prefix';

        return view('statamic::graphql.graphiql', [
            'url' => '/'.config($configKey),
        ]);
    }

    protected function isLegacyRebingGraphql()
    {
        return class_exists('\Rebing\GraphQL\Support\ResolveInfoFieldsAndArguments');
    }
}
