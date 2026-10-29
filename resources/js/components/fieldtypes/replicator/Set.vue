<script setup>
import { computed, inject, ref, watch, onBeforeUnmount } from 'vue';
import {
    Icon,
    Switch,
    Subheading,
    Badge,
    Dropdown,
    DropdownItem,
    DropdownSeparator,
    Button,
    DropdownMenu,
    DragHandle,
    PublishFields as Fields,
    PublishFieldsProvider as FieldsProvider,
    injectPublishContext as injectContainerContext,
} from '@/components/ui';
import FieldAction from '@/components/field-actions/FieldAction.js';
import toFieldActions from '@/components/field-actions/toFieldActions.js';
import { reveal } from '@api';
import usePreviewText from '@/composables/use-preview-text';
import { createMountScheduler } from '@/util/createMountScheduler.js';
import ShowField from '@/components/field-conditions/ShowField.js';
import analyzeSetConditions from '@/components/field-conditions/analyzeSetConditions.js';
import { useKeyboardItemReorder } from '@/composables/keyboard-item-reorder.js';
import KeyboardReorderIndicator from '@/components/sortable/KeyboardReorderIndicator.vue';

const emit = defineEmits(['collapsed', 'expanded', 'duplicated', 'removed', 'moved']);

const replicatorSets = inject('replicatorSets');
const mountScheduler = inject('mountScheduler', createMountScheduler());

const props = defineProps({
    config: Object,
    id: String,
    fieldPath: String,
    metaPath: String,
    index: Number,
    collapsed: Boolean,
    values: Object,
    sortableItemClass: String,
    sortableHandleClass: String,
    readOnly: Boolean,
    enabled: Boolean,
    hasError: Boolean,
    canAddSet: Boolean,
    canReorder: Boolean,
    totalSets: {
        type: Number,
        default: 0,
    },
    showFieldPreviews: Boolean,
});

const {
    setFieldValue,
    setFieldMeta,
    previews,
    extraValues,
    visibleValues,
    revealerValues,
    hiddenFields,
    setHiddenField,
    container,
} = injectContainerContext();
const fieldPathPrefix = computed(() => `${props.fieldPath}.${props.index}`);
const metaPathPrefix = computed(() => `${props.metaPath}.existing.${props.id}`);
const isInvalid = computed(() => Object.keys(props.config).length === 0);
const hasFields = computed(() => Array.isArray(props.config.fields) ? props.config.fields.length > 0 : Object.keys(props.config.fields || {}).length > 0);

const setGroup = computed(() => {
    if (replicatorSets.length < 1) return null;

    return (
        replicatorSets.find((group) => {
            return group.sets.filter((set) => set.handle === props.config.handle).length > 0;
        }) ?? {}
    );
});

const isSetGroupVisible = computed(() => replicatorSets.length > 1 && setGroup.value.display);

const fieldActionPayload = computed(() => ({
    // vm: this,
    // fieldVm: this.fieldVm,
    // fieldPathPrefix: this.fieldPathPrefix,
    index: props.index,
    values: props.values,
    config: props.config,
    // meta: this.meta,
    update: (handle, value) => setFieldValue(`${fieldPathPrefix.value}.${handle}`, value),
    updateMeta: (handle, value) => setFieldMeta(`${metaPathPrefix.value}.${handle}`, value),
    isReadOnly: props.readOnly,
}));

const fieldActions = computed(() => {
    return toFieldActions('replicator-fieldtype-set', fieldActionPayload.value);
});

const { previewText } = usePreviewText({
    config: computed(() => props.config),
    values: computed(() => props.values),
    previews,
    fieldPathPrefix,
    showFieldPreviews: computed(() => props.showFieldPreviews),
});

function toggleEnabledState() {
    setFieldValue(`${fieldPathPrefix.value}.enabled`, !props.enabled);
}

function toggleCollapsedState() {
    props.collapsed ? emit('expanded') : emit('collapsed');
}

function prewarmFields() {
    if (hasBeenExpanded.value || !hasFields.value) return;
    hasBeenExpanded.value = true;
    mountScheduler.schedule(() => {
        if (!isUnmounted) {
            fieldsReady.value = true;
        }
    });
}

const deletingSet = ref(false);

function destroy() {
    deletingSet.value = false;
    emit('removed');
}

// Defer mounting collapsed set bodies. Once expanded, keep mounted (v-show thereafter).
const { needsRootValues, canDeferMount } = analyzeSetConditions(props.config);
const hasBeenExpanded = ref(!props.collapsed);
const fieldsReady = ref(!props.collapsed);
let isUnmounted = false;

