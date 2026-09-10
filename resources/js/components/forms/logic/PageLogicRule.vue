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
import PageRule from '@/components/forms/builder/pages/PageRule.vue';
import FieldNumber from '@/components/forms/FieldNumber.vue';
import { categories, categoryColorClasses } from '@/components/forms/builder/categories';

const emit = defineEmits(['collapsed', 'expanded', 'removed', 'update:rule']);

const props = defineProps({
    rule: { type: Object, required: true },
    pageId: { type: String, required: true },
    pageDisplay: { type: String, required: true },
    id: String,
    collapsed: Boolean,
    suggestableFields: { type: Array, default: () => [] },
    pageDestinationOptions: { type: Array, default: () => [] },
    fieldtypes: Array,
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

const getFieldConfig = (handle) => props.suggestableFields.find(field => field.handle === handle);
const getFieldDisplay = (handle) => __(getFieldConfig(handle)?.config?.display) || handle;

const getIconClass = (category) => {
    const color = categories[category]?.color || 'gray';
    return categoryColorClasses[color]?.icon || 'text-gray-600 dark:text-gray-400';
};

const firstFieldConfig = computed(() => {
    const firstCondition = props.rule.conditions?.[0];
    if (!firstCondition?.field) return null;
    const field = getFieldConfig(firstCondition.field);
    return {
        handle: firstCondition.field,
        display: __(field?.config?.display) || firstCondition.field,
        icon: field?.icon || 'generic-field',
        iconClass: getIconClass(field?.category),
    };
});

const previewParts = computed(() => {
    if (!props.rule.conditions || props.rule.conditions.length === 0) return null;

    const parts = [];

    props.rule.conditions.forEach((condition, index) => {
        if (!condition.field) return;

        if (index === 0) {
            parts.push({ type: 'operator', text: getOperatorLabel(condition.operator) });

            if (condition.value !== null && condition.value !== undefined && condition.value !== '') {
                const displayValue = Array.isArray(condition.value)
                    ? condition.value.join(', ')
                    : String(condition.value);
                parts.push({ type: 'value', text: displayValue });
            }
        } else {
            parts.push({ type: 'join', text: condition.join === 'or' ? __('or') : __('and') });
            parts.push({ type: 'field-plain', text: getFieldDisplay(condition.field) });
            parts.push({ type: 'operator', text: getOperatorLabel(condition.operator) });

            if (condition.value !== null && condition.value !== undefined && condition.value !== '') {
                const displayValue = Array.isArray(condition.value)
                    ? condition.value.join(', ')
                    : String(condition.value);
                parts.push({ type: 'value', text: displayValue });
            }
        }
    });

    if (parts.length === 0) return null;

    const destination = props.pageDestinationOptions.find(p => p.value === props.rule.destination);
    parts.push({
        type: 'destination',
        text: destination?.label || props.rule.destination || __('Select page'),
    });

    return parts;
});

const collapsedSummary = computed(() => {
    if (!props.rule.conditions || props.rule.conditions.length === 0) return __('No conditions configured');
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
            :data-collapsed="collapsed ?? undefined"
        >
            <header
                class="group/header animate-border-color flex items-center show-focus-within rounded-[calc(var(--radius-lg)-1px)] px-1.5 antialiased duration-200 dark:bg-gray-925 border-gray-300 dark:shadow-md"
                :class="{
                    'bg-white dark:bg-gray-900': collapsed,
                    'bg-gray-200/50 dark:bg-gray-950/35 rounded-b-none': !collapsed,
                }"
            >
                <button type="button" class="show-focus-within_target flex flex-1 items-center gap-1.75 p-2 py-1.75 ps-0 min-w-0 focus:outline-none cursor-pointer" @click="toggleCollapsedState">
                    <Badge v-if="collapsed" pill size="sm" color="white" class="font-medium text-gray-800 dark:text-gray-200">
                        {{ __('If') }}
                    </Badge>
                    <Badge v-if="collapsed && firstFieldConfig" size="lg" pill color="white" class="px-3 text-gray-950 gap-1">
                        <FieldNumber :field-key="firstFieldConfig.handle" class="me-0.5" />
                        <Icon
                            :name="firstFieldConfig.icon"
                            class="size-3.5 me-1 rounded-sm opacity-100!"
                            :class="firstFieldConfig.iconClass"
                            aria-hidden="true"
                        />
                        {{ firstFieldConfig.display }}
                    </Badge>
                    <Subheading v-show="collapsed" class="overflow-hidden text-ellipsis whitespace-nowrap text-xs flex items-center gap-1">
                        <template v-if="collapsedSummary">
                            <span class="lowercase">{{ collapsedSummary }}</span>
                        </template>
                        <template v-else-if="previewParts">
                            <template v-for="(part, index) in previewParts" :key="index">
                                <Badge
                                    v-if="part.type === 'operator'"
                                    class="inline-block px-1 py-1.5 font-medium st-text-trim-ex-alphabetic lowercase bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300"
                                >
                                    {{ part.text }}
                                </Badge>
                                <span v-else-if="part.type === 'value'" class="font-mono text-gray-900 dark:text-gray-100">{{ part.text }}</span>
                                <Badge
                                    v-else-if="part.type === 'join'"
                                    class="inline-block px-1 py-1.5 font-medium st-text-trim-ex-alphabetic lowercase bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300"
                                >
                                    {{ part.text }}
                                </Badge>
                                <span v-else-if="part.type === 'field-plain'" class="text-gray-700 dark:text-gray-300">{{ part.text }}</span>
                                <Badge
                                    v-else-if="part.type === 'destination'"
                                    pill
                                    class="inline-flex items-center gap-1.25 px-1.5 py-1.25 dark:py-1.25 font-medium bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-300"
                                >
                                    <Icon name="page" class="size-2.75! shrink-0" aria-hidden="true" />
                                    <span class="st-text-trim-cap">{{ __('Go to') }}</span>
                                    <span class="st-text-trim-cap">{{ part.text }}</span>
                                </Badge>
                            </template>
                        </template>
                    </Subheading>
                    <Badge v-if="!collapsed && firstFieldConfig" size="lg" pill color="white" class="px-3 text-gray-950 gap-1">
                        <FieldNumber :field-key="firstFieldConfig.handle" class="me-0.5" />
                        <Icon
                            :name="firstFieldConfig.icon"
                            class="size-3.5 me-0.5 rounded-sm opacity-100!"
                            :class="firstFieldConfig.iconClass"
                            aria-hidden="true"
                        />
                        {{ firstFieldConfig.display }}
                    </Badge>
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
                        <PageRule
                            :rule="rule"
                            :suggestable-fields
                            :page-destination-options
                            :fieldtypes
                            @update:rule="emit('update:rule', $event)"
                            @remove="emit('removed')"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
