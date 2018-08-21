@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">{{ __('Structures') }}</h1>

        @can('create', 'Statamic\Contracts\Data\Structures\Structure')
            <a href="{{ cp_route('structures.create') }}" class="btn">{{ __('Create Structure') }}</a>
        @endcan
    </div>

    @if (! count($structures))
    <div class="card">
        <div class="no-results">
            <div class="mx-auto w-32 h-32 p-4 border rounded-full text-grey-light">@svg('new/hierarchy-files-1')</div>
            <h2>{{ __('Structures') }}</h2>
            <h3>{{ __('There are no structures.') }}</h3>
        </div>
    </div>
    @else
    <div class="card flush">
        <div class="dossier-table-wrapper">
            <table class="dossier">
                <tbody>
                    @foreach($structures as $structure)
                    <tr>
                        <td class="cell-title first-cell flex items-center">
                            <span class="column-label">{{ _('Title' )}}</span>
                            <div class="stat">
                                <i class="icon icon-documents"></i>
                                {{ $structure->flattenedPages()->count() }}
                            </div>
                            <a href="{{ cp_route('structures.edit', $structure->handle()) }}">{{ $structure->title() }}</a>

                            @can('delete', $structure)
                                <form method="POST" action="{{ cp_route('structures.destroy', $structure->handle()) }}">
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
