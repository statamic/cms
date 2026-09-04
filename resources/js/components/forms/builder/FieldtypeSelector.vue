<script setup lang="ts">
import { Button, Input } from '@ui';
import { computed, ref } from 'vue';
import { mapValues } from 'lodash-es';
import fuzzysort from 'fuzzysort';
import { usePage } from '@inertiajs/vue3';
import { FieldView, injectBuilderContext, InspectorType } from '@/pages/forms/Builder.vue';
import { categories, categoryColorClasses } from './categories';
import { __ } from '@/bootstrap/globals';

const { fieldtypes, fieldView, formsProInstalled, inspect } = injectBuilderContext();

const search = ref('');
const isSearching = computed(() => search.value.length > 0);

const hasFieldsets = Object.keys(usePage().props.fieldsets ?? {}).length > 0;

const allFieldtypes = computed(() => {
    let options = [...fieldtypes];

    options.push({
        handle: 'section',
        title: __('Section'),
        description: __('Sections group fields into clearly defined areas, helping you structure, organize, and navigate your form.'),
        categories: ['structure'],
        keywords: [],
        icon: 'add-section',
        order: 1,
        config: [],
    });

    if (formsProInstalled) {
        options.push({
            handle: 'page_break',
            title: __('Page Break'),
            description: __('Page Breaks split a form into multiple steps, turning a long form into a more manageable, step-by-step experience.'),
            categories: ['structure'],
            keywords: [],
            icon: 'page',
            order: 2,
            config: [],
        });
    }

    if (hasFieldsets) {
        options.push({
            handle: 'fieldset',
            title: __('Link Existing'),
            description: __('Link one or more existing fields from a fieldset.'),
            categories: ['fieldsets'],
            keywords: [],
            icon: 'link',
            config: [],
        });
    }

    return options;
});

const groupedFieldtypes = computed(() => {
    return mapValues(categories, (category, handle) => {
        category.handle = handle;
        category.fieldtypes = [];

        allFieldtypes.value.forEach((fieldtype) => {
            let categories = fieldtype.categories;
            if (categories.length === 0) categories = ['other'];
            if (categories.includes(handle)) category.fieldtypes.push(fieldtype);
        });

        category.fieldtypes.sort((a, b) => {
            const aHasOrder = a.order != null;
            const bHasOrder = b.order != null;

            if (aHasOrder && bHasOrder) return a.order - b.order;
            if (aHasOrder) return -1;
            if (bHasOrder) return 1;

            return a.title.localeCompare(b.title);
        });

        return category;
    });
});

const searchFieldtypes = computed(() => {
    let options = allFieldtypes.value;

    if (search.value) {
        return fuzzysort
            .go(search.value, options, {
                all: true,
                keys: ['title', (obj) => obj.categories?.join(), (obj) => obj.keywords?.join()],
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
});

const displayedFieldtypes = computed(() => isSearching.value ? [{ fieldtypes: searchFieldtypes.value }] : groupedFieldtypes.value);
</script>

<template>
    <div
        style="--graph-paper-y-offset: 9rem;"
        class="bg-graph-paper [&_button]:w-full [&_button>div]:truncate [&_button>div]:block [&_button]:rounded-xl [&_button]:font-normal [&_button]:justify-start [&_button]:h-9 [&_button_svg]:size-3.5"
    >
        <div class="fieldtype-source-container px-0.5 max-[1000px]:ps-2 pt-6">
            <Input icon="magnifying-glass" :legible-text="false" input-class="rounded-xl placeholder-xs" :placeholder="__('Search Field Types...')" v-model="search" />

            <ul class="py-10 max-[1000px]:pb-20 grid gap-8 @container">
                <li
                    v-for="group in displayedFieldtypes"
                    :key="group.handle"
                    v-show="group.fieldtypes.length > 0"
                >
                    <h2
                        v-if="group.title"
                        class="inline-flex items-center px-1.5 pb-1.5 bg-gray-100 dark:bg-gray-900 text-sm text-gray-950 dark:text-gray-200 font-medium"
                        :class="fieldView === FieldView.Collapsed ? 'gap-1.5' : 'gap-0'"
                    >
                        <span
                            class="h-2 shrink-0 rounded-full"
                            :class="{
                                [categoryColorClasses[group.color].dot]: true,
                                'w-2 opacity-100': fieldView === FieldView.Collapsed,
                                'w-0 opacity-0': fieldView === FieldView.Expanded,
                            }"
                            aria-hidden="true"
                        />
                        {{ group.title }}
                    </h2>
                    <ul class="fieldtype-source grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                        <li
                            v-for="fieldtype in group.fieldtypes"
                            :key="fieldtype.handle"
                            class="fieldtype-draggable list-none"
                            :data-fieldtype="fieldtype.handle"
                            tabindex="-1"
                        >
                            <Button
                                :text="__(fieldtype.title)"
                                :title="__(fieldtype.title)"
                                :icon="fieldtype.icon"
                                @click="inspect(InspectorType.FieldtypeHint, fieldtype)"
                                class="show-focus"
                            />
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</template>
