<template>
    <Combobox
        class="w-full"
        searchable
        ignore-filter
        :disabled="config.disabled"
        :max-selections="config.max_items"
        :model-value="value"
        :multiple
        :options="normalizedOptions"
        :placeholder="__(config.placeholder)"
        :read-only="isReadOnly"
        @update:modelValue="comboboxUpdated"
        @search="search"
    >
        <!--
            This slot is *basically* exactly the same as the default selected-options slot in Combobox. We're just looping
            through the Dictionary Fieldtype's selectedOptions state, rather than the one maintained by the Combobox component.
        -->
        <template #selected-options="{ disabled, getOptionLabel, getOptionValue, labelHtml, deselect }">
            <sortable-list
                v-if="multiple"
                item-class="sortable-item"
                handle-class="sortable-item"
                :distance="5"
                :mirror="false"
                :disabled
                :model-value="value"
                @update:modelValue="comboboxUpdated"
            >
                <div class="flex flex-wrap gap-2 pt-3">
                    <div
                        v-for="option in selectedOptions"
                        :key="getOptionValue(option)"
                        class="sortable-item cursor-grab"
                    >
                        <Badge size="lg" color="white">
                            <div v-if="labelHtml" v-html="getOptionLabel(option)"></div>
                            <div v-else>{{ __(getOptionLabel(option)) }}</div>

                            <button
                                v-if="!disabled"
                                type="button"
                                class="-mx-3 cursor-pointer px-3 text-gray-400 hover:text-gray-700"
                                :aria-label="__('Deselect option')"
                                @click="deselect(option.value)"
                            >
                                <span>&times;</span>
                            </button>
                            <button
                                v-else
                                type="button"
                                class="-mx-3 cursor-pointer px-3 text-gray-400 hover:text-gray-700"
                            >
                                <span>&times;</span>
                            </button>
                        </Badge>
                    </div>
                </div>
            </sortable-list>
        </template>
    </Combobox>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import HasInputOptions from './HasInputOptions.js';
import { SortableList } from '../sortable/Sortable';
import debounce from '@statamic/util/debounce.js';
import { Badge, Combobox } from '@statamic/cms/ui';

export default {
    mixins: [Fieldtype, HasInputOptions],

    components: {
        Badge,
        Combobox,
        SortableList,
    },

    data() {
        return {
            options: {},
            selectedOptionData: this.meta.selectedOptions,
        };
    },

    computed: {
        multiple() {
            return this.config.max_items !== 1;
        },

        normalizedOptions() {
            return this.normalizeInputOptions(this.options);
        },

        selectedOptions() {
            let selections = this.value || [];

            if (typeof selections === 'string' || typeof selections === 'number') {
                selections = [selections];
            }

            return selections.map((value) => {
                let option = this.selectedOptionData.find((option) => option.value === value);

                if (!option) return { value, label: value };

                return { value: option.value, label: option.label, invalid: option.invalid };
            });
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            return this.selectedOptions.map((option) => option.label).join(', ');
        },

        configParameter() {
            return utf8btoa(JSON.stringify(this.config));
        },
    },

    mounted() {
        this.request();
    },

    methods: {
        focus() {
            this.$refs.input.focus();
        },

        comboboxUpdated(value) {
            this.update(value || null);

            let selections = value || [];

            if (typeof selections === 'string' || typeof selections === 'number') {
                selections = [selections];
            }

            selections.forEach((value) => {
                if (this.selectedOptionData.find((option) => option.value === value)) {
                    return;
                }

                let option = this.normalizedOptions.find((option) => option.value === value);

                this.selectedOptionData.push(option);
            });
        },

        request(params = {}) {
            params = {
                config: this.configParameter,
                ...params,
            };

            return this.$axios.get(this.meta.url, { params }).then((response) => {
                this.options = response.data.data;
                return Promise.resolve(response);
            });
        },

        search: debounce(function (search, loading) {
            loading(true);

            this.request({ search }).then((response) => loading(false));
        }, 300),
    },
};
</script>
