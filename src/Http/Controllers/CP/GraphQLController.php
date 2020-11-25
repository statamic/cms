<?php

namespace Statamic\Http\Controllers\CP;

class GraphQLController
{
    public function index()
    {
        return view('statamic::graphql.index');
    }

    public function graphiql()
    {
        return view('statamic::graphql.graphiql');
    }
}
