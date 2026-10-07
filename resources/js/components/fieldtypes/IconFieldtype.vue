<script setup>
import axios from 'axios';
import fuzzysort from 'fuzzysort';
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { computed, ref, watch } from 'vue';
import { Button, Combobox, Icon, Input, Modal } from '@/components/ui';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { isReadOnly, update } = Fieldtype.use(emit, props);

const icons = ref([]);
const loading = ref(true);
const loaders = ref({});
const iconsCache = ref({});
const cacheKey = computed(() => props.config.set ?? '__default__');
const isCompact = computed(() => props.config.mode === 'compact');
const isOpen = ref(false);
const search = ref('');

const options = computed(() => {
    let options = [];

    for (let [name, html] of Object.entries(icons.value || {})) {
        options.push({
            value: name,
            label: name,
            html,
        });
    }

    return options;
});

const selectedOption = computed(() => {
    return options.value.find((option) => option.value === props.value) ?? null;
});

const noneOption = { value: null, label: 'No Icon', none: true };

const filteredOptions = computed(() => {
    const matches = fuzzysort
        .go(search.value, options.value, {
            all: true,
            key: 'label',
        })
        .map((result) => result.obj);

    return search.value ? matches : [noneOption, ...matches];
});

function request() {
    if (loaders.value[cacheKey.value]) return;

    loaders.value = { ...loaders.value, [cacheKey.value]: true };

    axios
        .post(props.meta.url, {
            config: utf8btoa(JSON.stringify(props.config)),
        })
        .then((response) => {
            icons.value = response.data.icons;
            iconsCache.value = { ...iconsCache.value, [cacheKey.value]: response.data.icons };
        })
        .finally(() => {
            loaders.value = { ...loaders.value, [cacheKey.value]: false };
        });
}

function comboboxUpdated(value) {
    update(value || null);
}

function openPicker() {
    if (isReadOnly.value || props.config.disabled) return;

    isOpen.value = true;
}

function selectIcon(name) {
    if (isReadOnly.value || props.config.disabled) return;

    update(name);
    isOpen.value = false;
    search.value = '';
}

watch(
    () => loaders.value[cacheKey.value],
    (loadingState) => {
        icons.value = iconsCache.value[cacheKey.value];
        loading.value = loadingState;
    }
);

watch(isOpen, (open) => {
    if (!open) search.value = '';
});

request();
</script>

<template>
    <Combobox
        v-if="!loading && !isCompact"
        clearable
        :disabled="config.disabled"
        :model-value="value"
        :multiple="false"
        :options="options"
        :placeholder="__(config.placeholder || 'Search...')"
        :read-only="isReadOnly"
        :searchable="true"
        @update:modelValue="comboboxUpdated"
    >
        <template #option="option">
            <div class="flex items-center">
                <div class="size-4">
                    <Icon v-if="!option.html" :name="option.label" class="size-4" />
                    <div v-if="option.html" v-html="option.html" class="[&>svg]:size-4" />
                </div>
                <span class="ms-3 truncate">
                    {{ __(option.label) }}
                </span>
            </div>
        </template>
        <template #selected-option="{ option }">
            <div class="flex items-center">
                <Icon v-if="!option.html" :name="option.label" class="flex size-4 items-center" />
                <div v-if="option.html" v-html="option.html" class="[&>svg]:size-4" />
                <span class="ms-3 truncate text-sm text-gray-900 dark:text-gray-200">
                    {{ __(option.label) }}
                </span>
            </div>
        </template>
    </Combobox>

    <div v-else-if="!loading" class="flex items-center">
        <Button
            icon-only
            size="base"
            :aria-label="value || __('Select Icon')"
            :disabled="config.disabled"
            :read-only="isReadOnly"
            :title="value || undefined"
            @click="openPicker"
        >
            <Icon v-if="selectedOption && !selectedOption.html" :name="selectedOption.label" />
            <div
                v-else-if="selectedOption?.html"
                v-html="selectedOption.html"
                class="flex items-center justify-center [&>svg]:size-4.5"
            />
            <Icon v-else name="plus" class="opacity-40" />
        </Button>

        <Modal
            v-model:open="isOpen"
            :blur="false"
            :title="__('Select Icon')"
            class="xl:max-w-3xl 2xl:max-w-page"
        >
            <Input
                v-model="search"
                :placeholder="__('Search...')"
                icon-prepend="magnifying-glass"
                size="sm"
                type="text"
            />

            <div class="st-custom-scrollbar max-h-[40vh] overflow-auto">
                <div class="grid grid-cols-6 gap-1 sm:grid-cols-8 md:grid-cols-10 lg:grid-cols-12">
                    <button
                        v-for="option in filteredOptions"
                        :key="option.none ? '__none__' : option.value"
                        type="button"
                        class="flex size-9 items-center justify-center rounded-lg"
                        :aria-label="__(option.label)"
                        :aria-pressed="option.value === value"
                        :class="{
                            'bg-gray-100 dark:bg-gray-900': option.value === value,
                            'hover:bg-gray-100 dark:hover:bg-gray-900': option.value !== value,
                        }"
                        :title="__(option.label)"
                        @click="selectIcon(option.value)"
                    >
                        <Icon v-if="option.none" name="x" class="size-3.5 opacity-40" />
                        <Icon v-else-if="!option.html" :name="option.label" class="size-5" />
                        <div v-else v-html="option.html" class="[&>svg]:size-5" />
                    </button>
                </div>
                <div v-if="filteredOptions.length === 0" class="p-3 text-center text-xs text-gray-600">
                    {{ search ? __('No results') : __('No icons available') }}
                </div>
            </div>
        </Modal>
    </div>
</template>
