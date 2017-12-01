@extends('layout')

@section('content')

    <globals-listing inline-template v-cloak>
        <div>
            <div class="flexy sticky mb-24">
                <h1 class="fill">{{ t('global_sets') }}</h1>
                @can('super')
                    <a href="{{ route('globals.manage') }}" class="btn">{{ translate('cp.manage_global_sets') }}</a>
                @endcan
            </div>

            <template v-if="noItems">
                <div class="no-results">
                    <span class="icon icon-documents"></span>
                    <h2>{{ trans('cp.globals_empty_heading') }}</h2>
                    <h3>{{ trans('cp.globals_empty') }}</h3>
                    @can('super')
                        <a href="{{ route('globals.manage') }}" class="btn btn-default btn-lg">{{ trans('cp.manage_global_sets') }}</a>
                    @endcan
                </div>
            </template>

            <div class="card flush">
                <dossier-table v-if="hasItems" :items="items" :options="tableOptions"></dossier-table>
            </div>
        </div>
    </globals-listing>

@endsection
