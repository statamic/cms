@extends('statamic::layout')
@section('title', 'GraphQL')

@section('content')
    <iframe
        src="/cp/graphiql"
        class="card"
        style="padding: 0; width: 100%; height: calc(100vh - 48px - 24px - 24px)"
    ></iframe>
@endsection
