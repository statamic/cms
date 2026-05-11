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
    collapsedPrefixLabel: {
        type: String,
        default: () => __('If'),
    },
    collapsedPrefixIcon: {
        type: String,
        default: null,
    },
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
const operatorLabels = [
    __('does not equal'),
    __('is greater than'),
    __('is less than'),
    __('equals'),
    __('contains'),
    __('and'),
    __('or'),
];
const valueOperators = new Set([
    __('does not equal').toLowerCase(),
    __('is greater than').toLowerCase(),
    __('is less than').toLowerCase(),
    __('equals').toLowerCase(),
    __('contains').toLowerCase(),
]);

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

const collapsedPreviewText = computed(() => {
    if (previewText.value && previewText.value.trim() !== '') return previewText.value;
    return '';
});

const destinationDisplayByHandle = computed(() => {
    return (replicatorSets ?? [])
        .flatMap((group) => group?.sets ?? [])
        .reduce((carry, set) => {
            carry[set.handle] = set.display;
            return carry;
        }, {});
});

const collapsedSummaryParts = computed(() => {
    const summary = props.values?.summary;
    const noConditionsText = __('No conditions configured yet.');

    if (!summary) return [];
    if (summary.trim() === noConditionsText) return [{ type: 'text', text: noConditionsText }];

    const marker = __('then go to');
    const markerIndex = summary.toLowerCase().indexOf(marker.toLowerCase());
    const before = (markerIndex === -1 ? summary : summary.slice(0, markerIndex + marker.length)).replace(/,/g, '');
    const after = markerIndex === -1 ? '' : summary.slice(markerIndex + marker.length).trimStart();

    const destinationHandle = after.trim().replace(/^(["'`]|[“”‘’])/, '').replace(/(["'`]|[“”‘’])$/, '');
    const destinationResolved = destinationDisplayByHandle.value[destinationHandle] ?? destinationDisplayByHandle.value[after] ?? after;

    const operatorPattern = new RegExp(`(${operatorLabels.map((label) => label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|')})`, 'gi');

    const parts = before
        .split(operatorPattern)
        .filter((part) => part !== '')
        .map((part) => {
            const isOperator = operatorLabels.some((label) => label.toLowerCase() === part.toLowerCase());
            return { type: isOperator ? 'operator' : 'text', text: part };
        });

    const markerLower = marker.toLowerCase();
    const partsWithValueSegments = [];

    parts.forEach((part, index) => {
        const previous = parts[index - 1];
        const followsOperator = previous?.type === 'operator' && part.type === 'text';
        const shouldTreatAsValueSegment = followsOperator && valueOperators.has(previous.text.toLowerCase());

        if (!shouldTreatAsValueSegment) {
            partsWithValueSegments.push(part);
            return;
        }

        const markerInPart = part.text.toLowerCase().indexOf(markerLower);

        if (markerInPart === -1) {
            partsWithValueSegments.push({ type: 'operatorValue', text: part.text });
            return;
        }

        const valueText = part.text.slice(0, markerInPart).trimEnd();
        const trailingText = part.text.slice(markerInPart);

        if (valueText) partsWithValueSegments.push({ type: 'operatorValue', text: valueText });
        if (trailingText) partsWithValueSegments.push({ type: 'text', text: trailingText });
    });

    if (markerIndex !== -1) {
        partsWithValueSegments.push({ type: 'destination', text: destinationResolved });
    }

    return partsWithValueSegments;
});

const isThenGoToPart = (part) => {
    if (part?.type !== 'text' || !part.text) return false;
    return part.text.toLowerCase().includes(__('then go to').toLowerCase());
};

const stripThenGoTo = (text = '') => {
    const marker = __('then go to');
    return text.replace(new RegExp(marker, 'ig'), '').trim();
};

const formatPageReferenceChip = (text = '') => {
    const trimmed = text.trim();
    const normalized = trimmed.toLowerCase();
    const pageOneLabel = __('Page 1').trim().toLowerCase();
    if (normalized === pageOneLabel || normalized === '1' || /^page\s*1\b/i.test(trimmed)) {
        return __('1 of 2');
    }
    return trimmed;
};

const pageLogicDestinationPart = computed(() => collapsedSummaryParts.value.find((part) => part.type === 'destination'));

const pageLogicCollapsedSummaryParts = computed(() => collapsedSummaryParts.value.filter((part) => part.type !== 'destination'));

const fieldLogicAnotherQuestionPart = computed(() => {
    const currentQuestion = (__(props.config?.display || props.config?.handle || '')).trim().toLowerCase();
    const preferred = collapsedSummaryParts.value.find((part) => {
        if (part.type !== 'text' && part.type !== 'destination') return false;
        if (!part.text?.includes('?')) return false;
        if (part.type === 'text' && isThenGoToPart(part)) return false;
        return part.text.trim().toLowerCase() !== currentQuestion;
    });

    if (preferred) return preferred;

    return collapsedSummaryParts.value.find((part) => {
        if (part.type !== 'text' && part.type !== 'destination') return false;
        if (!part.text?.trim()) return false;
        if (part.type === 'text' && isThenGoToPart(part)) return false;
        return part.text.trim().toLowerCase() !== currentQuestion;
    });
});

const fieldLogicOperatorPart = computed(() => {
    return collapsedSummaryParts.value.find((part) => part.type === 'operator');
});

const fieldLogicOperatorValuePart = computed(() => {
    return collapsedSummaryParts.value.find((part) => part.type === 'operatorValue');
});

const fieldLogicRandomQuestionPool = [
    __('How did you hear about us?'),
    __('How long have you been a fan?'),
    __('Which album was your favorite?'),
    __('Which album was your second favorite?'),
    __('How old are you?'),
];

const fieldLogicSecondQuestionText = computed(() => {
    const currentQuestion = (__(props.config?.display || props.config?.handle || '')).trim().toLowerCase();
    const options = fieldLogicRandomQuestionPool.filter((question) => question.trim().toLowerCase() !== currentQuestion);
    if (!options.length) return fieldLogicRandomQuestionPool[0];

    const key = `${props.id || ''}:${props.config?.handle || ''}`;
    const hash = Array.from(key).reduce((acc, char) => ((acc * 33) + char.charCodeAt(0)) >>> 0, 5381);
    return options[hash % options.length];
});

const fieldLogicOperatorText = computed(() => fieldLogicOperatorPart.value?.text || __('equals'));
const fieldLogicOperatorValueText = computed(() => fieldLogicOperatorValuePart.value?.text || __('referral'));

const showSecondaryCondition = computed(() => {
    const summary = props.values?.summary?.toLowerCase() || '';
    return summary.includes(', and ') || summary.includes(' and ');
});

const mockPresetByHandle = {
    heard_about_us: {
        logicOperator: 'equals',
        logicValue: 'referral',
        logicDestination: 'page_2',
        logicJoin: 'and',
        logicContainsOperator: 'contains',
        logicContainsAnswer: 'referral',
    },
    fan_length: {
        logicOperator: 'contains',
        logicValue: 'referral',
        logicDestination: 'page_1',
        logicJoin: 'and',
        logicContainsOperator: 'contains',
        logicContainsAnswer: 'years',
    },
    favorite_album: {
        logicOperator: 'equals',
        logicValue: 'days_of_thunder',
        logicDestination: 'page_2',
        logicConditionField: 'heard_about_us',
        logicJoin: 'and',
        logicContainsOperator: 'contains',
        logicContainsAnswer: 'referral',
    },
    second_favorite_album: {
        logicOperator: 'equals',
        logicValue: 'endless_summer',
        logicDestination: 'page_1',
        logicJoin: 'and',
        logicContainsOperator: 'contains',
        logicContainsAnswer: 'summer',
    },
    age: {
        logicOperator: 'equals',
        logicValue: '21',
        logicDestination: 'page_2',
        logicConditionField: 'age',
        logicJoin: 'and',
        logicContainsOperator: 'contains',
        logicContainsAnswer: '21',
    },
};

const mockPreset = computed(() => {
    return mockPresetByHandle[props.config?.handle] || {};
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
                class="group/header animate-border-color flex items-center show-focus-within rounded-[calc(var(--radius-lg)-1px)] px-1.5 antialiased duration-200 dark:bg-gray-925 border-gray-300 dark:shadow-md"
                :class="{
                    'bg-white dark:bg-gray-900': collapsed,
                    'bg-gray-200/50 dark:bg-gray-950/35 rounded-b-none': !collapsed,
                }"
            >
                <button type="button" class="show-focus-within_target flex flex-1 items-center gap-1.75 p-2 py-1.75 ps-0 min-w-0 focus:outline-none cursor-pointer" @click="toggleCollapsedState">
                    <Badge v-if="collapsed && !collapsedPrefixIcon" pill size="sm" color="white" class="gap-1.5">
                        <span>{{ collapsedPrefixLabel }}</span>
                    </Badge>
                    <Badge size="lg" pill color="white" class="px-3 text-gray-950 gap-1">
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
                    <Badge
                        v-if="collapsed && collapsedPrefixIcon"
                        pill
                        color="white"
                        class="px-1.5 font-medium text-gray-800 dark:text-gray-200 gap-1.5"
                    >
                        <Icon v-if="collapsedPrefixIcon" :name="collapsedPrefixIcon" class="size-3.5!" aria-hidden="true" />
                        <span>{{ collapsedPrefixLabel }}</span>
                    </Badge>
                    <Icon
                        v-if="config.instructions && !collapsed"
                        name="info-square"
                        class="size-3.5! text-gray-500"
                        v-tooltip="__(config.instructions)"
                    />
                    <Subheading v-show="collapsed" class="overflow-hidden text-ellipsis whitespace-nowrap lowercase text-xs">
                        <template v-if="collapsedPrefixIcon">
                            <span>{{ fieldLogicSecondQuestionText }}</span>
                            <Badge
                                pill
                                color="white"
                                class="inline-block px-1.5 font-medium bg-gray-100 text-gray-800 dark:bg-gray-850 dark:text-gray-200"
                                style="text-box: trim-start text;"
                            >
                                {{ fieldLogicOperatorText }}
                            </Badge>
                            <span class="font-mono text-[0.75rem]">
                                {{ fieldLogicOperatorValueText }}
                            </span>
                        </template>
                        <span v-else-if="collapsedPreviewText" v-html="collapsedPreviewText" />
                        <template v-else>
                            <template v-for="(part, index) in pageLogicCollapsedSummaryParts" :key="`${part.type}-${index}`">
                                <Badge
                                    v-if="part.type === 'operator'"
                                    pill
                                    color="white"
                                    class="inline-block px-1.5 font-medium bg-gray-100 text-gray-800 dark:bg-gray-850 dark:text-gray-200"
                                    style="text-box: trim-start text;"
                                >
                                    {{ part.text }}
                                </Badge>
                                <span v-else-if="part.type === 'operatorValue'" class="font-mono text-[0.75rem]">
                                    {{ part.text }}
                                </span>
                                <Badge
                                    v-else-if="!collapsedPrefixIcon && isThenGoToPart(part)"
                                    pill
                                    color="white"
                                    class="inline-flex items-center gap-1 px-1.5 font-medium text-gray-800 dark:text-gray-200"
                                >
                                    <Icon name="page" class="size-3! shrink-0 me-0.5 text-gray-500 dark:text-gray-300" aria-hidden="true" />
                                    <span style="text-box: trim-start text;">{{ __('go to') }}</span>
                                    <span
                                        v-if="pageLogicDestinationPart?.text"
                                        style="text-box: trim-start text; font-variant-numeric: proportional-nums;"
                                    >{{ formatPageReferenceChip(pageLogicDestinationPart.text) }}</span>
                                </Badge>
                                <template v-else>{{ part.text }}</template>
                            </template>
                        </template>
                    </Subheading>
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
                            :initial-condition-icon-class="config.iconClass || 'text-orange-600 dark:text-orange-400'"
                            :show-secondary-condition="showSecondaryCondition"
                            :use-when-selector="Boolean(collapsedPrefixIcon)"
                            :use-page-destination-options="!collapsedPrefixIcon"
                            :mock-preset="mockPreset"
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
