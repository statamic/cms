@extends('layout')

@section('content')

    <configure-collection-listing inline-template v-cloak>
        <div>
            <div class="flexy mb-24">
                <h1 class="fill">{{ t('manage_collections') }}</h1>
                <a href="{{ route('collection.create') }}" class="btn btn-primary">{{ translate('cp.create_collection_button') }}</a>
            </div>

            <div class="card" v-if="noItems">
                <div class="no-results">
                    <span class="icon icon-documents"></span>
                    <h2>{{ trans_choice('cp.collections', 2) }}</h2>
                    <h3>{{ trans('cp.collections_empty') }}</h3>
                    <a href="{{ route('collection.create') }}" class="btn btn-default btn-lg">{{ trans('cp.create_collection_button') }}</a>
                </div>
            </div>

            <div class="card flush" v-if="hasItems">
                <dossier-table :items="items" :options="tableOptions"></dossier-table>
            </div>

        </div>
    </configure-collection-listing>

@endsection
