<script setup lang="ts">
import { computed, ref } from 'vue';
import { mapValues } from 'lodash-es';
import fuzzysort from 'fuzzysort';
import { Button, Input } from '@ui';
import { categories, categoryColorClasses } from './categories';
import { FieldView, injectBuilderContext, InspectorType } from '@/pages/forms/Builder.vue';

const props = defineProps<{
    fieldtypes: Array,
}>();

const { fieldView, inspect } = injectBuilderContext();

const search = ref('');
const isSearching = computed(() => search.value.length > 0);

const allFieldtypes = computed(() => {
    let options = [...props.fieldtypes];

    options.push({
        handle: 'section',
        title: __('Section'),
        categories: ['structure'],
        keywords: [],
        icon: 'add-section',
        config: [],
    });

    // TODO: Only show this when Forms Pro is installed
    options.push({
        handle: 'page_break',
        title: __('Page Break'),
        categories: ['structure'],
        keywords: [],
        icon: 'page',
        config: [],
    });

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
        <div class="left-panel-popover min-[1000px]:hidden">
            <div id="popover-left-panel" class="left-panel-popover__menu" popover>
                <button class="left-panel-popover__close-button" title="Close" popovertarget="popover-left-panel">
                    <svg height="100pt" aria-hidden="true" viewBox="0 0 100 100" width="100pt" xmlns="http://www.w3.org/2000/svg"><path d="m91.668 13.676-5.3398-5.3398-36.328 36.324-36.328-36.324-5.3398 5.3398 36.328 36.324-36.328 36.324 5.3398 5.3398 36.328-36.324 36.328 36.324 5.3398-5.3398-36.328-36.324z"/></svg>
                </button>
                <ul class="bg-graph-paper px-0.5 grid gap-8 @container py-10 pb-40">
                    <li
                        v-for="group in displayedFieldtypes"
                        :key="group.handle"
                        v-show="group.fieldtypes.length > 0"
                    >
                        <h2
                            v-if="group.title"
                            class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium"
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
                                />
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <!-- This is the desktop nav - the content is repeated from the left panel -->
        <div class="fieldtype-source-container px-0.5 pt-6 max-[1000px]:hidden">
            <Input icon="magnifying-glass" :legible-text="false" input-class="rounded-xl" :placeholder="__('Search Field Types...')" v-model="search" />

            <ul class="py-10 grid gap-8 @container">
                <li
                    v-for="group in displayedFieldtypes"
                    :key="group.handle"
                    v-show="group.fieldtypes.length > 0"
                >
                    <h2
                        v-if="group.title"
                        class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium"
                        :class="fieldView === FieldView.Collapsed ? 'gap-1.5' : 'gap-0'"
                    >
                        <span
                            class="h-2 shrink-0 rounded-full"
                            :class="{
                                [categoryColorClasses[group.color].dot]: true,
                                'w-2 opacity-100': fieldView === 'collapsed',
                                'w-0 opacity-0': fieldView === 'expanded',
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
                            />
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</template>
