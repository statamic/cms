<script setup>
import { injectTabContext } from './TabProvider.vue';
import {
    Button,
    Panel,
    PanelHeader,
    Heading,
    Subheading,
    Card,
    Editable,
    Field,
    Icon,
    Input,
    Stack,
} from '@ui';
import { deepClone } from '@/util/clone.js';
import FieldsProvider from './FieldsProvider.vue';
import Fields from './Fields.vue';
import ShowField from '@/components/field-conditions/ShowField.js';
import { injectContainerContext } from './Container.vue';
import { SortableList } from '@/components/sortable/Sortable.js';
import { Sortable, Plugins, Draggable } from '@shopify/draggable';
import markdown from '@/util/markdown.js';
import { computed, nextTick, onBeforeUnmount, provide, ref, watch } from 'vue';

const { container, visibleValues, extraValues, revealerValues, asConfig, hiddenFields, setHiddenField, values, setFieldValue, setValues, readOnly, editingSections, canAddSections, meta, setFieldMeta } = injectContainerContext();
const tab = injectTabContext();
const visibleSections = computed(() => {
    return tab.sections.filter((section) => {
        return section.fields.some((field) => {
            return new ShowField(
                visibleValues.value,
                extraValues.value,
                visibleValues.value,
                revealerValues.value,
                hiddenFields.value,
                setHiddenField,
                { container }
            ).showField(field, field.handle);
        });
    });
});
const reorderableSections = computed(() => visibleSections.value.filter((section) => section.reorderable));
const usesGroupEditor = computed(() => !!canAddSections?.value);
const listedSections = computed(() => {
    if (!usesGroupEditor.value) return visibleSections.value;

    return visibleSections.value.filter((section) => !isOtherSection(section));
});
const pinnedSections = computed(() => {
    if (!usesGroupEditor.value) return [];

    return visibleSections.value.filter(isOtherSection);
});
const canReorderSections = computed(() => !readOnly.value && reorderableSections.value.length > 1);
const isEditingSections = computed(() => !!editingSections?.value);
const showSectionDragHandles = computed(() => usesGroupEditor.value && isEditingSections.value && !readOnly.value);
const showAddSection = computed(() => !!canAddSections?.value && isEditingSections.value && !readOnly.value);
const showGroupActions = computed(() => showAddSection.value);
const hasOnlyOtherGroup = computed(() => usesGroupEditor.value && listedSections.value.length === 0);

watch(isEditingSections, (editing) => {
    if (!editing) {
        closeGroupEditor({ saved: true });
        return;
    }

    if (hasOnlyOtherGroup.value && !readOnly.value) {
        nextTick(() => openNewGroupEditor());
    }
});

const titleSnapshots = ref({});
const groupEditorOpen = ref(false);
const editingNewGroup = ref(false);
const editingGroupHandle = ref(null);
const editingGroupTitle = ref('');
const groupTitleError = ref(null);
const editingSection = ref(null);

function renderInstructions(instructions) {
    return instructions ? markdown(__(instructions), { openLinksInNewTabs: true }) : '';
}

function snapshotTitle(handle) {
    titleSnapshots.value[handle] = values.value[handle] ?? null;
}

function updateTitle(handle, value) {
    setFieldValue(handle, value?.trim() || null);
}

function cancelTitleEdit(handle) {
    setFieldValue(handle, titleSnapshots.value[handle]);
}

function toggleSection(section) {
    if (section.collapsible) {
        section.collapsibleInteracted = true;
        section.collapsed = !section.collapsed;
    }
}

function sectionKey(section) {
    return section.editable_title_handle || section.fields[0]?.handle;
}

function reorderSections(reordered) {
    const reorderedKeys = new Set(reordered.map(sectionKey));
    const remaining = tab.sections.filter((section) => !reorderedKeys.has(sectionKey(section)));

    tab.sections.splice(0, tab.sections.length, ...reordered, ...remaining);
    persistGroupOrder(reordered);
}

