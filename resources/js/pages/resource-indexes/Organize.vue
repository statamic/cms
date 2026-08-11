<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Sortable, Plugins } from '@shopify/draggable';
import { nanoid } from 'nanoid';
import axios from 'axios';
import { deepClone } from '@/util/clone.js';
import Head from '@/pages/layout/Head.vue';
import SortableList from '@/components/sortable/SortableList.vue';
import {
    addResourceIndexItems,
    isResourceIndexFallbackReorder,
    moveResourceIndexItem,
    removeResourceIndexItem,
    unassignedResourceIndexItems,
} from '@/components/resource-indexes/organize-groups.js';
import {
    Button,
    Card,
    Combobox,
    ConfirmationModal,
    Dropdown,
    DropdownItem,
    DropdownMenu,
    ErrorMessage,
    Field,
    Header,
    Heading,
    Icon,
    Input,
    Panel,
    PanelHeader,
    Stack,
    Subheading,
} from '@ui';

const props = defineProps({
    resourceIndex: Object,
    items: Array,
    groups: Array,
    hasSavedGroups: Boolean,
    cancelUrl: String,
    updateUrl: String,
    resetUrl: String,
});

const organizer = ref();
const groups = ref(deepClone(props.groups));
const initialGroups = ref(deepClone(props.groups));
const hasSavedGroups = ref(props.hasSavedGroups);
const errors = ref({});
const saving = ref(false);
const confirmingReset = ref(false);
const editingGroupId = ref(null);
const editingGroupTitle = ref('');
const editingNewGroup = ref(false);
const groupTitleError = ref(null);
const addingToGroupId = ref(null);
const addingItemIds = ref([]);
const fallbackRenderKey = ref(0);
const ignoringDirtyState = ref(false);
let itemsSortable;

const title = computed(() => __('Organize :resource', { resource: props.resourceIndex.title }));
const itemLabel = computed(() => props.resourceIndex.itemLabel || __('Item'));
const addItemText = computed(() => __('Add :item', { item: itemLabel.value }));
const isDirty = computed(() => JSON.stringify(groups.value) !== JSON.stringify(initialGroups.value));
const dirtyStateKey = `resource-index-organization-${props.resourceIndex.handle}`;

const itemOptions = computed(() => {
    const options = props.items.map((item) => ({
        id: String(item.id),
        title: item.title,
        icon: item.icon || props.resourceIndex.icon,
    }));
    const knownIds = new Set(options.map((item) => item.id));

    groups.value
        .flatMap((group) => group.items)
        .forEach((id) => {
            id = String(id);
            if (knownIds.has(id)) return;

            knownIds.add(id);
            options.push({
                id,
                title: __('Unavailable (:id)', { id }),
                icon: props.resourceIndex.icon,
                unavailable: true,
            });
        });

    return options;
});

const itemsById = computed(() => new Map(itemOptions.value.map((item) => [item.id, item])));
const fallbackItems = computed(() => unassignedResourceIndexItems(itemOptions.value, groups.value));
const itemPickerOptions = computed(() => {
    const group = groups.value.find((group) => group.id === addingToGroupId.value);
    if (!group) return [];

    const existingIds = new Set(group.items.map(String));

    return props.items
        .map((item) => ({ id: String(item.id), title: item.title }))
        .filter((item) => !existingIds.has(item.id));
});

watch(isDirty, (dirty) => Statamic.$dirty.state(dirtyStateKey, ignoringDirtyState.value ? false : dirty), { immediate: true });

watch(
    () => props.groups,
    (value) => {
        groups.value = deepClone(value);
        initialGroups.value = deepClone(value);
        hasSavedGroups.value = props.hasSavedGroups;
        errors.value = {};
    },
    { deep: true },
);

watch(groups, () => nextTick(setupItemsSortable), { deep: true });

function itemsFor(group) {
    return group.items
        .map((id) => itemsById.value.get(String(id)))
        .filter(Boolean);
}

function addGroup() {
    editingGroupId.value = nanoid(10);
    editingGroupTitle.value = __('New Group');
    editingNewGroup.value = true;
    groupTitleError.value = null;
}

function editGroup(group, isNew = false) {
    editingGroupId.value = group.id;
    editingGroupTitle.value = group.title;
    editingNewGroup.value = isNew;
    groupTitleError.value = null;
}

