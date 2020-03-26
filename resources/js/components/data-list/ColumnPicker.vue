<template>
    <popover ref="popover">

        <template slot="trigger">
            <button
                v-tooltip="__('Customize Columns')"
                class="btn btn-sm px-sm mt-1 py-sm -ml-sm cursor-pointer"
            >
                <svg-icon name="settings-horizontal" class="w-4" />
            </button>
        </template>

        <div class="column-picker">
            <sortable-list
                v-model="selectedColumns"
                :vertical="true"
                item-class="column-picker-item"
                handle-class="column-picker-item"
                append-to=".popover-content"
            >
                <div class="outline-none text-left px-1 py-1">
                    <h6 v-text="__('Displayed Columns')" class="p-1"/>
                    <div class="column-picker-item sortable" v-for="column in selectedColumns" :key="column.field">
                        <label>
                            <input type="checkbox" class="mr-1" v-model="column.visible" @change="columnToggled(column)" :disabled="selectedColumns.length === 1" />
                            {{ column.label }}
                        </label>
                    </div>
                </div>
            </sortable-list>

            <div v-if="hiddenColumns.length" class="outline-none text-left px-1 pb-1">
                <h6 v-text="__('Available Columns')" class="px-1 pb-1"/>
                <div class="column-picker-item" v-for="column in hiddenColumns" :key="column.field">
                    <label class="cursor-pointer">
                        <input type="checkbox" class="mr-1" v-model="column.visible" @change="columnToggled(column) "/>
                        {{ column.label }}
                    </label>
                </div>
            </div>
        </div>

        <div class="flex border-t text-grey-80" v-if="preferencesKey">
            <button
                class="p-1 hover:bg-grey-10 rounded-bl text-xs flex-1"
                v-text="__('Reset')"
                @click="reset" :disabled="saving"
            />
            <button
                class="p-1 hover:bg-grey-10 text-blue flex-1 rounded-br border-l text-xs"
                v-text="__('Save')"
                @click="save" :disabled="saving"
            />
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
            this.saving = true;

            this.$preferences.set(this.preferencesKey, this.selectedColumns.map(column => column.field))
                .then(response => {
                    this.saving = false;
                    this.$refs.popover.close();
                    this.$toast.success(__('These are now your default columns.'));
                })
                .catch(error => {
                    this.saving = false;
                    this.$toast.error(__('Unable to save column preferences.'));
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
                    this.$refs.popover.close();
                    this.$toast.success(__('Columns have been reset to their defaults.'));
                })
                .catch(error => {
                    this.saving = false;
                    this.$toast.error(__('Unable to save column preferences.'));
                });
        },

    }
}
</script>