onBeforeUnmount(() => {
    isUnmounted = true;
});

watch(
    () => props.collapsed,
    (collapsed) => {
        if (!collapsed && !hasBeenExpanded.value) {
            hasBeenExpanded.value = true;
            mountScheduler.schedule(() => {
                if (!isUnmounted) {
                    fieldsReady.value = true;
                }
            });
        } else if (!collapsed) {
            fieldsReady.value = true;
        }
    },
);

// Auto-expand when validation errors point at this set.
watch(
    () => props.hasError,
    (hasError) => {
        if (hasError && props.collapsed) {
            emit('expanded');
        }
    },
    { immediate: true },
);

// Some of what mounting used to do can't be done headlessly — nothing evaluates the
// conditions of fields nested inside this set's fields, and revealers only register
// themselves when they mount. Those sets get mounted anyway, but off the critical path.
if (!canDeferMount) prewarmFields();

// Headlessly evaluate conditions for never-mounted sets so omitValue bookkeeping
// stays correct for the save payload. Sets whose conditions only look at their own
// values keep a narrow dependency; the rest have to watch the whole tree.
watch(
    needsRootValues
        ? [() => props.values, fieldsReady, revealerValues, visibleValues]
        : [() => props.values, fieldsReady, revealerValues],
    () => {
        if (fieldsReady.value || !hasFields.value) return;

        const scopedValues = props.values || {};
        const scopedExtra = data_get(extraValues.value, fieldPathPrefix.value) || {};
        const showField = new ShowField(
            scopedValues,
            scopedExtra,
            visibleValues.value,
            revealerValues.value,
            hiddenFields.value,
            setHiddenField,
            { container },
        );

        (props.config.fields || []).forEach((field) => {
            showField.showField(field, `${fieldPathPrefix.value}.${field.handle}`);
        });
    },
    { deep: true, immediate: true },
);

const {
    moving,
    moveOrigin,
    rootEl: reorderFocusEl,
    status: reorderStatus,
    startMoving,
} = useKeyboardItemReorder({
    index: () => props.index,
    total: () => props.totalSets,
    onMove: (from, to) => emit('moved', from, to),
});

function startMovingFromHandle() {
    startMoving('start');
}

function startMovingFromMenu() {
    startMoving('end');
}

const rootEl = ref();
reveal.use(rootEl, () => emit('expanded'));

const dropdownJustClosed = ref(false);
let dropdownJustClosedTimeout = null;

function onSetDropdownClosed() {
    dropdownJustClosed.value = true;
    if (dropdownJustClosedTimeout) clearTimeout(dropdownJustClosedTimeout);
    dropdownJustClosedTimeout = setTimeout(() => {
        dropdownJustClosed.value = false;
        dropdownJustClosedTimeout = null;
    }, 150);
}
</script>

