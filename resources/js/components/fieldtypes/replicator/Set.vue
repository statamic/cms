<script setup>
import { computed, inject, ref } from 'vue';
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
    PublishFields as Fields,
    PublishFieldsProvider as FieldsProvider,
    injectPublishContext as injectContainerContext,
} from '@/components/ui';
import { Motion } from 'motion-v';
import PreviewHtml from '@/components/fieldtypes/replicator/PreviewHtml.js';
import FieldAction from '@/components/field-actions/FieldAction.js';
import toFieldActions from '@/components/field-actions/toFieldActions.js';
import { reveal } from '@api';

const emit = defineEmits(['collapsed', 'expanded', 'duplicated', 'removed']);

const replicatorSets = inject('replicatorSets');

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
    showFieldPreviews: Boolean,
});

const {
    setFieldValue,
    setFieldMeta,
    previews
} = injectContainerContext();
const fieldPathPrefix = computed(() => `${props.fieldPath}.${props.index}`);
const metaPathPrefix = computed(() => `${props.metaPath}.existing.${props.id}`);
const isInvalid = computed(() => Object.keys(props.config).length === 0);

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

const previewText = computed(() => {
    return Object.entries(data_get(previews.value, fieldPathPrefix.value) || {})
        .filter(([handle, value]) => {
            if (!handle.endsWith('_')) return false;
            handle = handle.substr(0, handle.length - 1); // Remove the trailing underscore.
            const config = props.config.fields.find((f) => f.handle === handle) || {};
            return config.replicator_preview === undefined ? props.showFieldPreviews : config.replicator_preview;
        })
        .map(([handle, value]) => value)
        .filter((value) => !['null', '[]', '{}', '', undefined].includes(JSON.stringify(value)))
        .map((value) => {
            if (value instanceof PreviewHtml) return value.html;

            if (typeof value === 'string') return escapeHtml(value);

            if (Array.isArray(value) && typeof value[0] === 'string') {
                return escapeHtml(value.join(', '));
            }

            return escapeHtml(JSON.stringify(value));
        })
        .filter((html) => html && html.trim() !== '')
        .join(' <span class="text-gray-400 dark:text-gray-600">/</span> ');
});

function toggleEnabledState() {
    setFieldValue(`${fieldPathPrefix.value}.enabled`, !props.enabled);
}

function toggleCollapsedState() {
    props.collapsed ? emit('expanded') : emit('collapsed');
}

const deletingSet = ref(false);

function destroy() {
    deletingSet.value = false;
    emit('removed');
}

const rootEl = ref();
reveal.use(rootEl, () => emit('expanded'));

const shouldClipOverflow = ref(false);

function onAnimationStart() {
    shouldClipOverflow.value = true;
}

function onAnimationComplete() {
    if (!props.collapsed) {
        shouldClipOverflow.value = false;
    }
}
</script>

<template>
    <div ref="rootEl" :class="sortableItemClass">
        <slot name="picker" />
        <div
            layout
            data-replicator-set
            class="relative z-2 w-full rounded-lg border border-gray-300 text-base dark:border-white/10 bg-white dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black shadow-ui-sm dark:[&_[data-ui-switch]]:border-gray-600 dark:[&_[data-ui-switch]]:border-1"
            :class="{
                'border-red-500': hasError
            }"
            :data-collapsed="collapsed ?? undefined"
            :data-error="hasError ?? undefined"
            :data-invalid="isInvalid ?? undefined"
            :data-readonly="readOnly ?? undefined"
            :data-type="config.handle"
        >
            <header
                class="group/header animate-border-color flex items-center rounded-[calc(var(--radius-lg)-1px)] px-1.5 antialiased duration-200 bg-gray-100/50 dark:bg-gray-925 hover:bg-gray-100 dark:hover:bg-gray-950 border-gray-300 dark:shadow-md border-b-1 border-b-transparent"
                :class="{
                    'bg-gray-200/50 dark:bg-gray-950 rounded-b-none border-b-gray-300! dark:border-b-white/10!': !collapsed
                }"
            >
                <Icon
                    name="handles"
                    :class="sortableHandleClass"
                    class="size-4 cursor-grab text-gray-400"
                    v-if="!readOnly"
                />
                <button type="button" class="flex flex-1 items-center gap-4 p-2 py-1.75 min-w-0 cursor-pointer" @click="toggleCollapsedState">
                    <Badge size="lg" pill color="white" class="px-3">
                        <span v-if="isSetGroupVisible" class="flex items-center gap-2">
                            {{ __(setGroup.display) }}
                            <Icon name="chevron-right" class="relative top-px size-3" />
                        </span>
                        {{ __(config.display) || config.handle }}
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
                    <Switch size="xs" :model-value="enabled" @update:model-value="toggleEnabledState" v-tooltip="enabled ? __('Included in output') : __('Hidden from output')" />
                    <Dropdown>
                        <template #trigger>
                            <Button icon="dots" variant="ghost" size="xs" :aria-label="__('Open dropdown menu')" />
                        </template>
                        <DropdownMenu>
                            <DropdownItem
                                v-if="fieldActions.length"
                                v-for="action in fieldActions"
                                :text="action.title"
                                :variant="action.dangerous ? 'destructive' : 'default'"
                                @click="action.run(action)"
                            />
                            <DropdownSeparator v-if="fieldActions.length" />
                            <DropdownItem
                                :text="__(collapsed ? __('Expand Set') : __('Collapse Set'))"
                                @click="toggleCollapsedState"
                            />
                            <DropdownItem :text="__('Duplicate Set')" @click="emit('duplicated')" />
                            <DropdownItem
                                :text="__('Delete Set')"
                                variant="destructive"
                                @click="deletingSet = true"
                            />
                        </DropdownMenu>
                    </Dropdown>
                </div>
            </header>

            <Motion
                :class="{ 'overflow-clip': shouldClipOverflow }"
                :initial="{ height: collapsed ? '0px' : 'auto' }"
                :animate="{ height: collapsed ? '0px' : 'auto' }"
                :transition="{ duration: 0.25, type: 'tween' }"
                @animation-start="onAnimationStart"
                @animation-complete="onAnimationComplete"
            >
                <FieldsProvider
                    :fields="config.fields"
                    :as-config="false"
                    :field-path-prefix="fieldPathPrefix"
                    :meta-path-prefix="metaPathPrefix"
                >
                    <Fields class="p-4" />
                </FieldsProvider>
            </Motion>
        </div>

        <confirmation-modal
            v-if="deletingSet"
            :title="__('Delete Set')"
            :body-text="__('Are you sure?')"
            :button-text="__('Delete')"
            :danger="true"
            @confirm="destroy"
            @cancel="deletingSet = false"
        />
    </div>
</template>
