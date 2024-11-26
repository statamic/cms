@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('Rebuild Search'))

@section('content')

    <header class="mb-6">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('utilities.index'),
            'title' => __('Utilities')
        ])
        <div class="flex items-center justify-between">
            <h1>{{ __('Search Indexes') }}</h1>

            <form method="POST" action="{{ cp_route('utilities.search', 'all') }}">
                @csrf
                @foreach (\Statamic\Facades\Search::indexes() as $index)
                    <input type="hidden" name="indexes[]" value="{{ $index->name() }}::{{ $index->locale() }}">
                @endforeach
                <button class="btn-primary">{{ __('Update All') }}</button>
            </form>
        </div>
    </header>

    <div class="card p-0">
        @if ($errors->has('indexes'))
            <p class="p-4"><small class="help-block text-red-500">{{ $errors->first() }}</small></p>
        @endif

        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('Index') }}</th>
                    <th>{{ __('Driver') }}</th>
                    <th>{{ __('Searchables') }}</th>
                    <th>{{ __('Fields') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach (\Statamic\Facades\Search::indexes() as $index)
                    <tr>
                        <td class="flex items-center">
                            @cp_svg('search-drivers/' . $index->config()['driver'], 'w-6 h-6 rtl:ml-2 ltr:mr-2 shrink-0', 'unknown')
                            <div class="text-gray-800 dark:text-dark-150 leading-none">{{ $index->title() }}</div>
                        </td>
                        <td>
                            {{ ucwords($index->config()['driver']) }}
                        </td>
                        <td>
                            @if (is_string($index->config()['searchables']))
                                <span class="badge-pill-sm">{{ $index->config()['searchables'] }}</span>
                            @else
                                <div class="text-sm text-gray flex flex-wrap">
                                    @foreach($index->config()['searchables'] as $searchable)
                                        <div class="rtl:ml-2 ltr:mr-2 badge-pill-sm">
                                            {{ $searchable }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="text-sm text-gray flex flex-wrap">
                                @foreach($index->config()['fields'] as $field)
                                    <div class="rtl:ml-2 ltr:mr-2 badge-pill-sm">
                                        {{ $field }}
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="rtl:text-left ltr:text-right">
                            <form method="POST" action="{{ cp_route('utilities.search') }}">
                                @csrf
                                <input type="hidden" name="indexes[]" value="{{ $index->name() }}::{{ $index->locale() }}">
                                <button type="submit" class="btn btn-xs">{{ __('Update') }}</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
        </table>
    </div>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Search Indexes'),
        'url' => Statamic::docsUrl('search#indexes')
    ])

@stop