function persistGroupOrder(reordered) {
    const next = {};

    for (const section of reordered) {
        const nameHandle = section.editable_title_handle;
        const sitesHandle = sitesHandleFor(section);

        if (nameHandle in values.value) {
            next[nameHandle] = values.value[nameHandle];
        }

        if (sitesHandle in values.value) {
            next[sitesHandle] = values.value[sitesHandle];
        }
    }

    for (const [key, value] of Object.entries(values.value)) {
        if (!(key in next)) {
            next[key] = value;
        }
    }

    setValues(valuesWithOtherLast(next));
}

function valuesWithOtherLast(next) {
    const otherSites = 'group_other_sites';
    const otherName = 'group_other_name';
    const ordered = {};

    for (const [key, value] of Object.entries(next)) {
        if (key === otherName || key === otherSites) continue;
        ordered[key] = value;
    }

    if (otherName in next) ordered[otherName] = next[otherName];
    if (otherSites in next) ordered[otherSites] = next[otherSites];

    return ordered;
}

function sitesHandleFor(section) {
    return section.fields.find((field) => field.handle.endsWith('_sites'))?.handle
        ?? section.fields[0]?.handle;
}

function isOtherSection(section) {
    return sitesHandleFor(section) === 'group_other_sites';
}

function insertNamedSection(section) {
    const index = tab.sections.findIndex(isOtherSection);

    if (index === -1) {
        tab.sections.push(section);
        return;
    }

    tab.sections.splice(index, 0, section);
}

function uniqueGroupKey(title) {
    let base = Statamic.$slug.create(title) || 'group';

    if (base === 'other') {
        base = 'group';
    }

    let key = base;
    let suffix = 2;

    while (`group_${key}_sites` in values.value) {
        key = `${base}-${suffix++}`;
    }

    return key;
}

function sectionTitle(section) {
    if (!section.editable_title_handle) return __(section.display);

    return values.value[section.editable_title_handle] || __(section.display);
}

function sectionGridField(section) {
    return section.fields.find((field) => field.type === 'grid' && field.headers_in_section);
}

function showsSectionTitleOutside(section) {
    return !!sectionGridField(section);
}

function showsSectionTitle(section) {
    if (hasOnlyOtherGroup.value && isOtherSection(section)) {
        return false;
    }

    return true;
}

const groupTitleInput = ref(null);

function openNewGroupEditor() {
    const section = addGroup(__('New Group'));

    if (!section) return;

    nextTick(() => {
        focusSection(section);
        editGroup(section, { isNew: true });
    });
}

function editGroup(section, { isNew = false } = {}) {
    groupEditorOpen.value = true;
    editingNewGroup.value = isNew;
    editingSection.value = section;
    editingGroupHandle.value = section.editable_title_handle;
    editingGroupTitle.value = values.value[section.editable_title_handle] || '';
    groupTitleError.value = null;
}

function closeGroupEditor({ saved = false } = {}) {
    const sectionToRemove = !saved && editingNewGroup.value ? editingSection.value : null;

    groupEditorOpen.value = false;
    editingNewGroup.value = false;
    editingSection.value = null;
    editingGroupHandle.value = null;
    editingGroupTitle.value = '';
    groupTitleError.value = null;

    if (sectionToRemove) {
        removeGroup(sectionToRemove);
    }
}

function saveGroupTitle() {
    const title = editingGroupTitle.value.trim();

    if (!title) {
        groupTitleError.value = __('statamic::validation.required');
        return;
    }

    if (editingGroupHandle.value) {
        setFieldValue(editingGroupHandle.value, title);
    }

    closeGroupEditor({ saved: true });
}

