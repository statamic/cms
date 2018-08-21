@extends('statamic::layout')

@section('content')

    The publish component would go here for editing an entry.

    <pre class="card mt-4 whitespace-pre-wrap text-sm">{{ json_encode($entry->toArray(), JSON_PRETTY_PRINT) }}</pre>

@endsection
