@extends('statamic::layout')
@section('title', Statamic::crumb('Create Entry', $collection['title']))

@section('content')
    <base-entry-create-form
        :actions="{{ json_encode($actions) }}"
        collection-title="{{ $collection['title'] }}"
        collection-url="{{ $collection['url'] }}"
        :fieldset="{{ json_encode($blueprint) }}"
        :values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        :published="{{ json_encode($published) }}"
        :localizations="{{ json_encode($localizations) }}"
        :revisions="{{ Statamic\Support\Str::bool($revisionsEnabled ) }}"
    ></base-entry-create-form>

@endsection