function removeGroup(section) {
    const fromHandle = sitesHandleFor(section);
    const moving = values.value[fromHandle] || [];
    const next = { ...values.value };

    next.group_other_sites = [...(next.group_other_sites || []), ...moving];
    delete next[section.editable_title_handle];
    delete next[fromHandle];

    const index = tab.sections.findIndex((item) => sectionKey(item) === sectionKey(section));
    if (index !== -1) {
        tab.sections.splice(index, 1);
    }

    setValues(valuesWithOtherLast(next));

    moving.forEach((row) => moveRowMeta(fromHandle, 'group_other_sites', row._id));
    unregisterSectionRowZone(fromHandle);
}

function addGroup(title) {
    const template = reorderableSections.value[0] ?? visibleSections.value[0];

    if (!template) return;

    const key = uniqueGroupKey(title);
    const templateSitesHandle = sitesHandleFor(template);
    const nameHandle = `group_${key}_name`;
    const sitesHandle = `group_${key}_sites`;
    const sitesField = deepClone(
        template.fields.find((field) => field.handle === templateSitesHandle)
            ?? template.fields.find((field) => field.handle?.endsWith('_sites'))
            ?? template.fields[0],
    );

    sitesField.handle = sitesHandle;

    const handleField = (sitesField.fields || []).find((field) => field.handle === 'handle');
    if (handleField) {
        handleField.prefix_from = nameHandle;
    }

    const section = {
        display: title,
        editable_title_handle: nameHandle,
        reorderable: true,
        fields: [
            {
                handle: nameHandle,
                type: 'text',
                visibility: 'hidden',
                always_save: true,
            },
            sitesField,
        ],
    };

    insertNamedSection(section);

    setValues(valuesWithOtherLast({
        ...values.value,
        [nameHandle]: title,
        [sitesHandle]: [],
    }));

    const templateMeta = meta.value[templateSitesHandle];

    if (templateMeta) {
        setFieldMeta(sitesHandle, {
            defaults: deepClone(templateMeta.defaults),
            new: deepClone(templateMeta.new),
            existing: {},
        });
    }

    return section;
}

function focusSection(section) {
    const el = document.querySelector(`[data-section-key="${CSS.escape(sectionKey(section))}"]`);

    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el?.focus({ preventScroll: true });
}

function focusGroupTitleInput() {
    nextTick(() => groupTitleInput.value?.select());
}

const SECTION_ROW_ITEM_CLASS = 'publish-section-row-item';
const SECTION_ROW_HANDLE_CLASS = 'publish-section-row-handle';
const rowZones = new Map();
let rowSortable = null;
let rowSortableRebuildQueued = false;

function registerSectionRowZone(el, handle) {
    for (const [existing, existingHandle] of rowZones) {
        if (existingHandle === handle) {
            rowZones.delete(existing);
        }
    }

    rowZones.set(el, handle);
    scheduleRowSortableRebuild();
}

function unregisterSectionRowZone(el) {
    if (typeof el === 'string') {
        for (const [existing, handle] of rowZones) {
            if (handle === el) {
                rowZones.delete(existing);
            }
        }
    } else {
        rowZones.delete(el);
    }

    scheduleRowSortableRebuild();
}

function scheduleRowSortableRebuild() {
    if (rowSortableRebuildQueued) return;

    rowSortableRebuildQueued = true;

    nextTick(() => {
        rowSortableRebuildQueued = false;
        rebuildRowSortable();
    });
}

function rebuildRowSortable() {
    rowSortable?.destroy();
    rowSortable = null;

    const containers = [...rowZones.keys()].filter((el) => el?.isConnected);

    if (!containers.length || readOnly.value) return;

    rowSortable = new Sortable(containers, {
        draggable: `.${SECTION_ROW_ITEM_CLASS}`,
        handle: `.${SECTION_ROW_HANDLE_CLASS}`,
        delay: 0,
        distance: 4,
        swapAnimation: { vertical: true, horizontal: false },
        plugins: [Plugins.SwapAnimation],
        mirror: {
            constrainDimensions: true,
            appendTo: 'body',
            xAxis: false,
        },
        exclude: {
            plugins: [Draggable.Plugins.Focusable],
        },
    });

    rowSortable.on('sortable:stop', moveRowBetweenZones);
}

