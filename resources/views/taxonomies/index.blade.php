@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">{{ __('Taxonomies') }}</h1>

        @can('super')
            <a href="{{ route('taxonomies.configure.index') }}" class="btn">{{ __('Configure Taxonomies') }}</a>
        @endcan
    </div>

    @if (! count($taxonomies))
    <div class="card">
        <div class="no-results">
            <span class="icon icon-documents"></span>
            <h2>{{ __('Taxonomies') }}</h2>
            <h3>{{ __('There are no taxonomies.') }}</h3>
            @can('super')
                <a href="{{ route('taxonomies.configure.index') }}" class="btn btn-default btn-lg">{{ __('Configure') }}</a>
            @endcan
        </div>
    </div>
    @else
    <div class="card flush">
        <div class="dossier-table-wrapper">
            <table class="dossier w-full">
                <tbody>
                    @foreach ($taxonomies as $taxonomy)
                    <tr>
                        <td class="cell-title first-cell flex items-center">
                            <span class="column-label">{{ _('Title' )}}</span>
                            <div class="stat">
                                <i class="icon icon-documents"></i>
                                TODO
                                {{-- {{ $taxonomy->count() }} --}}
                            </div>
                            <a href="{{ route('terms.show', $taxonomy->path()) }}">{{ $taxonomy->title() }}</a>
                        </td>
                        <td class="text-right">
                            <a class="btn btn-icon btn-primary" href="{{ route('term.create', $taxonomy->path()) }}">
                                <span class="icon icon-plus"></span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endsection
