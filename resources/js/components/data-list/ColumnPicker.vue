<template>
    <div>
        <button class="btn-flat btn-icon-only dropdown-toggle" @click="customizing = !customizing">
            <svg-icon name="settings-vertical" class="w-4 h-4 mr-1" />
            <span>{{ __('Columns') }}</span>
        </button>

        <pane name="columns" v-if="customizing" @closed="dismiss">
            <div class="flex flex-col h-full">

                <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
                    {{ __('Columns') }}
                    <button
                        type="button"
                        class="btn-close"
                        @click="dismiss"
                        v-html="'&times'" />
                </div>

                <div class="pt-2 overflow-y-auto">

                    <sortable-list
                        v-model="columns"
                        :vertical="true"
                        item-class="column-picker-item"
                        handle-class="column-picker-item"
                    >
                        <div>
                            <div class="column-picker-item column px-3" v-for="column in sharedState.columns" :key="column.field">
                                <label><input type="checkbox" v-model="column.visible" /> {{ column.label }}</label>
                            </div>
                        </div>
                    </sortable-list>

                    <div v-if="preferencesKey">
                        <loading-graphic class="mt-3 ml-3" v-if="saving" :inline="true" :text="__('Saving')" />
                        <template v-else>
                            <div class="flex justify-center p-3">
                                <button class="btn-flat w-full mr-sm block" @click="reset">{{ __('Reset') }}</button>
                                <button class="btn-flat w-full ml-sm block" @click="save">{{ __('Save') }}</button>
                            </div>
                        </template>
                    </div>

                </div>
            </div>
        </pane>
    </div>
</template>

<script>
import { SortableList } from '../sortable/Sortable';

export default {

    components: {
        SortableList
    },

    props: {
        preferencesKey: String
    },

    data() {
        return {
            customizing: false,
            saving: false,
        }
    },

    computed: {

        columns: {
            get() {
                return this.sharedState.columns;
            },
            set(columns) {
                this.sharedState.columns = columns;
            }
        },

        selectedColumns() {
            return this.sharedState.columns
                .filter(column => column.visible)
                .map(column => column.field);
        }

    },

    inject: ['sharedState'],

    methods: {

        dismiss() {
            this.customizing = false
        },

        save() {
            if (! this.selectedColumns.length) {
                return this.$toast.error(__('At least 1 column is required'));
            }

            this.saving = true;

            this.$preferences.set(this.preferencesKey, this.selectedColumns)
                .then(response => {
                    this.saving = false;
                    this.customizing = false;
                    this.$toast.success(__('Columns saved'));
                })
                .catch(error => {
                    this.saving = false;
                    this.$toast.error(__('Something went wrong'));
                });
        },

        reset() {
            this.sharedState.columns.forEach(column => column.visible = column.visibleDefault);

            this.saving = true;

            this.$preferences.remove(this.preferencesKey)
                .then(response => {
                    this.saving = false;
                    this.customizing = false;
                    this.$toast.success(__('Columns reset'));
                })
                .catch(error => {
                    this.saving = false;
                    this.$toast.error(__('Something went wrong'));
                });
        }
    }
}
</script>
