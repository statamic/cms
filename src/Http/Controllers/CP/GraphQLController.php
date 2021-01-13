<?php

namespace Statamic\Http\Controllers\CP;

class GraphQLController
{
    public function index()
    {
        return redirect()->action([self::class, 'graphiql']);
    }

    public function graphiql()
    {
        return view('statamic::graphql.graphiql', [
            'url' => '/'.config('graphql.prefix'),
        ]);
    }
}
