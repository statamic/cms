@extends('layout')

@section('content')

    <configure-asset-container-listing inline-template v-cloak>
        <div>

            <div class="flexy mb-24">
                <h1 class="fill">{{ t('manage_asset_containers') }}</h1>
                <a href="{{ route('assets.container.create') }}" class="btn btn-primary">{{ translate('cp.new_asset_container') }}</a>
            </div>

            <div class="card" v-if="noItems">
                <div class="no-results">
                    <span class="icon icon-documents"></span>
                    <h2>{{ trans('cp.asset_containers_empty_heading') }}</h2>
                    <h3>{{ trans('cp.asset_containers_empty') }}</h3>
                    <a href="{{ route('assets.container.create') }}" class="btn btn-default btn-lg">{{ trans('cp.new_asset_container') }}</a>
                </div>
            </div>

            <div class="card flush" v-if="hasItems">
                <dossier-table :items="items" :options="tableOptions"></dossier-table>
            </div>

        </div>
    </configure-asset-container-listing>

@endsection