function closeGroupEditor() {
    editingGroupId.value = null;
    editingGroupTitle.value = '';
    editingNewGroup.value = false;
    groupTitleError.value = null;
}

function updateGroupTitle() {
    const title = editingGroupTitle.value.trim();

    if (!title) {
        groupTitleError.value = __('statamic::validation.required');
        return;
    }

    if (editingNewGroup.value) {
        groups.value.push({
            id: editingGroupId.value,
            title,
            items: [],
        });
    } else {
        const group = groups.value.find((group) => group.id === editingGroupId.value);
        if (group) group.title = title;
    }

    closeGroupEditor();
}

function removeGroup(index) {
    groups.value.splice(index, 1);
}

function removeItem(groupId, itemId) {
    removeResourceIndexItem(groups.value, groupId, itemId);
}

function openItemPicker(group) {
    addingToGroupId.value = group.id;
    addingItemIds.value = [];
}

function closeItemPicker() {
    addingToGroupId.value = null;
    addingItemIds.value = [];
}

function addItems() {
    addResourceIndexItems(groups.value, addingToGroupId.value, addingItemIds.value);
    closeItemPicker();
}

function setupItemsSortable() {
    destroyItemsSortable();

    const itemContainers = organizer.value?.querySelectorAll('.resource-index-item-zone');

    if (itemContainers?.length) {
        itemsSortable = new Sortable(itemContainers, {
            draggable: '.resource-index-item',
            handle: '.resource-index-item-handle',
            mirror: { constrainDimensions: true, appendTo: 'body' },
            plugins: [Plugins.SwapAnimation],
        })
            .on('sortable:sort', preventFallbackReorder)
            .on('sortable:stop', itemDropped);
    }
}

function preventFallbackReorder(event) {
    const currentGroupId = event.dragEvent.source.parentElement?.dataset.group;
    const overGroupId = event.overContainer?.dataset.group;
    const fallbackGroupId = props.resourceIndex.fallbackGroup.id;

    if (isResourceIndexFallbackReorder(currentGroupId, overGroupId, fallbackGroupId)) {
        event.cancel();
    }
}

function itemDropped({ oldContainer, newContainer, oldIndex, newIndex }) {
    const oldGroupId = oldContainer.dataset.group;
    const newGroupId = newContainer.dataset.group;
    const fallbackGroupId = props.resourceIndex.fallbackGroup.id;

    if (isResourceIndexFallbackReorder(oldGroupId, newGroupId, fallbackGroupId)) {
        fallbackRenderKey.value++;
        nextTick(setupItemsSortable);

        return;
    }

    const itemId = oldGroupId === fallbackGroupId
        ? fallbackItems.value[oldIndex]?.id
        : groups.value.find((group) => group.id === oldGroupId)?.items[oldIndex];

    if (itemId === undefined) return;

    moveResourceIndexItem(groups.value, {
        itemId,
        oldGroupId,
        oldIndex,
        newGroupId,
        newIndex,
        fallbackGroupId,
    });
}

function destroyItemsSortable() {
    itemsSortable?.destroy();
    itemsSortable = null;
}

function save() {
    if (saving.value || !isDirty.value) return;

    saving.value = true;
    errors.value = {};

    axios.patch(props.updateUrl, { groups: groups.value })
        .then(() => {
            initialGroups.value = deepClone(groups.value);
            hasSavedGroups.value = true;
            ignoringDirtyState.value = true;
            Statamic.$dirty.remove(dirtyStateKey);
            Statamic.$dirty.disableWarning();
            saving.value = false;
            router.visit(props.cancelUrl, {
                onSuccess: () => Statamic.$toast.success(__('Saved')),
            });
        })
        .catch((error) => {
            errors.value = error.response?.data?.errors ?? {};
            Statamic.$toast.error(error.response?.data?.message ?? __('Something went wrong'));
        })
        .finally(() => {
            if (saving.value) saving.value = false;
        });
}

function discardChanges() {
    Statamic.$dirty.remove(dirtyStateKey);
    Statamic.$dirty.disableWarning();
    router.visit(props.cancelUrl);
}

