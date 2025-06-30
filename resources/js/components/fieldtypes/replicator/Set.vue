<script setup>
import Fields from '@statamic/components/ui/Publish/Fields.vue';
import FieldsProvider from '@statamic/components/ui/Publish/FieldsProvider.vue';
import { computed, inject } from 'vue';
import {
    Icon,
    Switch,
    Subheading,
    Badge,
    Tooltip,
    Dropdown,
    DropdownItem,
    DropdownSeparator,
    Button,
    DropdownMenu,
} from '@statamic/ui';
import { Motion } from 'motion-v';
import { injectContainerContext } from '@statamic/components/ui/Publish/Container.vue';
import PreviewHtml from '@statamic/components/fieldtypes/replicator/PreviewHtml.js';
import FieldAction from '@statamic/components/field-actions/FieldAction.js';

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

const { store } = injectContainerContext();
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
    update: (handle, value) => store.setDottedFieldValue({ path: `${fieldPathPrefix.value}.${handle}`, value }),
    updateMeta: (handle, value) => store.setDottedFieldMeta({ path: `${metaPathPrefix.value}.${handle}`, value }),
    isReadOnly: props.readOnly,
    // store: this.store,
    // storeName: this.storeName,
}));

const fieldActions = computed(() => {
    return [...Statamic.$fieldActions.get('replicator-fieldtype-set')]
        .map((action) => new FieldAction(action, fieldActionPayload.value))
        .filter((action) => action.visible);
});

const previewText = computed(() => {
    return Object.entries(data_get(store.previews, fieldPathPrefix.value) || {})
        .filter(([handle, value]) => {
            if (!handle.endsWith('_')) return false;
            handle = handle.substr(0, handle.length - 1); // Remove the trailing underscore.
            const config = props.config.fields.find((f) => f.handle === handle) || {};
            return config.replicator_preview === undefined ? props.showFieldPreviews : config.replicator_preview;
        })
        .map(([handle, value]) => value)
        .filter((value) => (['null', '[]', '{}', ''].includes(JSON.stringify(value)) ? null : value))
        .map((value) => {
            if (value instanceof PreviewHtml) return value.html;

            if (typeof value === 'string') return escapeHtml(value);

            if (Array.isArray(value) && typeof value[0] === 'string') {
                return escapeHtml(value.join(', '));
            }

            return escapeHtml(JSON.stringify(value));
        })
        .join(' / ');
});

function toggleEnabledState() {
    store.setDottedFieldValue({ path: `${fieldPathPrefix.value}.enabled`, value: !props.enabled });
}

function toggleCollapsedState() {
    props.collapsed ? emit('expanded') : emit('collapsed');
}

function destroy() {
    if (confirm(__('Are you sure?'))) emit('removed');
}
</script>

<template>
    <div :class="sortableItemClass">
        <slot name="picker" />
        <div
            layout
            class="shadow-ui-sm relative z-2 w-full rounded-lg border border-gray-200 bg-white text-base dark:border-x-0 dark:border-t-0 dark:border-white/15 dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black"
            :data-collapsed="collapsed ?? undefined"
            :data-error="hasError ?? undefined"
            :data-invalid="isInvalid ?? undefined"
            :data-readonly="readOnly ?? undefined"
            :data-type="config.handle"
        >
            <header
                class="group/header animate-border-color flex items-center rounded-lg border-b border-transparent px-1.5 antialiased duration-200 hover:bg-gray-50"
                :class="{ 'rounded-b-none border-gray-200! dark:border-white/15': !collapsed }"
            >
                <Icon
                    name="handles"
                    :class="sortableHandleClass"
                    class="size-4 cursor-grab text-gray-400"
                    v-if="!readOnly"
                />
                <button type="button" class="flex flex-1 items-center gap-4 p-2" @click="toggleCollapsedState">
                    <Badge variant="flat" size="lg">
                        <span v-if="isSetGroupVisible">
                            {{ __(setGroup.display) }}
                            <Icon name="ui/chevron-right" class="relative top-px size-3" />
                        </span>
                        {{ __(config.display) || config.handle }}
                    </Badge>
                    <Tooltip :markdown="__(config.instructions)">
                        <Icon
                            v-if="config.instructions && !collapsed"
                            name="info-square"
                            class="size-3.5! text-gray-500"
                        />
                    </Tooltip>
                    <Subheading
                        v-show="collapsed"
                        v-html="previewText"
                        class="overflow-hidden text-ellipsis whitespace-nowrap"
                    />
                </button>
                <div class="flex items-center gap-2" v-if="!readOnly">
                    <Tooltip :text="enabled ? __('Included in output') : __('Hidden from output')">
                        <Switch size="xs" :model-value="enabled" @update:model-value="toggleEnabledState" />
                    </Tooltip>

                    <Dropdown>
                        <template #trigger>
                            <Button icon="ui/dots" variant="ghost" size="xs" />
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
                                @click="destroy"
                            />
                        </DropdownMenu>
                    </Dropdown>
                </div>
            </header>

            <Motion
                layout
                class="overflow-hidden"
                :initial="{ height: collapsed ? '0px' : 'auto' }"
                :animate="{ height: collapsed ? '0px' : 'auto' }"
                :transition="{ duration: 0.25, type: 'tween' }"
            >
                <FieldsProvider
                    :fields="config.fields"
                    :field-path-prefix="fieldPathPrefix"
                    :meta-path-prefix="metaPathPrefix"
                >
                    <Fields class="p-4" />
                </FieldsProvider>
            </Motion>
        </div>
    </div>
</template>
