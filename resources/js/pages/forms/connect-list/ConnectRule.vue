<script setup>
import { computed, inject, ref } from 'vue';
import {
    Badge,
    Button,
    Dropdown,
    DropdownItem,
    DropdownMenu,
    Icon,
    Switch,
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

const { previews, setFieldValue } = injectContainerContext();
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

    const answerLabel = __('Friend referral');
    const partsWithAnswerClass = parts.flatMap((part) => {
        if (part.type !== 'text' || !part.text?.includes(answerLabel)) return [part];

        const [beforeAnswer, afterAnswer] = part.text.split(answerLabel);
        const nextParts = [];
        if (beforeAnswer) nextParts.push({ type: 'text', text: beforeAnswer });
        nextParts.push({ type: 'answer', text: answerLabel });
        if (afterAnswer) nextParts.push({ type: 'text', text: afterAnswer });
        return nextParts;
    });

    if (markerIndex !== -1) {
        partsWithAnswerClass.push({ type: 'destination', text: destinationResolved });
    }

    return partsWithAnswerClass;
});

const showSecondaryCondition = computed(() => {
    const summary = props.values?.summary?.toLowerCase() || '';
    return summary.includes(', and ') || summary.includes(' and ');
});

const initialConditionIcon = computed(() => {
    if (props.config?.handle === 'heard_about_us') return 'fieldtype-select';
    return props.config?.conditionIcon || props.config?.icon || 'fieldtype-radio';
});

const initialConditionIconClass = computed(() => {
    if (props.config?.handle === 'heard_about_us') return 'text-orange-600 dark:text-orange-400';
    return props.config?.conditionIconClass || props.config?.iconClass || 'text-orange-600 dark:text-orange-400';
});

const mockPresetByHandle = {
    heard_about_us: {
        logicOperator: 'equals',
        logicValue: 'referral',
        logicDestination: 'fan_length',
        logicJoin: 'and',
        logicContainsOperator: 'contains',
        logicContainsAnswer: 'referral',
    },
    fan_length: {
        logicOperator: 'contains',
        logicValue: 'referral',
        logicDestination: 'email_notifications',
        logicJoin: 'and',
        logicContainsOperator: 'contains',
        logicContainsAnswer: 'years',
    },
    favorite_album: {
        logicOperator: 'equals',
        logicValue: 'days_of_thunder',
        logicDestination: 'second_favorite',
        logicConditionField: 'heard_about_us',
        logicJoin: 'and',
        logicContainsOperator: 'contains',
        logicContainsAnswer: 'referral',
    },
    second_favorite_album: {
        logicOperator: 'equals',
        logicValue: 'endless_summer',
        logicDestination: 'email_notifications',
        logicJoin: 'and',
        logicContainsOperator: 'contains',
        logicContainsAnswer: 'summer',
    },
    age: {
        logicOperator: 'equals',
        logicValue: '21',
        logicDestination: 'free_drink_voucher',
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

function toggleEnabledState() {
    setFieldValue(`${fieldPathPrefix.value}.enabled`, !props.enabled);
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
                <button type="button" class="show-focus-within_target flex flex-1 items-center gap-1.75 p-2 py-1.75 min-w-0 focus:outline-none cursor-pointer" @click="toggleCollapsedState">
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
                    <Badge v-if="collapsed" pill size="sm" color="white">{{ __('if') }}</Badge>
                    <Icon
                        v-if="config.instructions && !collapsed"
                        name="info-square"
                        class="size-3.5! text-gray-500"
                        v-tooltip="__(config.instructions)"
                    />
                    <Subheading v-show="collapsed" class="overflow-hidden text-ellipsis whitespace-nowrap gap-1.5! lowercase">
                        <span v-if="collapsedPreviewText" v-html="collapsedPreviewText" />
                        <template v-else>
                            <template v-for="(part, index) in collapsedSummaryParts" :key="`${part.type}-${index}`">
                                <Badge
                                    v-if="part.type === 'operator'"
                                    size="sm"
                                    pill
                                    color="white"
                                    class="inline-block px-1.5 py-0 text-[12px] font-medium bg-gray-100 text-gray-800 dark:bg-gray-850 dark:text-gray-200"
                                    style="text-box: trim-start text;"
                                >
                                    {{ part.text }}
                                </Badge>
                                <template v-else-if="part.type === 'destination'">&nbsp;{{ part.text }}</template>
                                <span v-else-if="part.type === 'answer'" class="font-mono text-[0.725rem]">{{ part.text }}</span>
                                <template v-else>{{ part.text }}</template>
                            </template>
                        </template>
                    </Subheading>
                </button>
                <div class="flex items-center gap-2" v-if="!readOnly">
                    <Switch
                        size="xs"
                        :model-value="enabled"
                        @update:model-value="toggleEnabledState"
                        v-tooltip="enabled ? __('Email notification enabled') : __('Email notification disabled')"
                    />
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
                </div>
            </header>

            <div
                v-show="!collapsed"
                :class="{ 'contain-paint': collapsed, 'isolate': !collapsed }"
                class="border-t border-t-gray-300! dark:border-t-white/10!"
            >
                <div :tabindex="collapsed ? -1 : undefined" :inert="collapsed">
                    <div class="p-4">
                        <LogicFlowMock
                            :initial-condition-label="__(config.conditionDisplay || config.display) || config.handle"
                            :initial-condition-icon="initialConditionIcon"
                            :initial-condition-icon-class="initialConditionIconClass"
                            :show-secondary-condition="showSecondaryCondition"
                            :destination-step-label="__('Send an email to jack@statamic.com')"
                            :show-destination-selector="false"
                            :mock-preset="mockPreset"
                        />
                        <Button
                            size="sm"
                            variant="subtle"
                            class="-ms-2 mt-2 bg-transparent!"
                            :text="__('+ Add Condition (The above badge should be an expandable form)')"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
:deep(.logic-text-badge.logic-text__condition) {
    margin-top: 0.5rem;
}
</style>
