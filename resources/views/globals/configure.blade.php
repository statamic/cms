@extends('layout')

@section('content')

    <configure-globals-listing inline-template v-cloak>
        <div>

            <div class="flexy mb-24">
                <h1 class="fill">{{ t('manage_global_sets') }}</h1>
                <a href="{{ route('globals.create') }}" class="btn btn-primary pull-right">{{ translate('cp.create_global_set_button') }}</a>
            </div>

            <div class="card" v-if="noItems">
                <div class="no-results">
                    <span class="icon icon-documents"></span>
                    <h2>{{ trans('cp.globals_empty_heading') }}</h2>
                    <h3>{{ trans('cp.globals_empty') }}</h3>
                    <a href="{{ route('globals.create') }}" class="btn btn-default btn-lg">{{ trans('cp.create_global_set_button') }}</a>
                </div>
            </div>

            </div>

            <div class="card flush" v-if="hasItems">
                <dossier-table :items="items" :options="tableOptions"></dossier-table>
            </div>

        </div>
    </configure-globals-listing>

@endsection
