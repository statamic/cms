<template>
    <popover>

        <template slot="trigger">
            <button
                v-tooltip="__('Customize Columns')"
                class="btn btn-sm px-sm py-sm -ml-sm cursor-pointer"
            >
                <svg-icon name="settings-horizontal" class="w-4" />
            </button>
        </template>

        <div class="p-2">

            <sortable-list
                v-model="columns"
                :vertical="true"
                item-class="column-picker-item"
                handle-class="column-picker-item"
            >
                <div>
                    <div class="column-picker-item draggable-item column" v-for="column in selectedColumns" :key="column.field">
                        <label><input type="checkbox" v-model="column.visible" /> {{ column.label }}</label>
                    </div>
                </div>
            </sortable-list>

            <div v-if="hiddenColumns.length" class="mt-1">
                <div class="column-picker-item column" v-for="column in hiddenColumns" :key="column.field">
                    <label><input type="checkbox" v-model="column.visible" /> {{ column.label }}</label>
                </div>
            </div>

            <div v-if="preferencesKey">
                <loading-graphic v-if="saving" :inline="true" :text="__('Saving')" />
                <template v-else>
                    <div class="flex justify-left mt-2">
                        <button class="btn btn-sm" @click="reset">{{ __('Reset') }}</button>
                        <button class="btn btn-sm ml-1" @click="save">{{ __('Save') }}</button>
                    </div>
                </template>
            </div>

        </div>

    </popover>
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
            return this.sharedState.columns.filter(column => column.visible);
        },

        hiddenColumns() {
            return this.sharedState.columns.filter(column => ! column.visible);
        },

    },

    inject: ['sharedState'],

    methods: {

        save() {
            if (! this.selectedColumns.length) {
                return this.$toast.error(__('At least 1 column is required'));
            }

            this.saving = true;

            this.$preferences.set(this.preferencesKey, this.selectedColumns.map(column => column.field))
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
