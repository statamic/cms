@extends('statamic::layout')

@section('content')

    The publish component would go here for editing an entry.

    <hr>

    @if ($readOnly)
        The user cannot edit this. The publish component should be read only.
    @else
        This user can edit this.
    @endif

    <hr>

    <pre class="card mt-4 whitespace-pre-wrap text-sm">{{ json_encode($entry->toArray(), JSON_PRETTY_PRINT) }}</pre>

@endsection
