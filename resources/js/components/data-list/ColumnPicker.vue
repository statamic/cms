<template>
    <popover ref="popover" scroll strategy="fixed">

        <template slot="trigger">
            <button
                v-tooltip="__('Customize Columns')"
                class="btn py-1 px-1 h-8 w-8 flex items-center justify-center"
            >
                <svg-icon name="settings-horizontal" class="w-4 h-4" />
            </button>
        </template>

        <div class="column-picker rounded-t-md bg-gray-100 w-64">
            <header v-text="__('Displayed Columns')" class="border-b px-2 py-2 text-sm bg-white rounded-t-md font-medium"/>
            <sortable-list
                v-model="selectedColumns"
                :vertical="true"
                item-class="item"
                handle-class="item"
                append-to=".popover-content"
            >
                <div class="flex flex-col space-y-1 px-2 p-3 select-none">
                    <div class="item sortable cursor-grab" v-for="column in selectedColumns" :key="column.field">
                        <div class="item-move py-1">&nbsp;</div>
                        <div class="flex flex-1 ml-2 items-center p-0">
                            <input type="checkbox" class="mr-2" v-model="column.visible" @change="columnToggled(column)" :disabled="selectedColumns.length === 1" />
                            {{ column.label }}
                        </div>
                    </div>
                </div>
            </sortable-list>

            <div v-if="hiddenColumns.length" class="outline-none text-left">
                <header v-text="__('Available Columns')" class="border-y px-2 py-2 text-sm bg-white font-medium"/>
                <div class="flex flex-col space-y-1 py-2 px-3 select-none">
                    <div class="column-picker-item" v-for="column in hiddenColumns" :key="column.field">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" class="mr-2" v-model="column.visible" @change="columnToggled(column) "/>
                            {{ column.label }}
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex border-t text-gray-800" v-if="preferencesKey">
            <button
                class="p-2 hover:bg-gray-100 rounded-bl text-xs flex-1"
                v-text="__('Reset')"
                @click="reset" :disabled="saving"
            />
            <button
                class="p-2 hover:bg-gray-100 text-blue flex-1 rounded-br border-l text-xs"
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
