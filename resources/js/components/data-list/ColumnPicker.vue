<template>
    <div>
        <button
            @click="open = true"
            v-tooltip="__('Customize Columns')"
            class="btn flex h-8 w-8 items-center justify-center px-1 py-1"
        >
            <svg-icon name="light/settings-horizontal" class="h-4 w-4" />
        </button>

        <modal
            v-if="open"
            name="column-picker"
            @closed="open = false"
            draggable=".modal-drag-handle"
            click-to-close
            v-slot="{ close }"
        >
            <div class="-max-h-screen-px flex h-full flex-col">
                <header
                    class="modal-drag-handle flex cursor-grab items-center justify-between border-b bg-gray-200 p-4 active:cursor-grabbing dark:border-dark-900 dark:bg-dark-650"
                >
                    <h2>{{ __('Customize Columns') }}</h2>
                    <button class="btn-close" @click="close" :aria-label="__('Close Editor')">&times;</button>
                </header>

                <div class="flex min-h-0 grow rounded-t-md bg-gray-100 dark:bg-dark-600">
                    <!-- Available Columns -->
                    <div
                        class="flex w-1/2 flex-col outline-none dark:border-dark-900 ltr:border-r ltr:text-left rtl:border-l rtl:text-right"
                    >
                        <header
                            v-text="__('Available Columns')"
                            class="border-b bg-white px-3 py-2 text-sm font-medium dark:border-dark-900 dark:bg-dark-700"
                        />
                        <div
                            class="flex flex-1 select-none flex-col space-y-1 overflow-y-scroll px-3 py-2 shadow-inner"
                        >
                            <div
                                class="column-picker-item"
                                v-for="column in hiddenColumns"
                                :key="column.field"
                                v-if="hiddenColumns.length"
                            >
                                <label class="flex cursor-pointer items-center">
                                    <input
                                        type="checkbox"
                                        class="ltr:mr-2 rtl:ml-2"
                                        v-model="column.visible"
                                        @change="columnToggled(column)"
                                    />
                                    {{ __(column.label) }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Displayed Columns -->
                    <div class="flex w-1/2 flex-col">
                        <header
                            v-text="__('Displayed Columns')"
                            class="flex-none border-b bg-white px-3 py-2 text-sm font-medium dark:border-dark-900 dark:bg-dark-700"
                        />
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
                                <div class="select-none space-y-1 p-3 px-3">
                                    <div
                                        class="item sortable cursor-grab"
                                        v-for="column in selectedColumns"
                                        :key="column.field"
                                        tabindex="-1"
                                    >
                                        <div class="item-move py-1">&nbsp;</div>
                                        <div class="flex flex-1 items-center p-0 ltr:ml-2 rtl:mr-2">
                                            <input
                                                type="checkbox"
                                                class="ltr:mr-2 rtl:ml-2"
                                                v-model="column.visible"
                                                @change="columnToggled(column)"
                                                :disabled="selectedColumns.length === 1"
                                            />
                                            {{ __(column.label) }}
                                        </div>
                                    </div>
                                </div>
                            </sortable-list>
                        </div>
                    </div>
                </div>

                <footer
                    class="flex items-center justify-end border-t px-3 py-2 dark:border-dark-900 dark:bg-dark-700"
                    v-if="preferencesKey"
                >
                    <button class="btn" v-text="__('Reset')" @click="reset" :disabled="saving" />
                    <button
                        class="btn-primary ltr:ml-3 rtl:mr-3"
                        v-text="__('Save')"
                        @click="save"
                        :disabled="saving"
                    />
                </footer>
            </div>
        </modal>
    </div>
</template>

<script>
import { SortableList } from '../sortable/Sortable';
import { sortBy } from 'lodash-es';

export default {
    components: {
        SortableList,
    },

    props: {
        preferencesKey: String,
    },

    inject: ['sharedState'],

    data() {
        return {
            saving: false,
            selectedColumns: [],
            hiddenColumns: [],
            open: false,
        };
    },

    created() {
        this.setLocalColumns();
    },

    watch: {
        selectedColumns: {
            deep: true,
            handler() {
                this.setSharedStateColumns();
            },
        },
    },

    methods: {
        setLocalColumns() {
            this.selectedColumns = this.sharedState.columns.filter((column) => column.visible);
            let hiddenColumns = this.sharedState.columns.filter((column) => !column.visible);
            this.hiddenColumns = sortBy(hiddenColumns, (column) => column.label.toLowerCase());
        },

        setSharedStateColumns() {
            this.sharedState.columns = [...this.selectedColumns, ...this.hiddenColumns];
        },

        columnToggled(column) {
            let fromArray = column.visible ? this.hiddenColumns : this.selectedColumns;
            let toArray = column.visible ? this.selectedColumns : this.hiddenColumns;
            let currentIndex = fromArray.findIndex((c) => c.field === column.field);

            toArray.push(fromArray[currentIndex]);
            fromArray.splice(currentIndex, 1);

            this.hiddenColumns = sortBy(this.hiddenColumns, (column) => column.label.toLowerCase());
        },

        save() {
            this.saving = true;

            this.$preferences
                .set(
                    this.preferencesKey,
                    this.selectedColumns.map((column) => column.field),
                )
                .then((response) => {
                    this.saving = false;
                    this.open = false;
                    this.$toast.success(__('These are now your default columns.'));
                })
                .catch((error) => {
                    this.saving = false;
                    this.$toast.error(__('Unable to save column preferences.'));
                });
        },

        reset() {
            this.sharedState.columns.forEach((column) => (column.visible = column.defaultVisibility));
            this.sharedState.columns = sortBy(this.sharedState.columns, (column) => column.defaultOrder);
            this.setLocalColumns();

            this.saving = true;

            this.$preferences
                .remove(this.preferencesKey)
                .then((response) => {
                    this.saving = false;
                    this.open = false;
                    this.$toast.success(__('Columns have been reset to their defaults.'));
                })
                .catch((error) => {
                    this.saving = false;
                    this.$toast.error(__('Unable to save column preferences.'));
                });
        },
    },
};
</script>
