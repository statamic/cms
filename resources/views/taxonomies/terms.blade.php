@extends('layout')

@section('content')

    <term-listing inline-template v-cloak
        get="{{ route('terms.get', $group) }}"
        delete="{{ route('terms.delete') }}"
        :can-delete="{{ bool_str(\Statamic\API\User::getCurrent()->can('taxonomies:'.$group.':delete')) }}">

        <div class="listing term-listing">
            <div class="flexy mb-24">
                <h1 class="fill">{{ $group_title }}</h1>
                <div class="controls">
                    @can("taxonomies:{$group}:create")
                        <a href="{{ route('term.create', $group) }}" class="btn btn-primary">
                            {{ trans('cp.create_taxonomy_term_button', ['term' => str_singular($group_title)]) }}
                        </a>
                    @endcan
                </div>
            </div>

            <div class="card flush">
                <template v-if="noItems">
                    <div class="no-results">
                        <span class="icon icon-documents"></span>
                        <h2>{{ trans('cp.taxonomy_terms_empty_heading', ['term' => $group_title]) }}</h2>
                        <h3>{{ trans('cp.taxonomy_terms_empty') }}</h3>
                        @can("taxonomies:{$group}:manage")
                            <a href="{{ route('term.create', $group) }}" class="btn btn-default btn-lg">{{ trans('cp.create_taxonomy_term_button', ['term' => str_singular($group_title)]) }}</a>
                        @endcan
                    </div>
                </template>

                <div class="loading" v-if="loading">
                    <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
                </div>

                <dossier-table v-if="hasItems" :items="items" :keyword.sync="keyword" :options="tableOptions"></dossier-table>
            </div>
        </div>

    </term-listing>

@endsection
