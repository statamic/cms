@extends('layout')

@section('content')

    <collection-listing inline-template v-cloak>
        <div>
            <div class="flexy mb-24">
                <h1 class="fill">{{ translate('cp.nav_collections') }}</h1>

                @can('super')
                    <a href="{{ route('collections.manage') }}" class="btn">{{ translate('cp.manage_collections') }}</a>
                @endcan
            </div>

            <div class="card" v-if="noItems">
                <div class="no-results">
                    <span class="icon icon-documents"></span>
                    <h2>{{ trans_choice('cp.collections', 2) }}</h2>
                    <h3>{{ trans('cp.collections_empty') }}</h3>
                    @can('super')
                        <a href="{{ route('collections.manage') }}" class="btn btn-default btn-lg">{{ trans('cp.manage_collections') }}</a>
                    @endcan
                </div>
            </div>

            <div class="card flush">
                <dossier-table v-if="hasItems" :items="items" :options="tableOptions"></dossier-table>
            </div>

        </div>
    </collection-listing>

@endsection
