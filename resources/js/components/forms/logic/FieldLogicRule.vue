<script setup>
import { computed } from 'vue';
import {
    Badge,
    Button,
    Dropdown,
    DropdownItem,
    DropdownMenu,
    Icon,
    Subheading,
} from '@/components/ui';
import FieldConditionsBuilder from '@/components/field-conditions/Builder.vue';

const emit = defineEmits(['collapsed', 'expanded', 'removed', 'update:conditions']);

const props = defineProps({
    config: Object,
    id: String,
    collapsed: Boolean,
    readOnly: Boolean,
    enabled: Boolean,
    hasError: Boolean,
    conditions: { type: Object, default: () => ({}) },
    suggestableFields: { type: Array, default: () => [] },
});

const operatorLabels = {
    '': __('equals'),
    'equals': __('equals'),
    'not': __('not'),
    'not_equals': __('does not equal'),
    'contains': __('contains'),
    'not_contains': __('does not contain'),
    'is_empty': __('is empty'),
    'not_empty': __('is not empty'),
    'starts_with': __('starts with'),
    'ends_with': __('ends with'),
    '==': __('equals'),
    '!=': __('does not equal'),
    '>': __('is greater than'),
    '<': __('is less than'),
    '>=': __('is at least'),
    '<=': __('is at most'),
};

const getOperatorLabel = (operator) => operatorLabels[operator] || operator || __('equals');
const getFieldDisplay = (handle) => props.suggestableFields.find(field => field.handle === handle)?.config?.display || handle;

const hasConditions = computed(() => props.conditions.if || props.conditions.unless || props.conditions.if_any || props.conditions.unless_any);
const isHideCondition = computed(() => props.conditions.unless || props.conditions.unless_any);

const previewParts = computed(() => {
    const conditionsObj = props.conditions.if || props.conditions.unless || props.conditions.if_any || props.conditions.unless_any;
    if (!conditionsObj || typeof conditionsObj !== 'object') return null;

    const entries = Object.entries(conditionsObj);
    if (entries.length === 0) return null;

    const [fieldHandle, rawValue] = entries[0];
    const parts = [];

    parts.push({ type: 'field', text: getFieldDisplay(fieldHandle) });

    let operator = '';
    let value = rawValue;

    if (typeof rawValue === 'string') {
        const operatorPrefixes = ['not ', 'contains ', 'is empty', 'not empty', 'starts_with ', 'ends_with '];
        for (const prefix of operatorPrefixes) {
            if (rawValue.toLowerCase().startsWith(prefix)) {
                operator = prefix.trim();
                value = rawValue.slice(prefix.length).trim();
                break;
            }
        }
    }

    parts.push({ type: 'operator', text: getOperatorLabel(operator) });

    if (value && value !== 'empty') {
        parts.push({ type: 'value', text: String(value) });
    }

    if (entries.length > 1) {
        parts.push({ type: 'more', text: `+${entries.length - 1}` });
    }

    return parts;
});

const collapsedSummary = computed(() => {
    if (!hasConditions.value) return __('Always show');
    if (!previewParts.value) return __('Configure conditions');
    return null;
});

const prefixLabel = computed(() => isHideCondition.value ? __('Hide when') : __('Show when'));
const prefixIcon = computed(() => isHideCondition.value ? 'eye-closed' : 'eye');

const toggleCollapsedState = () => props.collapsed ? emit('expanded') : emit('collapsed');

const onConditionsUpdated = (conditions) => emit('update:conditions', conditions);
const onAlwaysSaveUpdated = (alwaysSave) => emit('update:conditions', { ...props.conditions, always_save: alwaysSave });
</script>

<template>
    <div>
        <div
            layout
            data-replicator-rule
            class="@container relative w-full rounded-lg border border-gray-300 text-base dark:border-white/10 bg-white dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black shadow-ui-sm"
            :class="{ 'border-red-500': hasError }"
            :data-collapsed="collapsed ?? undefined"
            :data-error="hasError ?? undefined"
            :data-readonly="readOnly ?? undefined"
            :data-type="config?.handle"
        >
            <header
                class="group/header animate-border-color flex items-center show-focus-within rounded-[calc(var(--radius-lg)-1px)] px-1.5 antialiased duration-200 dark:bg-gray-925 border-gray-300 dark:shadow-md"
                :class="{
                    'bg-white dark:bg-gray-900': collapsed,
                    'bg-gray-200/50 dark:bg-gray-950/35 rounded-b-none': !collapsed,
                }"
            >
                <button type="button" class="show-focus-within_target flex flex-1 items-center gap-1.75 p-2 py-1.75 ps-0 min-w-0 focus:outline-none cursor-pointer" @click="toggleCollapsedState">
                    <Badge size="lg" pill color="white" class="px-3 text-gray-950 gap-1">
                        <Icon
                            v-if="config?.icon"
                            :name="config.icon"
                            class="size-3.5 me-1 rounded-sm opacity-100!"
                            :class="config.iconClass"
                            aria-hidden="true"
                        />
                        {{ __(config?.display) || config?.handle }}
                    </Badge>
                    <Badge
                        v-if="collapsed"
                        pill
                        color="white"
                        class="px-1.5 font-medium text-gray-800 dark:text-gray-200 gap-1.5"
                    >
                        <Icon :name="prefixIcon" class="size-3.5!" aria-hidden="true" />
                        <span>{{ prefixLabel }}</span>
                    </Badge>
                    <Subheading v-show="collapsed" class="overflow-hidden text-ellipsis whitespace-nowrap text-xs flex items-center gap-1">
                        <template v-if="collapsedSummary">
                            <span class="lowercase">{{ collapsedSummary }}</span>
                        </template>
                        <template v-else-if="previewParts">
                            <template v-for="(part, index) in previewParts" :key="index">
                                <span v-if="part.type === 'field'" class="text-gray-700 dark:text-gray-300">{{ part.text }}</span>
                                <Badge
                                    v-else-if="part.type === 'operator'"
                                    pill
                                    size="sm"
                                    color="white"
                                    class="px-1.5 font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                >
                                    {{ part.text }}
                                </Badge>
                                <span v-else-if="part.type === 'value'" class="font-mono text-[0.7rem] text-gray-900 dark:text-gray-100">{{ part.text }}</span>
                                <span v-else-if="part.type === 'more'" class="text-gray-500 dark:text-gray-400 text-2xs">{{ part.text }}</span>
                            </template>
                        </template>
                    </Subheading>
                </button>
                <Dropdown align="end">
                    <template #trigger>
                        <Button icon="dots" variant="ghost" size="xs" class="me-2" :aria-label="__('Open row actions')" />
                    </template>
                    <DropdownMenu>
                        <DropdownItem
                            :text="__('Delete rule')"
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
                        <FieldConditionsBuilder
                            :config="conditions"
                            :suggestable-fields
                            :allow-custom-conditions="false"
                            :show-always-save="false"
                            size="sm"
                            @updated="onConditionsUpdated"
                            @updated-always-save="onAlwaysSaveUpdated"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
