@extends('layout')

@section('content')

    <user-group-listing inline-template v-cloak>

        <div class="listing user-roles-listing">
            <div class="flexy mb-24">
                <h1 class="fill">{{ translate('cp.nav_user-groups') }}</h1>
                <div class="controls">
                    @can('users:create')
                        <search v-model="keyword"></search>
                        <div class="btn-group">
                            <a href="{{ route('user.group.create') }}" class="btn btn-primary">{{ translate('cp.create_usergroup_button') }}</a>
                        </div>
                    @endcan
                </div>
            </div>
            <div class="card flush">
                <template v-if="noItems" v-cloak>
                    <div class="no-results">
                        <span class="icon icon-documents"></span>
                        <h2>{{ trans('cp.usergroups_empty_heading') }}</h2>
                        <h3>{{ trans('cp.usergroups_empty') }}</h3>
                        <a href="{{ route('user.group.create') }}" class="btn btn-default btn-lg">{{ trans('cp.create_usergroup_button') }}</a>
                    </div>
                </template>
                <dossier-table v-if="hasItems" :items="items" :keyword.sync="keyword" :options="tableOptions"></dossier-table>
            </div>
        </div>

    </user-group-listing>

@endsection
