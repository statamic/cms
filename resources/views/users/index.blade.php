@extends('layout')

@section('content')

    <user-listing inline-template v-cloak>
        <div>
            <div class="flexy mb-24">
                <h1 class="fill">{{ t('manage_users') }}</h1>
                @can('users:create')
                    <a href="{{ route('user.create') }}" class="btn btn-primary">{{ translate('cp.create_user_button') }}</a>
                @endcan
            </div>
            <div class="card flush">
                <div class="loading" v-if="loading">
                    <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
                </div>
                <dossier-table v-if="hasItems" :items="items" :keyword.sync="keyword" :options="tableOptions"></dossier-table>
            </div>
        </div>
    </user-listing>

@endsection
