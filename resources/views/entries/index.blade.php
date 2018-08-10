@extends('statamic::layout')

@section('content')

    <entry-listing inline-template v-cloak
        collection="{{ $collection->path() }}"
        get="{{ route('entries.get', $collection->path()) }}"
        delete="{{ route('entries.delete') }}"
        reorder="{{ route('entries.reorder') }}"
        search="{{ route('entries.search', $collection->path()) }}"
        sort="{{ $sort }}"
        sort-order="{{ $sort_order }}"
        :reorderable="{{ $reorderable }}"
        :can-delete="{{ bool_str(\Statamic\API\User::getCurrent()->can('collections:'.$collection->path().':delete')) }}"
        :can-create="{{ bool_str(\Statamic\API\User::getCurrent()->can('collections:'.$collection->path().':create')) }}"
        create-entry-route="{{ route('entry.create', $collection->path()) }}">

        <div class="listing entry-listing">

            <div class="flex flex-wrap justify-between lg_flex-no-wrap items-center mb-3">
                <h1 class="flex-1 mb-8 lg_mb-0">{{ $collection->title() }}</h1>
                <div class="controls flex items-center w-full lg:w-auto">
                    @can("collections:{$collection->path()}:create")
                        <template v-if="! reordering">
                            <search :keyword.sync="searchTerm" class="w-full lg_w-auto"></search>

                            <dossier-sort-selector v-if="columns.length > 0" class="ml-1"></dossier-sort-selector>

                            <div class="btn-group ml-1">
                                <select-fieldtype :data.sync="showDrafts" :options="draftOptions"></select-fieldtype>
                            </div>

                            <div class="btn-group ml-1" v-if="locales.length > 1">
                                <select-fieldtype :data.sync="locale" :options="locales"></select-fieldtype>
                            </div>

                            <button type="button" @click="enableReorder" class="btn ml-1" v-if="reorderable">
                                {{ t('reorder') }}
                            </button>

                            <a href="{{ route('entry.create', $collection->path()) }}" class="btn btn-primary ml-1">{{ t('create_entry_button') }}</a>
                        </template>
                        <template v-else>
                            <button type="button" @click="cancelOrder" class="btn ml-1">
                                {{ t('cancel') }}
                            </button>
                            <button type="button" @click="saveOrder" class="btn btn-primary ml-1">
                                {{ t('save_order') }}
                            </button>
                        </template>
                    @endcan
                </div>
            </div>

            <div class="card flush dossier-for-mobile">
                <template v-if="noItems">
                    <div class="info-block">
                        <template v-if="isSearching">
                            <span class="icon icon-magnifying-glass"></span>
                            <h2>{{ translate('cp.no_search_results') }}</h2>
                        </template>
                        <template v-else>
                            <span class="icon icon-documents"></span>
                            <h2>{{ trans('cp.entries_empty_heading', ['type' => $collection->title()]) }}</h2>
                            <h3>{{ trans('cp.entries_empty') }}</h3>
                            @can("collections:{$collection->path()}:create")
                                <a href="{{ route('entry.create', $collection->path()) }}" class="btn btn-default btn-lg">{{ trans('cp.create_entry_button') }}</a>
                            @endcan
                        </template>
                    </div>
                </template>

                <div class="loading" v-if="loading">
                    <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
                </div>

                <dossier-table v-if="hasItems" :items="items" :options="tableOptions" :is-searching="isSearching"></dossier-table>
            </div>
        </div>

    </entry-listing>
@endsection
