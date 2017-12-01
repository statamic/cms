@extends('layout')

@section('content')

    <taxonomies-listing inline-template v-cloak>
        <div>
            <div class="flexy mb-24 sticky">
                <h1 class="fill">{{ trans_choice('cp.taxonomies', 2) }}</h1>
                @can('super')
                    <a href="{{ route('taxonomies.manage') }}" class="btn">{{ translate('cp.manage_taxonomies') }}</a>
                @endcan
            </div>

            <template v-if="noItems" v-cloak>
                <div class="no-results">
                    <span class="icon icon-documents"></span>
                    <h2>{{ trans_choice('cp.taxonomies', 2) }}</h2>
                    <h3>{{ trans('cp.taxonomies_empty') }}</h3>
                    @can('taxonomies:manage')
                        <a href="{{ route('taxonomy.create') }}" class="btn btn-default btn-lg">{{ trans('cp.create_taxonomy_button') }}</a>
                    @endcan
                </div>
            </template>

            <div class="card">
                <dossier-table v-if="hasItems" :items="items" :options="tableOptions"></dossier-table>
            </div>

        </div>
    </taxonomies-listing>

@endsection
