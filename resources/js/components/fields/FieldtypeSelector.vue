<template>
    <div class="h-full overflow-auto bg-white dark:bg-gray-800 p-3 rounded-l-xl">
        <header class="flex items-center justify-between pl-3">
            <ui-heading :text="__('Fieldtypes')" size="lg" icon="cog" />
            <ui-button type="button" icon="x" variant="subtle" @click="close" />
        </header>

        <div v-if="!fieldtypesLoaded" class="absolute inset-0 z-200 flex items-center justify-center text-center">
            <Icon name="loading" />
        </div>

        <div class="flex p-3" v-if="fieldtypesLoaded">
            <ui-input
                v-model="search"
                ref="search"
                autofocus
                @keydown.esc="cancelSearch"
                :placeholder="`${__('Search')}...`"
            />
        </div>

        <div class="p-2 space-y-8" v-if="fieldtypesLoaded">
            <div
                v-for="group in displayedFieldtypes"
                :key="group.handle"
                v-show="group.fieldtypes.length > 0"
            >
                <h2 v-if="group.title" v-text="group.title" class="mb-2 px-2" />
                <div class="fieldtype-selector">
                    <ui-panel>
                        <ui-panel-header v-if="group.description" class="px-2! py-1.7
                        5!">
                            <ui-description :text="group.description" />
                        </ui-panel-header>
                        <div class="grid grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-1.5">
                        <div v-for="fieldtype in group.fieldtypes" :key="fieldtype.handle">
                            <button
                                class="flex items-center gap-2 w-full px-3 py-2.5 group bg-white dark:bg-gray-850 shadow-ui-sm rounded-xl border border-gray-200 dark:border-x-0 dark:border-b-0 dark:border-gray-700 cursor-pointer"
                                type="button"
                                @click="select(fieldtype)"
                                :title="fieldtype.text"
                            >
                                <ui-icon :name="fieldtype.icon.startsWith('<svg') ? fieldtype.icon : `fieldtype-${fieldtype.icon}`" class="text-gray-500 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-gray-100" />
                                <span class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100" v-text="fieldtype.text" />
                            </button>
                            </div>
                        </div>
                    </ui-panel>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import fuzzysort from 'fuzzysort';
import { ref } from 'vue';
import { mapValues } from 'lodash-es';
import { Icon } from '@/components/ui';

const loadedFieldtypes = ref(null);

