@extends('statamic::layout')

@section('content')

    <user-listing inline-template v-cloak>
        <div>
            <div class="flex items-center mb-2">
                <h1 class="flex-1">{{ t('manage_users') }}</h1>
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
