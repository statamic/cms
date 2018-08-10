@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">{{ t('nav_collections') }}</h1>

        @can('super')
            <a href="{{ route('collections.manage') }}" class="btn">{{ t('manage_collections') }}</a>
        @endcan
    </div>

    @if(! count($collections))
    <div class="card">
        <div class="no-results">
            <span class="icon icon-documents"></span>
            <h2>{{ t('nav_collections') }}</h2>
            <h3>{{ t('collections_empty') }}</h3>
            @can('super')
                <a href="{{ route('collections.manage') }}" class="btn btn-default btn-lg">{{ t('manage_collections') }}</a>
            @endcan
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
                            <span class="column-label">{{ t('title' )}}</span>
                            <div class="stat">
                                <i class="icon icon-documents"></i>
                                {{ $collection->count() }}
                            </div>
                            <div class="flex-1">
                                <a href="{{ route('entry.edit', $collection->path()) }}">{{ $collection->title() }}</a>
                            </div>
                            <a class="btn btn-icon btn-primary" href="{{ route('entry.create', $collection->path()) }}">
                                <span class="icon icon-plus"></span>
                            </a>
                        </td>
                        <td class="column-actions">
                            <div class="btn-group action-more">
                                <button type="button" class="btn-more dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="icon icon-dots-three-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="{{ $collection->editUrl() }}">{{ t('manage') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endsection