function moveRowBetweenZones(event) {
    const fromHandle = rowZones.get(event.oldContainer);
    const toHandle = rowZones.get(event.newContainer);

    if (!fromHandle || !toHandle || event.oldIndex < 0) return;

    const fromRows = [...(values.value[fromHandle] || [])];
    const toRows = fromHandle === toHandle ? fromRows : [...(values.value[toHandle] || [])];
    const [row] = fromRows.splice(event.oldIndex, 1);

    if (!row) return;

    const newIndex = Math.min(Math.max(event.newIndex ?? 0, 0), toRows.length);
    toRows.splice(newIndex, 0, row);

    if (fromHandle === toHandle) {
        setFieldValue(fromHandle, fromRows);
        return;
    }

    setValues(valuesWithOtherLast({
        ...values.value,
        [fromHandle]: fromRows,
        [toHandle]: toRows,
    }));

    moveRowMeta(fromHandle, toHandle, row._id);
    scheduleRowSortableRebuild();
}

function ensureGridMeta(handle, templateHandle) {
    if (meta.value[handle] || !templateHandle) return;

    const templateMeta = meta.value[templateHandle];

    if (!templateMeta) return;

    setFieldMeta(handle, {
        defaults: deepClone(templateMeta.defaults),
        new: deepClone(templateMeta.new),
        existing: {},
    });
}

function moveRowMeta(fromHandle, toHandle, rowId) {
    if (!rowId || fromHandle === toHandle) return;

    ensureGridMeta(toHandle, fromHandle);

    const fromMeta = meta.value[fromHandle];
    const toMeta = meta.value[toHandle];

    if (!fromMeta?.existing || !toMeta) return;

    const { [rowId]: rowMeta, ...restExisting } = fromMeta.existing;

    setFieldMeta(fromHandle, { ...fromMeta, existing: restExisting });
    setFieldMeta(toHandle, {
        ...toMeta,
        existing: { ...toMeta.existing, [rowId]: rowMeta },
    });
}

const SECTION_ADD_ROW_ATTR = 'data-grid-add-row';

provide('sectionAddRowTarget', SECTION_ADD_ROW_ATTR);

function sectionAddRowBindings(section) {
    return { [SECTION_ADD_ROW_ATTR]: sitesHandleFor(section) };
}

if (canAddSections?.value) {
    provide('sectionRowSortable', {
        itemClass: SECTION_ROW_ITEM_CLASS,
        handleClass: SECTION_ROW_HANDLE_CLASS,
        register: registerSectionRowZone,
        unregister: unregisterSectionRowZone,
    });
}

onBeforeUnmount(() => {
    rowSortable?.destroy();
    rowSortable = null;
    rowZones.clear();
});
</script>

