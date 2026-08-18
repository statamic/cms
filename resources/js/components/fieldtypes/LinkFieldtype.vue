<template>
    <div class="flex gap-2 sm:gap-3">
        <!-- Link type selector -->
        <div class="w-fit">
            <Select :options v-model="option" :adaptive-width="true" />
        </div>

        <div class="flex-1 flex">
            <Input v-if="option === 'url'" :read-only="isReadOnly" v-model="urlValue" />

            <component
                v-else-if="matchedType && matchedType.metaLoaded"
                :is="matchedTypeComponent"
                ref="typeField"
                :config="matchedType.config"
                :meta="matchedType.meta"
                :value="selectedByType[option]"
                :handle="option"
                @update:value="typeSelected"
                @update:meta="updateTypeMeta(option, $event)"
            />

            <Icon v-else-if="matchedType" name="loading" class="size-4 self-center" />
        </div>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { Icon, Input, Select } from '@/components/ui';
import debounce from '@/util/debounce.js';
import { markRaw } from 'vue';
import { UPDATE_DEBOUNCE_MS } from './constants';

export default {
    components: { Icon, Input, Select },
    mixins: [Fieldtype],

    provide: {
        isInLinkField: true,
    },

    data() {
        return {
            option: this.meta.initialOption,
            options: this.initialOptions(),
            urlValue: this.meta.initialUrl,
            selectedByType: this.initialSelectedByType(this.meta),
            metaChanging: false,
        };
    },

    created() {
        this.syncUrlDebounced = markRaw(debounce((url) => {
            this.update(url);
            this.updateMeta({ ...this.meta, initialUrl: url });
        }, UPDATE_DEBOUNCE_MS));

        if (this.matchedType) this.loadTypeMeta(this.option);
    },

    computed: {
        matchedType() {
            return this.meta.types[this.option] ?? null;
        },

        matchedTypeComponent() {
            return `${this.matchedType.component}-fieldtype`;
        },

        typeValue() {
            const selected = this.selectedByType[this.option];
            return selected && selected.length ? `${this.option}::${selected[0]}` : null;
        },

        replicatorPreview() {
            if (!this.showFieldPreviews) return;

            if (this.option === 'url') return this.urlValue;
            if (this.option === 'first-child') return __('First Child');

            if (this.matchedType) {
                return data_get(this.meta, `types.${this.option}.meta.data.0.title`)
                    ?? data_get(this.meta, `types.${this.option}.meta.data.0.basename`)
                    ?? this.typeValue;
            }

            return this.value;
        },
    },

    watch: {
        option(option, oldOption) {
            if (this.metaChanging) return;

            if (option === null) {
                this.update(null);
            } else if (option === 'url') {
                this.updateDebounced(this.urlValue);
            } else if (option === 'first-child') {
                this.update('@child');
            } else if (this.matchedType) {
                this.loadTypeMeta(option).then(() => {
                    if (this.option !== option) return;

                    this.typeValue
                        ? this.update(this.typeValue)
                        : this.$nextTick(() => this.openTypeSelector());
                });
            }

            this.updateMeta({ ...this.meta, initialOption: option });
        },

        urlValue(url) {
            if (this.metaChanging) return;
            this.syncUrlDebounced(url);
        },

        meta(meta, oldMeta) {
            if (meta === oldMeta) return;
            if (JSON.stringify(meta) === JSON.stringify(oldMeta)) return;

            this.metaChanging = true;
            this.urlValue = meta.initialUrl;
            this.option = meta.initialOption;
            this.selectedByType = this.initialSelectedByType(meta);
            this.$nextTick(() => (this.metaChanging = false));
        },
    },

    methods: {
        initialOptions() {
            return [
                this.config.required ? null : { label: __('None'), value: null },

                { label: __('URL'), value: 'url' },

                this.meta.showFirstChildOption ? { label: __('First Child'), value: 'first-child' } : null,

                ...Object.entries(this.meta.types).map(([handle, type]) => ({ label: type.title, value: handle })),
            ].filter((option) => option);
        },

        initialSelectedByType(meta) {
            return Object.fromEntries(
                Object.entries(meta.types).map(([handle, type]) => [handle, type.selected])
            );
        },

        loadTypeMeta(handle) {
            const type = this.meta.types[handle];

            if (!type || type.metaLoaded) return Promise.resolve();

            const params = {
                config: utf8btoa(JSON.stringify({ ...type.config, handle })),
                value: this.selectedByType[handle]?.[0] ?? null,
            };

            return this.$axios
                .post(cp_url('fields/field-meta'), params)
                .then((response) => this.updateTypeMeta(handle, response.data.meta))
                .catch((e) => this.$toast.error(e.response?.data?.message || __('Something went wrong')));
        },

        openTypeSelector() {
            const field = this.$refs.typeField;

            if (!field) return;

            if (typeof field.linkExistingItem === 'function') field.linkExistingItem();
            else if (typeof field.openSelector === 'function') field.openSelector();
        },

        typeSelected(selected) {
            this.selectedByType = { ...this.selectedByType, [this.option]: selected };
            this.update(this.typeValue);
            this.updateMeta({
                ...this.meta,
                types: {
                    ...this.meta.types,
                    [this.option]: { ...this.meta.types[this.option], selected },
                },
            });
        },

        updateTypeMeta(handle, typeMeta) {
            this.updateMeta({
                ...this.meta,
                types: {
                    ...this.meta.types,
                    [handle]: { ...this.meta.types[handle], meta: typeMeta, metaLoaded: true },
                },
            });
        },
    },
};
</script>
