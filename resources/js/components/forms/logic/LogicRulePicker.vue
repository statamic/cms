<script setup>
import { Primitive } from 'reka-ui';
import fuzzysort from 'fuzzysort';
import { computed, ref, watch, onBeforeUnmount } from 'vue';
import { keys } from '@api';
import FieldNumber from '@/components/forms/FieldNumber.vue';

const emit = defineEmits(['added']);

const props = defineProps({
    items: { type: Array, default: () => [] },
    align: { type: String, default: 'start' },
    searchPlaceholder: { type: String, default: null },
});

const search = ref(null);
const selectionIndex = ref(0);
const keybindings = ref([]);
const isOpen = ref(false);

const hasMultipleItems = computed(() => props.items.length > 1);

const visibleItems = computed(() => {
    let items = props.items.filter((item) => !item.hide);
    if (search.value) {
        items = fuzzysort
            .go(search.value, items, {
                all: true,
                keys: [(item) => __(item.display), 'handle', (item) => __(item.instructions)],
                scoreFn: (scores) => {
                    const displayScore = scores[0]?.score ?? -Infinity;
                    const handleScore = scores[1]?.score ?? -Infinity;
                    const instructionsScore = (scores[2]?.score ?? -Infinity) * 0.5;
                    return Math.max(displayScore, handleScore, instructionsScore);
                },
            })
            .map((result) => result.obj);
    }
    return items;
});

const noSearchResults = computed(() => search.value && visibleItems.value.length === 0);
const searchPlaceholderText = computed(() => props.searchPlaceholder ? __(props.searchPlaceholder) : __('Search'));

const selectItem = (handle) => {
    emit('added', handle);
    isOpen.value = false;
    search.value = null;
};

const singleButtonClicked = () => selectItem(props.items[0].handle);

const bindKeys = () => {
    keybindings.value = [
        keys.bindGlobal('up', keypressUp),
        keys.bindGlobal('down', keypressDown),
        keys.bindGlobal('enter', keypressEnter),
    ];
};

const unbindKeys = () => {
    keybindings.value.forEach((binding) => binding.destroy());
    keybindings.value = [];
};

const keypressUp = (e) => {
    e.preventDefault();
    selectionIndex.value = selectionIndex.value === 0
        ? visibleItems.value.length - 1
        : selectionIndex.value - 1;
};

const keypressDown = (e) => {
    e.preventDefault();
    selectionIndex.value = selectionIndex.value === visibleItems.value.length - 1
        ? 0
        : selectionIndex.value + 1;
};

const keypressEnter = (e) => {
    e.preventDefault();
    const item = visibleItems.value[selectionIndex.value];
    if (item) selectItem(item.handle);
};

watch(isOpen, (open) => {
    if (open) bindKeys();
    else unbindKeys();
});

watch(search, () => selectionIndex.value = 0);

onBeforeUnmount(() => unbindKeys());
</script>

<template>
    <template v-if="!hasMultipleItems">
        <Primitive as-child @click="singleButtonClicked">
            <slot name="trigger" />
        </Primitive>
    </template>

    <ui-popover
        v-else
        :align="align"
        :open="isOpen"
        @update:open="isOpen = $event"
        class="select-none w-72 rounded-b-lg"
        inset
    >
        <template #trigger>
            <slot name="trigger" />
        </template>

        <template #default>
            <div class="flex items-center border-b border-gray-200 dark:border-gray-600 p-1.5 gap-1.5">
                <ui-input
                    :placeholder="searchPlaceholderText"
                    class="[&_svg]:size-5"
                    icon-prepend="magnifying-glass"
                    size="sm"
                    type="text"
                    v-model="search"
                    variant="ghost"
                />
            </div>

            <div class="max-h-[21rem] overflow-auto p-1.5 st-custom-scrollbar">
                <div
                    v-for="(item, i) in visibleItems"
                    :key="item.handle"
                    class="cursor-pointer rounded-md"
                    :class="{ 'bg-gray-100 dark:bg-gray-900': selectionIndex === i }"
                    @mouseover="selectionIndex = i"
                    :title="__(item.instructions)"
                >
                    <div
                        @click="selectItem(item.handle)"
                        class="flex items-center rounded-lg p-2.5 gap-2 sm:gap-3 cursor-pointer"
                    >
                        <ui-icon :name="item.icon || 'plus'" class="size-4" :class="item.iconClass || 'text-gray-600 dark:text-gray-300'" />
                        <div class="flex-1">
                            <div class="line-clamp-1 text-sm text-gray-900 dark:text-gray-200">
                                <FieldNumber :field-key="item.handle" class="me-1" />{{ __(item.display || item.handle) }}
                            </div>
                            <ui-description v-if="item.instructions" class="w-56 truncate text-2xs">
                                {{ __(item.instructions) }}
                            </ui-description>
                        </div>
                    </div>
                </div>
                <div v-if="noSearchResults" class="p-3 text-center text-xs text-gray-600">
                    {{ __('No results') }}
                </div>
            </div>
        </template>
    </ui-popover>
</template>
