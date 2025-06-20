<script setup>
import { Button, Modal, Tooltip } from '@statamic/ui';
import { SortableList } from '@statamic/components/sortable/Sortable.js';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import { computed, ref } from 'vue';

const { preferencesPrefix, columns, visibleColumns, hiddenColumns, setColumns } = injectListingContext();
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
    <div>
        <Tooltip :text="__('Customize Columns')">
            <Button icon="sliders-vertical" @click="open = true" />
        </Tooltip>
        <Modal :title="__('Customize Columns')" v-model:open="open">
            <div class="flex h-full flex-col">
                <div class="dark:bg-dark-600 flex min-h-0 grow rounded-t-md bg-gray-100">
                    <!-- Available Columns -->
                    <div
                        class="dark:border-dark-900 flex w-1/2 flex-col outline-hidden ltr:border-r ltr:text-left rtl:border-l rtl:text-right"
                    >
                        <header
                            v-text="__('Available Columns')"
                            class="dark:border-dark-900 dark:bg-dark-700 border-b bg-white px-3 py-2 text-sm font-medium"
                        />
                        <div
                            class="flex flex-1 flex-col space-y-1 overflow-y-scroll px-3 py-2 shadow-inner select-none"
                        >
                            <div
                                class="column-picker-item"
                                v-for="column in sortedHiddenColumns"
                                :key="column.field"
                                v-if="hiddenColumns.length"
                            >
                                <label class="flex cursor-pointer items-center">
                                    <input type="checkbox" class="ltr:mr-2 rtl:ml-2" v-model="column.visible" />
                                    {{ __(column.label) }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Displayed Columns -->
                    <div class="flex w-1/2 flex-col">
                        <header
                            v-text="__('Displayed Columns')"
                            class="dark:border-dark-900 dark:bg-dark-700 flex-none border-b bg-white px-3 py-2 text-sm font-medium"
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
                                <div class="space-y-1 p-3 px-3 select-none">
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
                                                :disabled="selectedColumns.length === 1 || column.required"
                                            />
                                            {{ __(column.label) }}
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
