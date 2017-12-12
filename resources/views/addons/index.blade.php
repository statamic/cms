@extends('layout')

@section('content')

    <addon-listing inline-template v-cloak>
        <div>

            <div class="flexy mb-24">
                <h1 class="fill">{{ __('Addons') }}</h1>
            </div>

            <div class="card flush">
                <template v-if="noItems">
                    <div class="no-results">
                        <span class="icon icon-power-plug"></span>
                        <h2>{{ __('There are no addons') }}</h2>
                        <h3>{{ __('Addons extend the functionality of Statamic.') }}</h3>
                    </div>
                </template>
                <dossier-table v-if="hasItems" :items="items" :keyword.sync="keyword" :options="tableOptions"></dossier-table>
            </div>

        </div>
    </addon-listing>

@endsection
