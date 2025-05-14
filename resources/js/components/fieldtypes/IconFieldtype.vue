<template>
    <Combobox
        v-if="!loading"
        class="w-full"
        clearable
        :disabled="config.disabled || isReadOnly"
        :options="options"
        :placeholder="__(config.placeholder || 'Search...')"
        :searchable="true"
        :multiple="false"
        :model-value="value"
        @update:modelValue="comboboxUpdated"
    >
        <template #option="option">
            <div class="flex items-center">
                <svg-icon v-if="!option.html" :name="`${meta.set}/${option.label}`" class="size-4" />
                <div v-if="option.html" v-html="option.html" class="size-4" />
                <span class="truncate ms-3">
                    {{ __(option.label) }}
                </span>
            </div>
        </template>
        <template #selected-option="{ option }">
            <div class="flex items-center">
                <svg-icon
                    v-if="!option.html"
                    :name="`${meta.set}/${option.label}`"
                    class="flex items-center size-4"
                />
                <div v-if="option.html" v-html="option.html" class="size-4" />
                <span class="truncate text-sm text-gray-800 dark:text-gray-200 ms-3">
                    {{ __(option.label) }}
                </span>
            </div>
        </template>
    </Combobox>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { ref, watch } from 'vue';
import { Combobox } from '@statamic/ui';
const iconsCache = ref({});
const loaders = ref({});

export default {
    components: { Combobox },
    mixins: [Fieldtype],

    data() {
        return {
            icons: [],
            loading: true,
        };
    },

    computed: {
        cacheKey() {
            return `${this.meta.directory}/${this.meta.set}`;
        },

        options() {
            let options = [];
            for (let [name, html] of Object.entries(this.icons)) {
                options.push({
                    value: name,
                    label: name,
                    html,
                });
            }
            return options;
        },
    },

    created() {
        this.request();

        watch(
            () => loaders.value[this.cacheKey],
            (loading) => {
                this.icons = iconsCache.value[this.cacheKey];
                this.loading = loading;
            },
        );
    },

    methods: {
        comboboxUpdated(value) {
            this.update(value || null);
        },

        request() {
            if (loaders.value[this.cacheKey]) return;

            loaders.value = { ...loaders.value, [this.cacheKey]: true };

            this.$axios
                .post(this.meta.url, {
                    config: utf8btoa(JSON.stringify(this.config)),
                })
                .then((response) => {
                    const icons = response.data.icons;
                    this.icons = icons;
                    iconsCache.value = { ...iconsCache.value, [this.cacheKey]: icons };
                })
                .finally(() => {
                    loaders.value = { ...loaders.value, [this.cacheKey]: false };
                });
        },
    },
};
</script>
