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
import RuleBuilder from '@/components/forms/builder/Pages/RuleBuilder.vue';

const emit = defineEmits(['collapsed', 'expanded', 'removed', 'update:rules']);

const props = defineProps({
    config: Object,
    id: String,
    collapsed: Boolean,
    readOnly: Boolean,
    enabled: Boolean,
    hasError: Boolean,
    rules: { type: Array, default: () => [] },
    suggestableFields: { type: Array, default: () => [] },
    pageOptions: { type: Array, default: () => [] },
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

const previewParts = computed(() => {
    if (props.rules.length === 0) return null;

    const rule = props.rules[0];
    if (!rule.conditions || rule.conditions.length === 0) return null;

    const condition = rule.conditions[0];
    if (!condition.field) return null;

    const parts = [];
    parts.push({ type: 'field', text: getFieldDisplay(condition.field) });
    parts.push({ type: 'operator', text: getOperatorLabel(condition.operator) });

    if (condition.value !== null && condition.value !== undefined) {
        parts.push({ type: 'value', text: String(condition.value) });
    }

    if (rule.conditions.length > 1) {
        parts.push({ type: 'more', text: `+${rule.conditions.length - 1}` });
    }

    parts.push({ type: 'goto', text: __('go to') });

    const dest = props.pageOptions.find(p => p.value === rule.destination);
    parts.push({ type: 'destination', text: dest?.label || rule.destination || __('Select page') });

    if (props.rules.length > 1) {
        parts.push({ type: 'moreRules', text: `+${props.rules.length - 1} ${__('more')}` });
    }

    return parts;
});

const collapsedSummary = computed(() => {
    if (props.rules.length === 0) return __('No rules configured');
    if (!previewParts.value) return __('Configure conditions');
    return null;
});

const toggleCollapsedState = () => props.collapsed ? emit('expanded') : emit('collapsed');
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
                    <Badge v-if="collapsed" pill size="sm" color="white" class="gap-1.5">
                        <span>{{ __('If') }}</span>
                    </Badge>
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
                                <Badge
                                    v-else-if="part.type === 'goto'"
                                    pill
                                    size="sm"
                                    color="white"
                                    class="px-1.5 font-medium text-gray-700 dark:text-gray-300"
                                >
                                    {{ part.text }}
                                </Badge>
                                <span v-else-if="part.type === 'destination'" class="inline-flex items-center gap-1">
                                    <Icon name="page" class="size-3 text-gray-500 dark:text-gray-400" />
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ part.text }}</span>
                                </span>
                                <span v-else-if="part.type === 'moreRules'" class="text-gray-500 dark:text-gray-400 text-2xs ms-1">{{ part.text }}</span>
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
                        <RuleBuilder
                            :rules
                            :suggestable-fields
                            :page-options
                            @update:rules="emit('update:rules', $event)"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