<template>
    <div
        :class="sortableItemClass"
        :data-moving="moving || undefined"
    >
        <slot name="picker" />
        <div
            ref="rootEl"
            tabindex="-1"
            layout
            data-replicator-set
            data-reorder-focus
            class="relative w-full rounded-lg border border-gray-300 text-base dark:border-white/10 bg-white dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black shadow-ui-sm dark:[&_[data-ui-switch]]:border-gray-600 dark:[&_[data-ui-switch]]:border-1"
            :class="{
                'border-red-500': hasError,
                'st-dropdown-just-closed': dropdownJustClosed,
                'border-dashed border-gray-300 dark:border-gray-600': readOnly && !hasError,
                'border-dashed': readOnly && hasError,
                'focus-outline': moving,
            }"
            :data-collapsed="collapsed ?? undefined"
            :data-error="hasError ?? undefined"
            :data-invalid="isInvalid ?? undefined"
            :data-readonly="readOnly ?? undefined"
            :data-type="config.handle"
        >
            <KeyboardReorderIndicator :moving="moving" :side="moveOrigin" />
            <header
                class="group/header animate-border-color flex items-center show-focus-within rounded-[calc(var(--radius-lg)-1px)] px-1.5 antialiased duration-200 bg-gray-100/50 dark:bg-gray-925 data-readonly:dark:!bg-transparent hover:bg-gray-100 dark:hover:bg-gray-950/45 border-gray-300 dark:shadow-md"
                :data-readonly="readOnly ? true : undefined"
                :class="{
                    'bg-gray-200/50 dark:bg-gray-950/35 rounded-b-none': !collapsed && hasFields && fieldsReady
                }"
                @pointerenter="prewarmFields"
            >
                <DragHandle
                    v-if="!readOnly"
                    :keyboard-reorder="canReorder"
                    :class="sortableHandleClass"
                    class="shrink-0"
                    @keyboard-reorder="startMovingFromHandle"
                />
                <button type="button" class="show-focus-within_target flex flex-1 min-w-0 cursor-pointer items-center gap-4 overflow-x-auto p-2 py-1.75 pe-4 focus:outline-none st-mask-horizontal-overflow" @click="toggleCollapsedState">
                    <Badge size="lg" pill color="white" class="px-3">
                        <span v-if="isSetGroupVisible" class="flex items-center gap-2">
                            <span class="st-text-trim-optical-cap">{{ __(setGroup.display) }}</span>
                            <Icon name="chevron-right" class="size-3" />
                        </span>
                        <span class="st-text-trim-optical-cap">{{ __(config.display) || config.handle }}</span>
                    </Badge>
                    <Icon
                        v-if="config.instructions && !collapsed"
                        name="info-square"
                        class="size-3.5! text-gray-500"
                        v-tooltip="__(config.instructions)"
                    />
                    <Subheading
                        v-show="collapsed"
                        v-html="previewText"
                        class="overflow-hidden text-ellipsis whitespace-nowrap"
                    />
                </button>
                <div class="flex items-center gap-2" v-if="!readOnly">
                    <button
                        ref="reorderFocusEl"
                        type="button"
                        class="sr-only"
                        :tabindex="moving ? 0 : -1"
                        :aria-label="__('messages.keyboard_item_reorder_instructions')"
                    />
                    <Switch
                        size="xs"
                        :model-value="enabled"
                        :label="__('Included in output')"
                        @update:model-value="toggleEnabledState"
                        v-tooltip="enabled ? __('Included in output') : __('Hidden from output')"
                    />
                    <Dropdown @closed="onSetDropdownClosed">
                        <template #trigger>
                            <Button icon="dots" variant="ghost" size="xs" :aria-label="__('Open dropdown menu')" />
                        </template>
                        <DropdownMenu>
                            <DropdownItem
                                v-if="fieldActions.length"
                                v-for="action in fieldActions"
                                :text="action.title"
                                :icon="action.icon"
                                :variant="action.dangerous ? 'destructive' : 'default'"
                                @click="action.run(action)"
                            />
                            <DropdownSeparator v-if="fieldActions.length" />
                            <DropdownItem
                                :text="__(collapsed ? __('Expand Set') : __('Collapse Set'))"
                                :icon="collapsed ? 'expand' : 'collapse'"
                                @click="toggleCollapsedState"
                            />
                            <DropdownItem
                                v-if="canReorder"
                                :text="__('Move')"
                                icon="move-vertical"
                                @click="startMovingFromMenu"
                            />
                            <DropdownItem
                                v-if="canAddSet"
                                :text="__('Duplicate Set')"
                                icon="duplicate"
                                @click="emit('duplicated')"
                            />
                            <DropdownItem
                                :text="__('Delete Set')"
                                icon="trash"
                                variant="destructive"
                                @click="deletingSet = true"
                            />
                        </DropdownMenu>
                    </Dropdown>
                    <div class="sr-only" aria-live="polite">{{ reorderStatus }}</div>
                </div>
            </header>

            <div
                v-if="fieldsReady && hasFields"
                v-show="!collapsed"
                data-set-body
                :class="{ 'contain-paint': collapsed, 'isolate': !collapsed }"
                class="border-t border-t-gray-300! dark:border-t-white/10!"
            >
                <div :tabindex="collapsed ? -1 : undefined" :inert="collapsed">
                    <FieldsProvider
                        :fields="config.fields"
                        :as-config="false"
                        :read-only="readOnly"
                        :field-path-prefix="fieldPathPrefix"
                        :meta-path-prefix="metaPathPrefix"
                    >
                        <Fields class="p-4" />
                    </FieldsProvider>
                </div>
            </div>
        </div>

        <confirmation-modal
            :open="deletingSet"
            :title="__('Delete Set')"
            :body-text="__('Are you sure?')"
            :button-text="__('Delete')"
            :danger="true"
            @confirm="destroy"
            @cancel="deletingSet = false"
        />
    </div>
</template>
