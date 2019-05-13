<template>

    <div class="h-full bg-white overflow-auto">

        <div v-if="fieldtypesLoading" class="absolute pin z-200 flex items-center justify-center text-center">
            <loading-graphic />
        </div>

        <div class="p-3" v-if="fieldtypesLoaded">
            <div class="filter mb-0">
                <a @click="filterBy = 'all'" :class="{'active': filterBy == 'all'}">{{ __('All') }}</a>
                <a @click="filterBy = filter" v-for="filter in filteredFilters" :class="{'active': filterBy == filter}">
                    {{ __(filter) }}
                </a>
                <a @click.prevent="openSearch" :class="['no-dot', {'active': search}]"><span class="icon icon-magnifying-glass"></span></a>
            </div>
        </div>

        <div class="p-3 pt-0" v-if="fieldtypesLoaded">
            <div class="fieldtype-selector">
                <div :class="['search', { 'is-searching': isSearching }]">
                    <input type="text" v-model="search" ref="search" @keydown.esc="cancelSearch" :placeholder="`${__('Search')}...`" />
                </div>
                <div class="flex flex-wrap -mx-1 fieldtype-list">
                    <div class="w-1/2 sm:w-1/3 md:w-1/4 p-1" v-for="option in fieldtypeOptions">
                        <a class="border flex items-center group w-full rounded shadow-sm py-1 px-2"
                            @click="select(option)">
                            <svg-icon class="h-4 w-4 opacity-50 group-hover:opacity-100" :name="option.icon"></svg-icon>
                            <span class="pl-2 text-grey-80 group-hover:text-grey-100">{{ option.text }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
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
            filters: ['Text', 'Media', 'Pickable', 'Structured', 'Relationship', 'Special', 'System'],
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

        searchFilteredFieldtypes() {
            let options = this.allFieldtypes;

            if (this.search) {
                options = options.filter(fieldtype => {
                    return fieldtype.text.toLowerCase().includes(this.search.toLowerCase());
                })
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
                return this.searchFilteredFieldtypes.filter(fieldtype => fieldtype.categories.includes(filter.toLowerCase())).length;
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
                type: fieldtype.handle,
                instructions: null,
                localizable: false,
                width: 100,
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

        cancelSearch() {
            this.isSearchOpen = false;
            this.search = '';
        }

    }

}
</script>
