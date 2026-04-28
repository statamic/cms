<script setup>
import { computed, inject, ref } from 'vue';
import {
    Badge,
    Button,
    Dropdown,
    DropdownItem,
    DropdownMenu,
    Icon,
    Subheading,
    injectPublishContext as injectContainerContext,
} from '@/components/ui';
import PreviewHtml from '@/components/fieldtypes/replicator/PreviewHtml.js';
import LogicFlowMock from '../LogicFlowMock.vue';
import { reveal } from '@api';

const emit = defineEmits(['collapsed', 'expanded', 'removed']);

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
    readOnly: Boolean,
    enabled: Boolean,
    hasError: Boolean,
    canAddRule: Boolean,
    showFieldPreviews: Boolean,
});

const { previews } = injectContainerContext();
const fieldPathPrefix = computed(() => `${props.fieldPath}.${props.index}`);
const isInvalid = computed(() => Object.keys(props.config).length === 0);

const ruleGroup = computed(() => {
    if (replicatorSets.length < 1) return null;

    return (
        replicatorSets.find((group) => {
            return group.sets.filter((set) => set.handle === props.config.handle).length > 0;
        }) ?? {}
    );
});

const isRuleGroupVisible = computed(() => replicatorSets.length > 1 && ruleGroup.value.display);

const previewText = computed(() => {
    return Object.entries(data_get(previews.value, fieldPathPrefix.value) || {})
        .filter(([handle]) => {
            if (!handle.endsWith('_')) return false;
            handle = handle.substr(0, handle.length - 1);
            const config = props.config.fields.find((f) => f.handle === handle);
            if (!config) return false;
            return config.replicator_preview === undefined ? props.showFieldPreviews : config.replicator_preview;
        })
        .map(([, value]) => value)
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

function toggleCollapsedState() {
    props.collapsed ? emit('expanded') : emit('collapsed');
}

const rootEl = ref();
reveal.use(rootEl, () => emit('expanded'));
</script>

<template>
    <div ref="rootEl" :class="sortableItemClass">
        <slot name="picker" />
        <div
            layout
            data-replicator-rule
            class="@container relative w-full rounded-lg border border-gray-300 text-base dark:border-white/10 bg-white dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black shadow-ui-sm"
            :class="{ 'border-red-500': hasError }"
            :data-collapsed="collapsed ?? undefined"
            :data-error="hasError ?? undefined"
            :data-invalid="isInvalid ?? undefined"
            :data-readonly="readOnly ?? undefined"
            :data-type="config.handle"
        >
            <header
                class="group/header animate-border-color flex items-center show-focus-within rounded-[calc(var(--radius-lg)-1px)] px-1.5 antialiased duration-200 bg-gray-100/50 dark:bg-gray-925 hover:bg-gray-100 dark:hover:bg-gray-950/45 border-gray-300 dark:shadow-md"
                :class="{ 'bg-gray-200/50 dark:bg-gray-950/35 rounded-b-none': !collapsed }"
            >
                <button type="button" class="show-focus-within_target flex flex-1 items-center gap-1.75 p-2 py-1.75 min-w-0 focus:outline-none cursor-pointer" @click="toggleCollapsedState">
                    <span v-if="collapsed" class="text-sm text-gray-700 dark:text-gray-300">{{ __('If') }}</span>
                    <Badge size="lg" pill color="white" class="px-3">
                        <span v-if="isRuleGroupVisible" class="flex items-center gap-2">
                            {{ __(ruleGroup.display) }}
                            <Icon name="chevron-right" class="relative top-px size-3" />
                        </span>
                        <Icon
                            v-if="config.icon"
                            :name="config.icon"
                            class="size-3.5 me-1 rounded-sm opacity-100!"
                            :class="config.iconClass"
                            aria-hidden="true"
                        />
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
                <Dropdown>
                    <template #trigger>
                        <Button icon="dots" variant="ghost" size="xs" class="me-2" :aria-label="__('Open row actions')" />
                    </template>
                    <DropdownMenu>
                        <DropdownItem
                            :text="__('Delete row')"
                            icon="trash"
                            variant="destructive"
                            @click="emit('removed')"
                        />
                    </DropdownMenu>
                </Dropdown>
            </header>

            <div
                v-show="!collapsed"
                :class="{ 'contain-paint': collapsed, 'isolate': !collapsed }"
                class="border-t border-t-gray-300! dark:border-t-white/10!"
            >
                <div :tabindex="collapsed ? -1 : undefined" :inert="collapsed">
                    <div class="p-4">
                        <LogicFlowMock
                            :initial-condition-label="__(config.display) || config.handle"
                            :initial-condition-icon="config.icon || 'fieldtype-radio'"
                            :initial-condition-icon-class="config.iconClass || 'bg-orange-50 text-orange-600 dark:bg-orange-950 dark:text-orange-400'"
                        />
                        <Button
                            size="sm"
                            variant="subtle"
                            class="-ms-2 mt-2 bg-transparent!"
                            :text="__('+ Add Condition')"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
