<template>

    <div class="h-full bg-white overflow-auto">
        <div class="bg-grey-30 px-3 py-1 border-b text-lg font-medium flex items-center justify-between">
            {{ __('Fieldtypes') }}
            <button type="button" class="btn-close" @click="close">×</button>
        </div>

        <div v-if="fieldtypesLoading" class="absolute inset-0 z-200 flex items-center justify-center text-center">
            <loading-graphic />
        </div>

        <div class="py-2 px-3 border-b bg-grey-10 mb-2 flex items-center" v-if="fieldtypesLoaded">
            <input type="text" class="input-text flex-1 mr-2 bg-white text-sm w-full" autofocus v-model="search" ref="search" @keydown.esc="cancelSearch" :placeholder="`${__('Search')}...`" />
            <div class="flex items-center">
                <button @click="switchFilter('all')" class="btn-flat" :class="{'bg-grey-50': filterBy == 'all'}">{{ __('All') }}</button>
                <button @click="switchFilter(filter)" v-for="filter in filteredFilters" class="btn-flat ml-1" :class="{'bg-grey-50': filterBy == filter}">
                    {{ filterLabels[filter] }}
                </button>
            </div>
        </div>

        <div class="p-2 pt-0" v-if="fieldtypesLoaded">
            <div class="fieldtype-selector">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-x-2 gap-y-sm">
                    <div class="p-1" v-for="option in fieldtypeOptions">
                        <a class="border flex items-center group w-full rounded hover:border-grey-50 shadow-sm py-1 px-1.5"
                            @click="select(option)">
                            <svg-icon class="h-4 w-4 text-grey-80 group-hover:text-blue" :name="option.icon" default="generic-field"></svg-icon>
                            <span class="pl-1.5 text-grey-80 text-md group-hover:text-blue">{{ option.text }}</span>
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
                controls: __('Controls'),
                media: __('Media'),
                structured: __('Structured'),
                relationship: __('Relationship'),
                special: __('Special'),
                system: __('System'),
                text: __('Text'),
            },
            search: ''
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
            return this.search;
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

        switchFilter(filter) {
            this.filterBy = filter;
            this.$refs.search.focus();
        },

        cancelSearch(event) {
            if (! this.search) return;

            event.stopPropagation();
            this.search = '';
        }

    }

}
</script>
