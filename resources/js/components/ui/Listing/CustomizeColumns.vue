<script setup>
import { Button, Modal, Tooltip } from '@/components/ui';
import { SortableList } from '@/components/sortable/Sortable.js';
import { injectListingContext } from '@/components/ui/Listing/Listing.vue';
import { computed, ref } from 'vue';

const { preferencesPrefix, columns, visibleColumns, hiddenColumns, setColumns, reorderable } = injectListingContext();
const preferencesKey = ref(`${preferencesPrefix.value}.columns`);
const saving = ref(false);
const open = ref(false);

const sortedHiddenColumns = computed(() => {
    return hiddenColumns.value.sort((a, b) => a.label.toLowerCase().localeCompare(b.label.toLowerCase()));
});

const selectedColumns = computed({
    get() {
        return visibleColumns.value;
    },
    set(selectedColumns) {
        setColumns([...selectedColumns, ...hiddenColumns.value]);
    },
});

function close() {
    open.value = false;
}

function save() {
    saving.value = true;

    Statamic.$preferences
        .set(
            preferencesKey.value,
            selectedColumns.value.map((column) => column.field),
        )
        .then(() => {
            saving.value = false;
            close();
            Statamic.$toast.success(__('These are now your default columns.'));
        })
        .catch(() => {
            saving.value = false;
            Statamic.$toast.error(__('Unable to save column preferences.'));
        });
}

function reset() {
    setColumns(
        columns.value
            .map((column) => ({ ...column, visible: column.defaultVisibility }))
            .sort((a, b) => a.defaultOrder - b.defaultOrder),
    );

    saving.value = true;

    Statamic.$preferences
        .remove(preferencesKey.value)
        .then(() => {
            saving.value = false;
            close();
            Statamic.$toast.success(__('Columns have been reset to their defaults.'));
        })
        .catch(() => {
            saving.value = false;
            Statamic.$toast.error(__('Unable to save column preferences.'));
        });
}
</script>

<template>
    <div data-ui-column-customizer class="absolute right-0 mask-bg mask-bg--left">
        <Tooltip :text="__('Customize Columns')">
            <Button icon="sliders-vertical" :disabled="reorderable" @click="open = true" :aria-label="__('Customize Columns')" />
        </Tooltip>
        <Modal :title="__('Customize Columns')" v-model:open="open">
            <div class="border rounded-lg dark:border-gray-900">
                <div class="flex">
                    <!-- Available Columns -->
                    <div class="flex w-1/2 flex-col text-start">
                        <ui-heading :text="__('Available Columns')" class="py-2 px-3 border-b dark:border-gray-900" />
                        <div class="flex flex-1 flex-col space-y-1 overflow-y-auto h-full px-3 py-2 select-none bg-gray-100 dark:bg-gray-900 rounded-bs-lg">
                            <ui-checkbox-item
                                v-model="column.visible"
                                :label="column.label"
                                v-for="column in sortedHiddenColumns"
                                :key="column.field"
                                class="column-picker-item"
                            />
                        </div>
                    </div>

                    <!-- Displayed Columns -->
                    <div class="flex w-1/2 flex-col text-start border-l dark:border-gray-700">
                        <ui-heading :text="__('Displayed Columns')" class="py-2 px-3 border-b dark:border-gray-900" />
                        <div class="overflow-y-auto bg-gray-100 dark:bg-gray-900 rounded-be-lg h-full">
                            <sortable-list
                                v-model="selectedColumns"
                                :distance="5"
                                :mirror="false"
                                :vertical="true"
                                item-class="item"
                                handle-class="item"
                                append-to="[data-ui-modal-content]"
                                :constrain-dimensions="true"
                            >
                                <div class="space-y-1.5 p-3 select-none">
                                    <div
                                        class="item sortable cursor-grab py-2 px-2.5 gap-3 relative rounded-lg bg-white dark:bg-gray-700 flex items-center justify-between text-xs shadow"
                                        v-for="column in selectedColumns"
                                        :key="column.field"
                                        tabindex="-1"
                                    >
                                        <ui-drag-handle class="item-move" />
                                        <div class="flex flex-1 items-center">
                                            <ui-checkbox-item
                                                v-model="column.visible"
                                                :label="column.label"
                                                :disabled="selectedColumns.length === 1 || column.required"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </sortable-list>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer v-if="preferencesKey">
                <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                    <Button :text="__('Cancel')" variant="ghost" @click="open = false" />
                    <Button :text="__('Reset')" @click="reset" :disabled="saving" />
                    <Button :text="__('Save')" variant="primary" @click="save" :disabled="saving" />
                </div>
            </template>
        </Modal>
    </div>
</template>
