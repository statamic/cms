@extends('layout')

@section('content')
<page-tree inline-template v-cloak>
    <div id="pages">
        <div class="flexy mb-24">
            <h1 class="fill">{{ translate('cp.nav_pages') }}</h1>
            <div class="controls">
                <div class="btn-group">
                    <select-fieldtype :data.sync="showDrafts" :options="draftOptions"></select-fieldtype>
                </div>
                <div class="btn-group ml-8" v-if="locales.length > 1">
                    <select-fieldtype :data.sync="locale" :options="locales"></select-fieldtype>
                </div>
                <div class="btn-group ml-8" v-if="arePages">
                    <button type="button" class="btn btn-default" v-on:click="expandAll" v-if="hasChildren">
                        {{ translate('cp.expand') }}
                    </button>
                    <button type="button" class="btn btn-default" v-on:click="collapseAll" v-if="hasChildren">
                        {{ translate('cp.collapse') }}
                    </button>
                    <button type="button" class="btn btn-default" v-on:click="toggleUrls" v-text="translate('cp.show_'+show)">
                    </button>
                </div>
                @can('pages:create')
                    <button type="button" class="btn btn-primary ml-8" @click="createPage('/')">
                        {{ t('create_page_button') }}
                    </button>
                @endcan
                @can('pages:reorder')
                    <div class="btn-group btn-group-primary ml-8" v-if="arePages && changed">
                        <button type="button" class="btn btn-secondary" v-if="! saving" @click="save">
                            {{ translate('cp.save_changes') }}
                        </button>
                        <span class="btn btn-primary btn-has-icon-right disabled" v-if="saving">
                            {{ translate('cp.saving') }} <i class="icon icon-circular-graph animation-spin"></i>
                        </span>
                    </div>
                @endcan
            </div>
        </div>

        <div :class="{'page-tree': true, 'show-urls': showUrls}">
            <div class="loading" v-if="loading">
                <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
            </div>

            <div class="saving" v-if="saving">
                <div class="inner">
                    <i class="icon icon-circular-graph animation-spin"></i> {{ translate('cp.saving') }}
                </div>
            </div>

            <ul class="tree-home list-unstyled" v-if="!loading">
                <branch url="/"
                        :home="true"
                        title="{{ array_get($home, 'title') }}"
                        uuid="{{ array_get($home, 'id')}}"
                        :edit-url="homeEditUrl"
                        :has-entries="{{ bool_str(array_get($home, 'has_entries')) }}"
                        entries-url="{{ array_get($home, 'entries_url') }}"
                        create-entry-url="{{ array_get($home, 'create_entry_url') }}">
                </branch>
            </ul>

            <branches :pages="pages" :depth="1"></branches>
        </div>

        <create-page :locale="locale"></create-page>
        <mount-collection></mount-collection>
    </div>
</page-tree>
@endsection
