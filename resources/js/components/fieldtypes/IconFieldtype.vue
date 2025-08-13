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
                <Icon v-if="!option.html" :name="getOptionIcon(option)" class="size-4" />
                <div v-if="option.html" v-html="option.html" class="size-4" />
                <span class="ms-3 truncate">
                    {{ __(option.label) }}
                </span>
            </div>
        </template>
        <template #selected-option="{ option }">
            <div class="flex items-center">
                <Icon v-if="!option.html" :name="getOptionIcon(option)" class="flex size-4 items-center" />
                <div v-if="option.html" v-html="option.html" class="size-4" />
                <span class="ms-3 truncate text-sm text-gray-900 dark:text-gray-200">
                    {{ __(option.label) }}
                </span>
            </div>
        </template>
    </Combobox>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { ref, watch } from 'vue';
import { Combobox, Icon } from '@statamic/ui';
const iconsCache = ref({});
const loaders = ref({});

export default {
    components: { Combobox, Icon },
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

        getOptionIcon(option) {
            if (this.meta.set) {
                return `${this.meta.set}/${option.label}`;
            }

            return option.label;
        },
    },
};
</script>