<template>
    <div>
        <SortableList
            :model-value="listedSections"
            :disabled="!canReorderSections || !isEditingSections"
            item-class="publish-section-sortable"
            handle-class="publish-section-drag-handle"
            :vertical="true"
            append-to="body"
            constrain-dimensions
            v-slot="{}"
            @update:model-value="reorderSections"
        >
            <div>
                <div
                    v-for="(section, i) in listedSections"
                    :key="sectionKey(section) || i"
                    :data-section-key="sectionKey(section)"
                    tabindex="-1"
                    class="outline-none"
                    :class="{
                        'publish-section-sortable': section.reorderable,
                        'mb-8': showsSectionTitleOutside(section),
                    }"
                >
                    <div
                        v-if="showsSectionTitleOutside(section) && showsSectionTitle(section)"
                        class="mb-2 flex items-center justify-between gap-3 px-1"
                    >
                        <div class="flex min-w-0 flex-1 items-center gap-2">
                            <Icon
                                v-if="section.reorderable && showSectionDragHandles"
                                name="handles-sm"
                                class="publish-section-drag-handle size-3! shrink-0 cursor-grab text-gray-400"
                                :aria-label="__('Drag to reorder')"
                                @mousedown.prevent
                            />
                            <Heading :text="sectionTitle(section)" />
                            <Subheading v-if="section.instructions" :text="renderInstructions(section.instructions)" />
                        </div>
                        <div class="flex shrink-0 items-center">
                            <div v-if="showGroupActions && section.reorderable" class="flex shrink-0 items-center">
                                <Button
                                    icon="pencil-line"
                                    size="sm"
                                    variant="ghost"
                                    :aria-label="__('Edit Group')"
                                    @click="editGroup(section)"
                                />
                                <Button
                                    icon="trash"
                                    size="sm"
                                    variant="ghost"
                                    :aria-label="__('Delete Group')"
                                    @click.prevent="removeGroup(section)"
                                />
                            </div>
                            <div v-if="section.collapsible" class="flex shrink-0 items-center">
                                <Button
                                    @click="toggleSection(section)"
                                    class="static! [&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                                    :icon="section.collapsed ? 'expand' : 'collapse'"
                                    size="sm"
                                    variant="ghost"
                                    :aria-label="__('Toggle section visibility')"
                                />
                            </div>
                        </div>
                    </div>

                    <Panel
                        :class="{
                            'mb-6': !showsSectionTitleOutside(section),
                            'mb-0!': showsSectionTitleOutside(section),
                            'pb-0': section.collapsed,
                        }"
                    >
                        <PanelHeader
                            v-if="!showsSectionTitleOutside(section) && showsSectionTitle(section) && (section.display || section.collapsible || section.editable_title_handle)"
                            class="pl-2.75! pr-3.25!"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 flex-1 items-center gap-2">
                                    <Icon
                                        v-if="section.reorderable && showSectionDragHandles"
                                        name="handles-sm"
                                        class="publish-section-drag-handle size-3! shrink-0 cursor-grab text-gray-400"
                                        :aria-label="__('Drag to reorder')"
                                        @mousedown.prevent
                                    />
                                    <Heading v-if="!section.editable_title_handle || usesGroupEditor" :text="sectionTitle(section)" />
                                    <Heading v-else class="min-w-0">
                                        <Editable
                                            :model-value="values[section.editable_title_handle] || ''"
                                            :activation-mode="'none'"
                                            submit-mode="both"
                                            :placeholder="__(section.display)"
                                            class="publish-section-editable-title min-w-0 field-sizing-content outline-offset-5 rounded-sm"
                                            @edit="snapshotTitle(section.editable_title_handle)"
                                            @update:model-value="(value) => updateTitle(section.editable_title_handle, value)"
                                            @cancel="cancelTitleEdit(section.editable_title_handle)"
                                        />
                                    </Heading>
                                    <Subheading v-if="section.instructions" :text="renderInstructions(section.instructions)" />
                                </div>
                                <div class="flex shrink-0 items-center">
                                    <div v-if="showGroupActions && section.reorderable" class="flex shrink-0 items-center">
                                        <Button
                                            icon="pencil-line"
                                            size="sm"
                                            variant="ghost"
                                            :aria-label="__('Edit Group')"
                                            @click="editGroup(section)"
                                        />
                                        <Button
                                            icon="trash"
                                            size="sm"
                                            variant="ghost"
                                            :aria-label="__('Delete Group')"
                                            @click.prevent="removeGroup(section)"
                                        />
                                    </div>
                                    <div v-if="section.collapsible" class="flex shrink-0 items-center">
                                        <Button
                                            @click="toggleSection(section)"
                                            class="static! [&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                                            :icon="section.collapsed ? 'expand' : 'collapse'"
                                            size="sm"
                                            variant="ghost"
                                            :aria-label="__('Toggle section visibility')"
                                        />
                                    </div>
                                </div>
                            </div>
                        </PanelHeader>
                        <div
                            class="publish-section-collapsible grid"
                            :class="[
                                section.collapsed ? 'publish-section-collapsible--collapsed' : 'publish-section-collapsible--expanded',
                                { 'publish-section-collapsible--interacted': section.collapsibleInteracted },
                            ]"
                        >
                            <div class="publish-section-collapsible__inner min-h-0">
                                <Card v-if="!usesGroupEditor" :class="{ 'p-0!': asConfig }">
                                    <FieldsProvider :fields="section.fields">
                                        <slot :section="section">
                                            <Fields />
                                        </slot>
                                    </FieldsProvider>
                                </Card>
                                <FieldsProvider v-else :fields="section.fields">
                                    <slot :section="section">
                                        <Fields />
                                    </slot>
                                </FieldsProvider>
                            </div>
                        </div>
                    </Panel>
                    <div
                        v-if="showsSectionTitleOutside(section)"
                        v-show="!section.collapsed"
                        v-bind="sectionAddRowBindings(section)"
                        class="mt-2 px-1 empty:hidden"
                    />
                </div>
            </div>
        </SortableList>

        <div
            v-for="(section, i) in pinnedSections"
            :key="sectionKey(section) || `pinned-${i}`"
            :class="{ 'mb-8': showsSectionTitleOutside(section) }"
        >
            <div
                v-if="showsSectionTitleOutside(section) && showsSectionTitle(section)"
                class="mb-2 flex items-center justify-between gap-3 px-1"
            >
                <div class="flex min-w-0 flex-1 items-center gap-2">
                    <Heading :text="sectionTitle(section)" />
                    <Subheading v-if="section.instructions" :text="renderInstructions(section.instructions)" />
                </div>
                <div v-if="section.collapsible" class="flex shrink-0 items-center">
                    <Button
                        @click="toggleSection(section)"
                        class="static! [&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                        :icon="section.collapsed ? 'expand' : 'collapse'"
                        size="sm"
                        variant="ghost"
                        :aria-label="__('Toggle section visibility')"
                    />
                </div>
            </div>

            <Panel
                :class="{
                    'mb-6': !showsSectionTitleOutside(section),
                    'mb-0!': showsSectionTitleOutside(section),
                    'pb-0': section.collapsed,
                }"
            >
                <PanelHeader
                    v-if="!showsSectionTitleOutside(section) && showsSectionTitle(section) && (section.display || section.collapsible)"
                    class="pl-2.75! pr-3.25!"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex min-w-0 flex-1 items-center gap-2">
                            <Heading :text="sectionTitle(section)" />
                            <Subheading v-if="section.instructions" :text="renderInstructions(section.instructions)" />
                        </div>
                        <div v-if="section.collapsible" class="flex shrink-0 items-center">
                            <Button
                                @click="toggleSection(section)"
                                class="static! [&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                                :icon="section.collapsed ? 'expand' : 'collapse'"
                                size="sm"
                                variant="ghost"
                                :aria-label="__('Toggle section visibility')"
                            />
                        </div>
                    </div>
                </PanelHeader>
                <div
                    class="publish-section-collapsible grid"
                    :class="[
                        section.collapsed ? 'publish-section-collapsible--collapsed' : 'publish-section-collapsible--expanded',
                        { 'publish-section-collapsible--interacted': section.collapsibleInteracted },
                    ]"
                >
                    <div class="publish-section-collapsible__inner min-h-0">
                        <Card v-if="!usesGroupEditor" :class="{ 'p-0!': asConfig }">
                            <FieldsProvider :fields="section.fields">
                                <slot :section="section">
                                    <Fields />
                                </slot>
                            </FieldsProvider>
                        </Card>
                        <FieldsProvider v-else :fields="section.fields">
                            <slot :section="section">
                                <Fields />
                            </slot>
                        </FieldsProvider>
                    </div>
                </div>
            </Panel>
            <div
                v-if="showsSectionTitleOutside(section)"
                v-show="!section.collapsed"
                v-bind="sectionAddRowBindings(section)"
                class="mt-2 px-1 empty:hidden"
            />
        </div>

        <div v-if="showAddSection" class="blueprint-add-section-container flex min-h-40 p-2">
            <button
                type="button"
                class="relative flex w-full items-center justify-center rounded-xl border border-dashed border-gray-500 text-gray-700 hover:border-gray hover:text-gray-925 dark:border-gray-500 dark:text-gray-300 dark:hover:border-gray-400 dark:hover:text-gray-200"
                @click="openNewGroupEditor"
            >
                <span class="flex items-center gap-2">
                    <Icon name="plus" class="size-4" />
                    {{ __('Add Group') }}
                </span>
            </button>
        </div>

        <Stack
            size="narrow"
            :open="groupEditorOpen"
            :title="editingNewGroup ? __('Add Group') : __('Edit Group')"
            @update:open="(open) => { if (!open) closeGroupEditor() }"
            @opened="focusGroupTitleInput"
        >
            <div class="space-y-6">
                <Field id="publish-section-group-title" :label="__('Title')" :error="groupTitleError" required>
                    <Input
                        ref="groupTitleInput"
                        id="publish-section-group-title"
                        v-model="editingGroupTitle"
                        :focus="true"
                        @keyup.enter="saveGroupTitle"
                    />
                </Field>
                <Button class="w-full" variant="primary" :text="__('Save')" @click="saveGroupTitle" />
            </div>
        </Stack>
    </div>
