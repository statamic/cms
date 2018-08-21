@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">{{ __('Collections') }}</h1>

        @can('create', 'Statamic\Contracts\Data\Entries\Collection')
            <a href="{{ cp_route('collections.create') }}" class="btn">{{ __('Create Collection') }}</a>
        @endcan
    </div>

    @if (! count($collections))
        <div class="card">
            <div class="no-results">
                <div class="mx-auto w-32 h-32 p-4 border rounded-full text-grey-light">@svg('new/content-pencil-write')</div>
                <h2>{{ __('Collections') }}</h2>
                <h3>{{ __('Collections are containers that hold groups of similar entries all following the same URL pattern.') }}</h3>
            </div>
        </div>
    @else
        <div class="card flush">
            <div class="dossier-table-wrapper">
                <table class="dossier">
                    <tbody>
                        @foreach($collections as $collection)
                        <tr>
                            <td class="cell-title first-cell flex items-center">
                                <span class="column-label">{{ _('Title' )}}</span>
                                <div class="stat">
                                    <i class="icon icon-documents"></i>
                                    {{ $collection->entries()->count() }}
                                </div>
                                <a href="{{ cp_route('collections.show', $collection->path()) }}">{{ $collection->title() }}</a>
                                <a href="{{ cp_route('collections.edit', $collection->path()) }}">Edit</a>

                                @can('delete', $collection)
                                    <form method="POST" action="{{ cp_route('collections.destroy', $collection->path()) }}">
                                        @csrf @method('delete') <button>Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