function reset() {
    confirmingReset.value = false;
    saving.value = true;

    axios.delete(props.resetUrl)
        .then(() => {
            Statamic.$toast.success(__('Reset'));
            initialGroups.value = deepClone(groups.value);
            Statamic.$dirty.remove(dirtyStateKey);
            router.reload();
        })
        .catch((error) => Statamic.$toast.error(error.response?.data?.message ?? __('Something went wrong')))
        .finally(() => saving.value = false);
}

const saveKeyBinding = Statamic.$keys.bindGlobal(['mod+s'], (event) => {
    if (!isDirty.value) return;

    event.preventDefault();
    save();
});

onMounted(setupItemsSortable);

onBeforeUnmount(() => {
    destroyItemsSortable();
    saveKeyBinding.destroy();
    Statamic.$dirty.remove(dirtyStateKey);
});
</script>

<template>
    <Head :title="title" />

    <div ref="organizer" class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Header :title="title" :icon="resourceIndex.icon">
            <Dropdown v-if="hasSavedGroups" align="end">
                <DropdownMenu>
                    <DropdownItem
                        :text="__('Reset Groups')"
                        icon="history"
                        variant="destructive"
                        @click="confirmingReset = true"
                    />
                </DropdownMenu>
            </Dropdown>
            <Button
                v-if="!isDirty"
                :text="__('Cancel')"
                :href="cancelUrl"
                :disabled="saving"
            />
            <Button
                v-if="isDirty"
                :text="__('Discard Changes')"
                variant="filled"
                :disabled="saving"
                @click="discardChanges"
            />
            <Button
                :text="__('Save')"
                variant="primary"
                :loading="saving"
                :disabled="!isDirty"
                @click="save"
            />
        </Header>

        <SortableList
            v-model="groups"
            item-class="resource-index-group"
            handle-class="resource-index-group-handle"
            append-to="body"
            vertical
            constrain-dimensions
            :disabled="groups.length < 2"
        >
            <div>
                <Panel
                    v-for="(group, index) in groups"
                    :key="group.id"
                    class="resource-index-group"
                >
                    <PanelHeader class="flex items-center justify-between pl-2.75! pr-3.25!">
                        <div class="flex min-w-0 flex-1 items-center gap-2">
                            <Icon
                                name="handles-sm"
                                class="resource-index-group-handle size-3! shrink-0 cursor-grab text-gray-400"
                            />
                            <Heading :text="group.title" />
                            <ErrorMessage
                                v-if="errors[`groups.${index}.title`]"
                                :text="errors[`groups.${index}.title`][0]"
                            />
                        </div>
                        <Button
                            icon="pencil-line"
                            size="sm"
                            variant="ghost"
                            :aria-label="__('Edit Group')"
                            @click="editGroup(group)"
                        />
                        <Button
                            icon="trash"
                            size="sm"
                            variant="ghost"
                            :aria-label="__('Delete Group')"
                            @click.prevent="removeGroup(index)"
                        />
                    </PanelHeader>

                    <div
                        class="resource-index-item-zone space-y-2 mb-4 outline-hidden"
                        :data-group="group.id"
                        tabindex="-1"
                    >
                        <Subheading
                            v-if="!group.items.length"
                            :text="__('Add or drag items here.')"
                            class="rounded-xl min-h-16 flex items-center justify-center border border-dashed border-gray-300 dark:border-gray-700 p-3 text-center w-full"
                        />

                        <Card
                            v-for="item in itemsFor(group)"
                            :key="`${group.id}:${item.id}`"
                            class="resource-index-item py-0.75! px-2!"
                        >
                            <div class="flex items-center gap-2">
                                <Icon
                                    name="handles"
                                    class="resource-index-item-handle size-4 shrink-0 cursor-grab text-gray-300 dark:text-gray-600"
                                />
                                <div class="flex min-w-0 flex-1 items-center justify-between gap-2">
                                    <div class="flex min-w-0 flex-1 items-center py-2">
                                        <Icon
                                            :name="item.icon"
                                            class="size-4 shrink-0 me-2 text-gray-500 dark:text-gray-400"
                                        />
                                        <span
                                            class="min-w-0 truncate text-sm"
                                            :class="{ 'text-gray-500': item.unavailable }"
                                        >{{ item.title }}</span>
                                    </div>
                                    <Button
                                        inset
                                        icon="trash"
                                        size="sm"
                                        variant="subtle"
                                        :aria-label="__('Remove')"
                                        v-tooltip="__('Remove')"
                                        @click.prevent="removeItem(group.id, item.id)"
                                    />
                                </div>
                            </div>
                        </Card>
                    </div>

                    <div class="flex gap-2">
                        <Button icon="add-circle" :text="addItemText" @click="openItemPicker(group)" />
                    </div>

                    <ErrorMessage
                        v-if="errors[`groups.${index}.items`]"
                        class="mt-3"
                        :text="errors[`groups.${index}.items`][0]"
                    />
                </Panel>
            </div>
        </SortableList>

        <Panel>
            <PanelHeader class="pl-2.75! pr-3.25!">
                <Heading :text="resourceIndex.fallbackGroup.title" />
            </PanelHeader>

            <div
                :key="fallbackRenderKey"
                class="resource-index-item-zone space-y-2 min-h-16 outline-hidden"
                :data-group="resourceIndex.fallbackGroup.id"
                tabindex="-1"
            >
                <Subheading
                    v-if="!fallbackItems.length"
                    :text="__('Drag items here.')"
                    class="rounded-xl min-h-16 flex items-center justify-center border border-dashed border-gray-300 dark:border-gray-700 p-3 text-center w-full"
                />

                <Card
                    v-for="item in fallbackItems"
                    :key="`${resourceIndex.fallbackGroup.id}:${item.id}`"
                    class="resource-index-item py-0.75! px-2!"
                >
                    <div class="flex items-center gap-2">
                        <Icon
                            name="handles"
                            class="resource-index-item-handle size-4 shrink-0 cursor-grab text-gray-300 dark:text-gray-600"
                        />
                        <div class="flex min-w-0 flex-1 items-center py-2">
                            <Icon
                                :name="item.icon"
                                class="size-4 shrink-0 me-2 text-gray-500 dark:text-gray-400"
                            />
                            <span class="min-w-0 truncate text-sm">{{ item.title }}</span>
                        </div>
                    </div>
                </Card>
            </div>
        </Panel>

        <div class="flex min-h-40 p-2">
            <button
                type="button"
                class="relative flex w-full items-center justify-center rounded-xl border border-dashed border-gray-500 text-gray-700 hover:border-gray hover:text-gray-925 dark:border-gray-500 dark:text-gray-300 dark:hover:border-gray-400 dark:hover:text-gray-200"
                :disabled="saving"
                @click="addGroup"
            >
                <span class="flex items-center gap-2">
                    <Icon name="plus" class="size-4" />
                    {{ __('Add Group') }}
                </span>
            </button>
        </div>

        <ErrorMessage v-if="errors.groups" class="mt-4" :text="errors.groups[0]" />

        <Stack
            size="narrow"
            :open="editingGroupId !== null"
            :title="editingNewGroup ? __('Add Group') : __('Edit Group')"
            @update:open="(open) => { if (!open) closeGroupEditor() }"
        >
            <div class="space-y-6">
                <Field id="resource-index-group-title" :label="__('Title')" :error="groupTitleError" required>
                    <Input
                        id="resource-index-group-title"
                        v-model="editingGroupTitle"
                        :focus="true"
                        @keyup.enter="updateGroupTitle"
                    />
                </Field>
                <Button class="w-full" variant="primary" :text="__('Save')" @click="updateGroupTitle" />
            </div>
        </Stack>

        <Stack
            size="narrow"
            :open="addingToGroupId !== null"
            :title="addItemText"
            :icon="resourceIndex.icon"
            @update:open="(open) => { if (!open) closeItemPicker() }"
        >
            <div class="space-y-6">
                <Field :label="resourceIndex.title">
                    <Combobox
                        v-model="addingItemIds"
                        :options="itemPickerOptions"
                        option-label="title"
                        option-value="id"
                        :placeholder="resourceIndex.title"
                        multiple
                        searchable
                    />
                </Field>
                <Button
                    class="w-full"
                    variant="primary"
                    :text="__('Add')"
                    :disabled="!addingItemIds.length"
                    @click="addItems"
                />
            </div>
        </Stack>

        <ConfirmationModal
            :open="confirmingReset"
            :title="__('Reset')"
            :body-text="__('Are you sure you want to reset these groups?')"
            :button-text="__('Reset')"
            danger
            @confirm="reset"
            @cancel="confirmingReset = false"
        />
    </div>
</template>
