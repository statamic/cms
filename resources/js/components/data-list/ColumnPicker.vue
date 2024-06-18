<template>
    <div>
        <button
            @click="open = true"
            v-tooltip="__('Customize Columns')"
            class="btn py-1 px-1 h-8 w-8 flex items-center justify-center"
        >
            <svg-icon name="light/settings-horizontal" class="w-4 h-4" />
        </button>

        <modal v-if="open" name="column-picker" @closed="open = false" draggable=".modal-drag-handle" click-to-close>
            <div class="flex flex-col h-full -max-h-screen-px">

                <header class="modal-drag-handle p-4 bg-gray-200 dark:bg-dark-650 border-b dark:border-dark-900 flex items-center justify-between cursor-grab active:cursor-grabbing">
                    <h2>{{ __('Customize Columns') }}</h2>
                    <button class="btn-close" @click="open = false" :aria-label="__('Close Editor')">&times;</button>
                </header>

                <div class="flex grow min-h-0 rounded-t-md bg-gray-100 dark:bg-dark-600">
                    <!-- Available Columns -->
                    <div class="outline-none rtl:text-right ltr:text-left w-1/2 rtl:border-l ltr:border-r dark:border-dark-900 flex flex-col">
                        <header v-text="__('Available Columns')" class="border-b dark:border-dark-900 py-2 px-3 text-sm bg-white dark:bg-dark-700 font-medium"/>
                        <div class="flex flex-1 flex-col space-y-1 py-2 px-3 select-none shadow-inner overflow-y-scroll">
                            <div class="column-picker-item" v-for="column in hiddenColumns" :key="column.field" v-if="hiddenColumns.length">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" class="rtl:ml-2 ltr:mr-2" v-model="column.visible" @change="columnToggled(column) "/>
                                    {{ __(column.label) }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Displayed Columns -->
                    <div class="w-1/2 flex flex-col">
                        <header v-text="__('Displayed Columns')" class="border-b dark:border-dark-900 px-3 py-2 text-sm bg-white dark:bg-dark-700 font-medium flex-none"/>
                        <div class="grow overflow-y-scroll shadow-inner">
                            <sortable-list
                                v-model="selectedColumns"
                                :vertical="true"
                                :distance="5"
                                item-class="item"
                                handle-class="item"
                                append-to=".modal-body"
                                constrain-dimensions
                            >
                                <div class="space-y-1 px-3 p-3 select-none">
                                    <div class="item sortable cursor-grab" v-for="column in selectedColumns" :key="column.field" tabindex="-1">
                                        <div class="item-move py-1">&nbsp;</div>
                                        <div class="flex flex-1 rtl:mr-2 ltr:ml-2 items-center p-0">
                                            <input type="checkbox" class="rtl:ml-2 ltr:mr-2" v-model="column.visible" @change="columnToggled(column)" :disabled="selectedColumns.length === 1" />
                                            {{ __(column.label) }}
                                        </div>
                                    </div>
                                </div>
                            </sortable-list>
                        </div>
                    </div>
                </div>

                <footer class="px-3 py-2 border-t dark:bg-dark-700 dark:border-dark-900 flex items-center justify-end" v-if="preferencesKey">
                    <button class="btn" v-text="__('Reset')" @click="reset" :disabled="saving" />
                    <button class="rtl:mr-3 ltr:ml-3 btn-primary" v-text="__('Save')" @click="save" :disabled="saving" />
                </footer>

            </div>
        </modal>
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

    inject: ['sharedState'],

    data() {
        return {
            saving: false,
            selectedColumns: [],
            hiddenColumns: [],
            open: false,
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
            let hiddenColumns = this.sharedState.columns.filter(column => ! column.visible);
            this.hiddenColumns = _.sortBy(hiddenColumns, column => column.label.toLowerCase());
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

            this.hiddenColumns = _.sortBy(this.hiddenColumns, column => column.label.toLowerCase());
        },

        save() {
            this.saving = true;

            this.$preferences.set(this.preferencesKey, this.selectedColumns.map(column => column.field))
                .then(response => {
                    this.saving = false;
                    this.open = false;
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
                    this.open = false;
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
