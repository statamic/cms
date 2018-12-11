@extends('statamic::layout')

@section('content')

    @if (! count($structures))
        <div class="text-center max-w-md mx-auto mt-5 screen-centered border-2 border-dashed rounded-lg px-4 py-8">
            @svg('empty/structure')
            <h1 class="my-3">{{ __('Create your first Structure now') }}</h1>
            <p class="text-grey mb-3">
                {{ __('Structures are heirarchial arrangements of your content, most often used to represent forms of site navigation.') }}
            </p>
            @can('create', 'Statamic\Contracts\Data\Structures\Structure')
                <a href="{{ cp_route('structures.create') }}" class="btn-primary btn-lg">{{ __('Create Structure') }}</a>
            @endcan
        </div>
    @endif

    @if(count($structures))
        <div class="flex mb-3">
            <h1 class="flex-1">{{ __('Structures') }}</h1>

            @can('create', 'Statamic\Contracts\Data\Structures\Structure')
                <a href="{{ cp_route('structures.create') }}" class="btn">{{ __('Create Structure') }}</a>
            @endcan
        </div>
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
