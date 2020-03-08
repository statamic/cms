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

        <div class="column-picker p-2">
            <sortable-list
                v-model="selectedColumns"
                :vertical="true"
                item-class="column-picker-item"
                handle-class="column-picker-item"
            >
                <div>
                    <div class="column-picker-item sortable" v-for="column in selectedColumns" :key="column.field">
                        <label><input type="checkbox" v-model="column.visible" @change="columnToggled(column)" :disabled="selectedColumns.length === 1" /> {{ column.label }}</label>
                    </div>
                </div>
            </sortable-list>

            <div v-if="hiddenColumns.length" class="mt-1">
                <div class="column-picker-item" v-for="column in hiddenColumns" :key="column.field">
                    <label><input type="checkbox" v-model="column.visible" @change="columnToggled(column) "/> {{ column.label }}</label>
                </div>
            </div>
        </div>

        <div v-if="preferencesKey" class="p-2 pt-0">
            <div class="flex justify-left">
                <button class="btn btn-sm btn-primary" @click="save" :disabled="saving">{{ __('Save') }}</button>
                <button class="btn btn-sm ml-1" @click="reset" :disabled="saving">{{ __('Reset') }}</button>
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

    inject: ['sharedState'],

    data() {
        return {
            saving: false,
            selectedColumns: [],
            hiddenColumns: [],
        }
    },

    created() {
        this.setLocalColumns();
    },

    watch: {
        selectedColumns: {
            deep: true,
            handler() {
                this.setSharedStateColumns();
            }
        }
    },

    methods: {

        setLocalColumns() {
            this.selectedColumns = this.sharedState.columns.filter(column => column.visible);
            this.hiddenColumns = this.sharedState.columns.filter(column => ! column.visible);
        },

        setSharedStateColumns() {
            this.sharedState.columns = [
                ...this.selectedColumns,
                ...this.hiddenColumns,
            ];
        },

        columnToggled(column) {
            let fromArray = column.visible ? this.hiddenColumns : this.selectedColumns;
            let toArray = column.visible ? this.selectedColumns : this.hiddenColumns;
            let currentIndex = _.findIndex(fromArray, { field: column.field });

            toArray.push(fromArray[currentIndex]);
            fromArray.splice(currentIndex, 1);

            this.hiddenColumns = _.sortBy(this.hiddenColumns, column => column.defaultOrder);
        },

        save() {
            if (! this.selectedColumns.length) {
                return this.$toast.error(__('At least 1 column is required'));
            }

            this.saving = true;

            this.$preferences.set(this.preferencesKey, this.selectedColumns.map(column => column.field))
                .then(response => {
                    this.saving = false;
                    this.$toast.success(__('Columns saved'));
                })
                .catch(error => {
                    this.saving = false;
                    this.$toast.error(__('Something went wrong'));
                });
        },

        reset() {
            this.sharedState.columns.forEach(column => column.visible = column.defaultVisibility);
            this.sharedState.columns = _.sortBy(this.sharedState.columns, column => column.defaultOrder);
            this.setLocalColumns();

            this.saving = true;

            this.$preferences.remove(this.preferencesKey)
                .then(response => {
                    this.saving = false;
                    this.$toast.success(__('Columns reset'));
                })
                .catch(error => {
                    this.saving = false;
                    this.$toast.error(__('Something went wrong'));
                });
        },

    }
}
</script>
