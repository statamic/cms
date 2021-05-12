<template>

    <div class="h-full bg-white overflow-auto">
        <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
            {{ __('Fieldtypes') }}
            <button type="button" class="btn-close" @click="close">×</button>
        </div>

        <div v-if="fieldtypesLoading" class="absolute inset-0 z-200 flex items-center justify-center text-center">
            <loading-graphic />
        </div>

        <div class="p-3" v-if="fieldtypesLoaded">
            <div class="filter mb-0">
                <a @click="filterBy = 'all'" :class="{'active': filterBy == 'all'}">{{ __('All') }}</a>
                <a @click="filterBy = filter" v-for="filter in filteredFilters" :class="{'active': filterBy == filter}">
                    {{ filterLabels[filter] }}
                </a>
                <a @click.prevent="openSearch" :class="['no-dot', {'active': search}]"><span class="icon icon-magnifying-glass"></span></a>
            </div>
        </div>

        <div class="p-3 pt-0" v-if="fieldtypesLoaded">
            <div class="fieldtype-selector">
                <div :class="['search', { 'is-searching': isSearching }]">
                    <input type="text" v-model="search" ref="search" @keydown.esc="cancelSearch" :placeholder="`${__('Search')}...`" />
                </div>
                <div class="fieldtype-list">
                    <div class="p-1" v-for="option in fieldtypeOptions">
                        <a class="border flex items-center group w-full rounded shadow-sm py-1 px-2"
                            @click="select(option)">
                            <svg-icon class="h-4 w-4 text-grey-80 group-hover:text-blue" :name="option.icon"></svg-icon>
                            <span class="pl-2 text-grey-80 group-hover:text-blue">{{ option.text }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import Fuse from 'fuse.js';
import ProvidesFieldtypes from '../fields/ProvidesFieldtypes';

export default {

    mixins: [ProvidesFieldtypes],

    props: {
        onSelect: {},
        show: {},
        allowTitle: {
            default: false
        },
        allowSlug: {
            default: false
        },
        allowDate: {
            default: false
        },
    },

    data: function() {
        return {
            isActive: false,
            filterBy: 'all',
            filterLabels: {
                text: __('Text'),
                media: __('Media'),
                pickable: __('Pickable'),
                structured: __('Structured'),
                relationship: __('Relationship'),
                special: __('Special'),
                system: __('System')
            },
            search: '',
            isSearchOpen: false
        }
    },

    computed: {

        fieldtypeSelectionText: function() {
            return _.findWhere(this.fieldtypesSelectOptions, { value: this.fieldtypeSelection }).text;
        },

        allFieldtypes() {
            if (!this.fieldtypesLoaded) return [];

            let options = this.fieldtypes.map(fieldtype => {
                return {text: fieldtype.title, value: fieldtype.handle, categories: fieldtype.categories, icon: fieldtype.icon};
            });

            if (this.allowDate) options.unshift({text: __('Publish Date'), value: 'date', categories: ['system'], isMeta: true, icon: 'date'});
            if (this.allowSlug) options.unshift({text: __('Slug'), value: 'slug', categories: ['system'], isMeta: true, icon: 'slug'});
            if (this.allowTitle) options.unshift({text: __('Title'), value: 'title', categories: ['system'], isMeta: true, icon: 'title'});

            return options;
        },

        filters() {
            return Object.keys(this.filterLabels);
        },

        searchFilteredFieldtypes() {
            let options = this.allFieldtypes;

            if (this.search) {
                const fuse = new Fuse(options, {
                    findAllMatches: true,
                    threshold: 0.1,
                    minMatchCharLength: 2,
                    keys: ['text'],
                });

                options = fuse.search(this.search);
            }

            return options;
        },

        fieldtypeOptions() {
            const options = this.searchFilteredFieldtypes;

            return this.filterBy === 'all'
                ? options
                : options.filter(fieldtype => fieldtype.categories.includes(this.filterBy.toLowerCase()));
        },

        filteredFilters() {
            if (!this.search && this.allowMeta) return this.filters;

            return this.filters.filter(filter => {
                return this.searchFilteredFieldtypes.filter(fieldtype => fieldtype.categories.includes(filter)).length;
            });
        },

        allowMeta() {
            return this.allowTitle || this.allowSlug || this.allowDate;
        },

        isSearching() {
            return this.search || this.isSearchOpen;
        }
    },

    watch: {

        show(val) {
            if (val) this.$refs.search.focus();
        },

        fieldtypesLoaded: {
            immediate: true,
            handler() {
                this.$nextTick(() => {
                    if (this.$refs.search) this.$refs.search.focus();
                });
            }
        }

    },

    methods: {

        select(selection) {
            if (selection.isMeta) {
                return this.selectMeta(selection);
            }

            const field = this.createField(selection.value);

            this.$emit('selected', field);
            this.close();
        },

        selectMeta(selection) {
            let fieldtype = selection.value;

            if (['title', 'slug'].includes(fieldtype)) {
                fieldtype = 'text';
            }

            let field = this.createField(fieldtype);

            field = Object.assign({
                display: __(`cp.${selection.value}`),
                handle: selection.value,
                type: fieldtype,
                isMeta: true
            }, field);

            this.$emit('selected', field);
            this.close();
        },

        createField(handle) {
            const fieldtype = _.findWhere(this.fieldtypes, { handle });

            // Build the initial empty field. The event listener will assign display, handle,
            // and id keys. This will be 'field_n' etc, where n would be the total root
            // level, grid, or set fields depending on the event listener location.
            let field = {
                display: fieldtype.title,
                type: fieldtype.handle,
                icon: fieldtype.icon,
                instructions: null,
                localizable: false,
                width: 100,
                listable: 'hidden',
                isNew: true
            };

            // Vue's reactivity works best when an object already has the appropriate values.
            // We'll set up the default values for each config option. Each option might
            // have a default value defined, otherwise will just set it to null.
            let defaults = {};
            _.each(fieldtype.config, configField => {
                defaults[configField.handle] = configField.default || null;
            });

            // Smoosh the field together with the defaults.
            return Object.assign(defaults, field);
        },

        close() {
            this.search = '';
            this.filterBy = 'all';
            this.$emit('closed');
        },

        openSearch() {
            this.isSearchOpen = true;
            this.$refs.search.focus();
        },

        cancelSearch(event) {
            if (! this.search) return;

            event.stopPropagation();
            this.isSearchOpen = false;
            this.search = '';
        }

    }

}
</script>
