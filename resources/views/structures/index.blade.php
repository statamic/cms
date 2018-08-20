@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">{{ __('Structures') }}</h1>

        @can('super')
            <a href="{{ route('statamic.cp.structures.configure.index') }}" class="btn">{{ __('Configure Sections') }}</a>
        @endcan
    </div>

    @if (! count($structures))
    <div class="card">
        <div class="no-results">
            <span class="icon icon-documents"></span>
            <h2>{{ __('Structures') }}</h2>
            <h3>{{ _('There are no structures.') }}</h3>
            @can('super')
                <a href="{{ route('statamic.cp.structures.configure.index') }}" class="btn btn-default btn-lg">{{ __('Configure') }}</a>
            @endcan
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
                            <a href="{{ route('statamic.cp.structures.edit', $structure->handle()) }}">{{ $structure->title() }}</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endsection
