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

        return view('statamic::graphql.graphiql', [
            'url' => '/'.config('graphql.route.prefix'),
        ]);
    }
}
