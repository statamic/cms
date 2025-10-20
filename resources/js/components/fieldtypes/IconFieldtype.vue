<script setup>
import axios from 'axios';
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { computed, ref, watch } from 'vue';
import { Combobox, Icon } from '@/components/ui';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { isReadOnly, update } = Fieldtype.use(emit, props);

const icons = ref([]);
const loading = ref(true);
const loaders = ref({});
const iconsCache = ref({});
const cacheKey = computed(() => props.config.set ?? '__default__');

const options = computed(() => {
    let options = [];

    for (let [name, html] of Object.entries(icons.value)) {
        options.push({
            value: name,
            label: name,
            html,
        });
    }

    return options;
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

watch(
    () => loaders.value[cacheKey.value],
    (loadingState) => {
        icons.value = iconsCache.value[cacheKey.value];
        loading.value = loadingState;
    }
);

request();
</script>

<template>
    <Combobox
        v-if="!loading"
        class="w-full"
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
                    <div v-if="option.html" v-html="option.html" class="size-4" />
                </div>
                <span class="ms-3 truncate">
                    {{ __(option.label) }}
                </span>
            </div>
        </template>
        <template #selected-option="{ option }">
            <div class="flex items-center">
                <Icon v-if="!option.html" :name="option.label" class="flex size-4 items-center" />
                <div v-if="option.html" v-html="option.html" class="size-4" />
                <span class="ms-3 truncate text-sm text-gray-900 dark:text-gray-200">
                    {{ __(option.label) }}
                </span>
            </div>
        </template>
    </Combobox>
</template>
