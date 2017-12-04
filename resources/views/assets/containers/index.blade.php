@extends('layout')

@section('content')

    <asset-container-listing inline-template v-cloak>
        <div>

            <div class="flexy mb-24">
                <h1 class="fill">{{ translate('cp.nav_assets') }}</h1>

                @can('super')
                    <a href="{{ route('assets.containers.manage') }}" class="btn">{{ translate('cp.manage_asset_containers') }}</a>
                @endcan
            </div>

            <template v-if="noItems" v-cloak>
                <div class="no-results">
                    <span class="icon icon-documents"></span>
                    <h2>{{ trans('cp.asset_containers_empty_heading') }}</h2>
                    <h3>{{ trans('cp.asset_containers_empty') }}</h3>
                    @can('super')
                        <a href="{{ route('assets.container.create') }}" class="btn btn-default btn-lg">{{ trans('cp.new_asset_container') }}</a>
                    @endcan
                </div>
            </template>

            <div class="card">
                <dossier-table v-if="hasItems" :items="items" :options="tableOptions"></dossier-table>
            </div>
        </div>
    </asset-container-listing>

@endsection
