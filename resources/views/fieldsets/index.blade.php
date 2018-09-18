@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">{{ __('Fieldsets') }}</h1>

        @can('create', 'Statamic\Contracts\Fields\Fieldset')
            <a href="{{ cp_route('fieldsets.create') }}" class="btn">{{ __('Create Fieldset') }}</a>
        @endcan
    </div>

    <fieldset-listing :fieldsets="{{ json_encode($fieldsets) }}"></fieldset-listing>

@endsection
