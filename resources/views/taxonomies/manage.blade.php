@extends('layout')

@section('content')

    <configure-taxonomies-listing inline-template v-cloak>
        <div>

            <div class="flexy mb-24">
                <h1 class="fill">{{ t('manage_taxonomies') }}</h1>
                @can('super')
                    <a href="{{ route('taxonomy.create') }}" class="btn btn-primary">{{ translate('cp.create_taxonomy_button') }}</a>
                @endcan
            </div>

            <div class="card" v-if="noItems">
                <div class="no-results">
                    <span class="icon icon-documents"></span>
                    <h2>{{ trans_choice('cp.taxonomies', 2) }}</h2>
                    <h3>{{ trans('cp.taxonomies_empty') }}</h3>
                    @can('super')
                        <a href="{{ route('taxonomy.create') }}" class="btn btn-default btn-lg">{{ trans('cp.create_taxonomy_button') }}</a>
                    @endcan
                </div>
            </div>

            <div class="card flush" v-if="hasItems">
                <dossier-table :items="items" :options="tableOptions"></dossier-table>
            </div>

        </div>
    </configure-taxonomies-listing>

@endsection