export default {
    components: {
        Icon,
    },

    props: {
        allowTitle: {
            default: false,
        },
        allowSlug: {
            default: false,
        },
        allowDate: {
            default: false,
        },
    },

    data: function () {
        return {
            categories: {
                text: {
                    title: __('Text & Rich Content'),
                    description: __('fieldtypes.picker.category.text.description'),
                },
                controls: {
                    title: __('Buttons & Controls'),
                    description: __('fieldtypes.picker.category.controls.description'),
                },
                media: {
                    title: __('Media'),
                    description: __('fieldtypes.picker.category.media.description'),
                },
                number: {
                    title: __('Number'),
                    description: __('fieldtypes.picker.category.number.description'),
                },
                relationship: {
                    title: __('Relationship'),
                    description: __('fieldtypes.picker.category.relationship.description'),
                },
                structured: {
                    title: __('Structured'),
                    description: __('fieldtypes.picker.category.structured.description'),
                },
                special: {
                    title: __('Special'),
                    description: __('fieldtypes.picker.category.special.description'),
                },
            },
            search: '',
        };
    },

    computed: {
        fieldtypes() {
            if (!this.fieldtypesLoaded) return;

            return loadedFieldtypes.value;
        },

        fieldtypesLoaded() {
            return Array.isArray(loadedFieldtypes.value);
        },

        allFieldtypes() {
            if (!this.fieldtypesLoaded) return [];

            let options = this.fieldtypes.map((fieldtype) => {
                return {
                    text: fieldtype.title,
                    value: fieldtype.handle,
                    categories: fieldtype.categories,
                    keywords: fieldtype.keywords,
                    icon: fieldtype.icon,
                };
            });

            if (this.allowDate)
                options.unshift({
                    text: __('Publish Date'),
                    value: 'date',
                    categories: ['system'],
                    isMeta: true,
                    icon: 'date',
                });
            if (this.allowSlug)
                options.unshift({
                    text: __('Slug'),
                    value: 'slug',
                    categories: ['system'],
                    isMeta: true,
                    icon: 'slug',
                });
            if (this.allowTitle)
                options.unshift({
                    text: __('Title'),
                    value: 'title',
                    categories: ['system'],
                    isMeta: true,
                    icon: 'title',
                });

            return options;
        },

        groupedFieldtypes() {
            return mapValues(this.categories, (category, handle) => {
                category.handle = handle;
                category.fieldtypes = [];

                this.allFieldtypes.forEach((fieldtype) => {
                    let categories = fieldtype.categories;
                    if (categories.length === 0) categories = ['special'];
                    if (categories.includes(handle)) category.fieldtypes.push(fieldtype);
                });

                return category;
            });
        },

        searchFieldtypes() {
            let options = this.allFieldtypes;

            if (this.search) {
                return fuzzysort
                    .go(this.search, this.allFieldtypes, {
                        all: true,
                        keys: ['text', (obj) => obj.categories?.join(), (obj) => obj.keywords?.join()],
                        scoreFn: (scores) => {
                            const textScore = scores[0]?.score * 1;
                            const categoriesScore = scores[1]?.score * 0.1;
                            const keywordsScore = scores[2]?.score * 0.4;
                            return Math.max(textScore, categoriesScore, keywordsScore);
                        },
                    })
                    .map((result) => result.obj);
            }

            return options;
        },

        displayedFieldtypes() {
            return this.isSearching ? [{ fieldtypes: this.searchFieldtypes }] : this.groupedFieldtypes;
        },

        allowMeta() {
            return this.allowTitle || this.allowSlug || this.allowDate;
        },

        isSearching() {
            return this.search;
        },
    },

    watch: {
        fieldtypesLoaded: {
            immediate: true,
            handler() {
                this.$nextTick(() => {
                    if (this.$refs.search?.$el?.querySelector('input')) {
                        this.$refs.search.$el.querySelector('input').focus();
                    }
                });
            },
        },
    },

    created() {
        if (this.fieldtypesLoaded) return;

        let url = cp_url('fields/fieldtypes?selectable=true');

        if (this.$config.get('isFormBlueprint')) url += '&forms=true';

        this.$axios.get(url).then((response) => (loadedFieldtypes.value = response.data));
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

            field = Object.assign(
                {
                    display: __(`cp.${selection.value}`),
                    handle: selection.value,
                    type: fieldtype,
                    isMeta: true,
                },
                field,
            );

            this.$emit('selected', field);
            this.close();
        },

        createField(handle) {
            const fieldtype = this.fieldtypes.find((f) => f.handle === handle);

            // Build the initial empty field. The event listener will assign display, handle,
            // and id keys. This will be 'field_n' etc, where n would be the total root
            // level, grid, or set fields depending on the event listener location.
            let field = {
                type: fieldtype.handle,
                display: __(':title Field', { title: fieldtype.title }),
                handle: null, // The handle will be generated from the display by the "slug" fieldtype.
                icon: fieldtype.icon,
                instructions: null,
                localizable: false,
                width: 100,
                listable: 'hidden',
                isNew: true,
            };

            // Vue's reactivity works best when an object already has the appropriate values.
            // We'll set up the default values for each config option. Each option might
            // have a default value defined, otherwise will just set it to null.
            let defaults = {};
            fieldtype.config.forEach((configField) => {
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

        cancelSearch(event) {
            if (!this.search) return;

            event.stopPropagation();
            this.search = '';
        },
    },
};
</script>
