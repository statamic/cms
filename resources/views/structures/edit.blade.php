@extends('statamic::layout')

@section('content')

<page-tree
    structure="{{ $structure->handle() }}"
    url="{{ route('structures.get', $structure->handle()) }}"
    save-url="{{ route('structures.update', $structure->handle()) }}"
    inline-template v-cloak>

    <div id="pages">
        <div class="flex items-center flex-wrap mb-3">
            <h1 class="w-full text-center mb-2 md:mb-0 md:text-left md:w-auto md:flex-1">{{ $structure->title() }}</h1>
            <div class="controls flex flex-wrap justify-center w-full items-center md:w-auto">
                <div class="btn-group mt-1 md:mt-0">
                    <select-fieldtype :data.sync="showDrafts" :options="draftOptions"></select-fieldtype>
                </div>
                <div class="btn-group ml-1 mt-1 md:mt-0" v-if="locales.length > 1">
                    <select-fieldtype :data.sync="locale" :options="locales"></select-fieldtype>
                </div>
                <div class="btn-group ml-1 mt-1 md:mt-0" v-if="pages.length > 0">
                    <button type="button" class="btn btn-default" @click="expandAll" v-if="hasChildren">
                        {{ t('expand') }}
                    </button>
                    <button type="button" class="btn btn-default" @click="collapseAll" v-if="hasChildren">
                        {{ t('collapse') }}
                    </button>
                    <button type="button" class="btn btn-default" @click="toggleUrls" v-text="translate('cp.show_'+show)">
                    </button>
                </div>
                @can('pages:create')
                    <button type="button" class="btn btn-primary ml-1 mt-1 md:mt-0" @click="createPage('/')">
                        {{ t('create_page_button') }}
                    </button>
                @endcan
                @can('pages:reorder')
                    <div class="btn-group btn-group-primary ml-1" v-if="pages.length > 0 && changed">
                        <button type="button" class="btn btn-primary" v-if="! saving" @click="save">
                            {{ t('save_changes') }}
                        </button>
                        <span class="btn btn-primary btn-has-icon-right disabled" v-if="saving">
                            {{ t('saving') }} <i class="icon icon-circular-graph animation-spin"></i>
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

            <ul></ul> <!-- home -->

            <branches :pages="pages" :depth="1" :sortable="isSortable"></branches>
        </div>

        <create-page :locale="locale"></create-page>

        <mount-collection></mount-collection>

        <audio ref="click">
            <source src="{{ cp_resource_url('audio/click.mp3') }}" type="audio/mp3">
        </audio>
        <audio ref="card_drop">
            <source src="{{ cp_resource_url('audio/card_drop.mp3') }}" type="audio/mp3">
        </audio>
        <audio ref="card_set">
            <source src="{{ cp_resource_url('audio/card_set.mp3') }}" type="audio/mp3">
        </audio>
    </div>
</page-tree>
@endsection