</template>

<style scoped>
.publish-section-editable-title :deep(span),
.publish-section-editable-title :deep(input) {
    display: block;
    width: 100%;
    min-width: 0;
    border: 0;
    background: transparent;
    padding: 0;
    margin: 0;
    font: inherit;
    color: inherit;
    letter-spacing: inherit;
    outline: none;
    box-shadow: none;
}

.publish-section-editable-title :deep(span[data-placeholder]) {
    color: var(--color-gray-500);
}

.dark .publish-section-editable-title :deep(span[data-placeholder]) {
    color: var(--color-gray-400);
}

.publish-section-collapsible {
    --timing: ease;
    /* No animation on load; enable once the user has toggled this section. */
    --speed: 0ms;

    /* Only setting the animation speed when the section is interacted with. Prevents the animation triggering on page load. */
    &.publish-section-collapsible--interacted {
        --speed: 250ms;
    }

    @media (prefers-reduced-motion: reduce) {
        --speed: 0ms;
    }
}

.publish-section-collapsible--expanded {
    /* We can animate collapse/expand using grid rows */
    animation: expand-rows var(--speed) var(--timing) forwards;

    .publish-section-collapsible__inner {
        animation: calc(var(--speed) * 2) var(--timing) section-fade-in both;
        overflow: clip;
        /* We need to increase the clip margin here vs regular collapsible sections because we have things appearing outside the section such as the logic indicator icon. */
        overflow-clip-margin: 2.5rem;
    }
}

.publish-section-collapsible--collapsed {
    animation: collapse-rows var(--speed) var(--timing) forwards;

    .publish-section-collapsible__inner {
        animation:
            clip-overflow 0ms var(--speed) forwards,
            make-invisible 0ms var(--speed) forwards;
        overflow: clip;
    }
}

@keyframes section-fade-in { from { opacity: 0%; } to { opacity: 100%; } }
@keyframes make-invisible { from { visibility: visible; } to { visibility: hidden; } }
@keyframes collapse-rows  { from { grid-template-rows: 1fr; } to { grid-template-rows: 0fr; } }
@keyframes expand-rows    { from { grid-template-rows: 0fr; } to { grid-template-rows: 1fr; } }
@keyframes clip-overflow  { to { overflow: clip; } }
</style>
