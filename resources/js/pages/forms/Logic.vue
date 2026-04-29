<script setup>
import Layout from '@/pages/layout/Layout.vue';
import FormsLayout from './Layout.vue';
import { Button, Card, Header, Heading, Icon, Panel, PanelHeader, StatusIndicator, ToggleGroup, ToggleItem, publishContextKey } from '@ui';
import LogicAddRuleButton from './logic-list/LogicAddRuleButton.vue';
import LogicRule from './logic-list/LogicRule.vue';
import { computed, provide, ref, watchEffect } from 'vue';
import { data_set } from '@/bootstrap/globals.js';
import { nanoid as uniqid } from 'nanoid';

defineOptions({ layout: [Layout, FormsLayout] });

const props = defineProps({
    form: Object,
});

const formTitle = computed(() => props.form?.title || __('Untitled Form'));
const logicView = ref('list');
const fieldPath = 'logic_rules';
const metaPath = 'logic_rules';
const sortableItemClass = 'logic-rule-block';
const loadingSet = ref(null);

const logicBlocks = ref([
    { _id: 'heard_about_us', type: 'heard_about_us', enabled: true, summary: __('equals Friend referral, then go to How long have you been a fan?') },
    { _id: 'fan_length', type: 'fan_length', enabled: true, summary: __('contains years, then go to Sign up for email notifications from The Midnight') },
    { _id: 'favorite_album', type: 'favorite_album', enabled: true, summary: __('equals Days of Thunder, and ‘:fieldname’ contains referral, then go to Which album was your second favorite?', { fieldname: __('How did you hear about us?') }) },
    { _id: 'second_favorite_album', type: 'second_favorite_album', enabled: false, summary: __('equals Endless Summer, then go to Sign up for email notifications from The Midnight') },
    { _id: 'age', type: 'age', enabled: true, summary: __('is greater than 21, then go to I want a free drink voucher') },
]);

const collapsed = ref(logicBlocks.value.map((block) => block._id));
const previews = ref({});
const meta = ref({});

const setConfigs = [
    { handle: 'heard_about_us', display: __('How did you hear about us?'), icon: 'fieldtype-select', iconClass: 'bg-orange-50 text-orange-600 dark:bg-transparent dark:text-orange-400', fields: [] },
    { handle: 'like_most', display: __('What do you like most about our band?'), icon: 'text-long', iconClass: 'bg-purple-50 text-purple-500 dark:bg-transparent dark:text-purple-400', fields: [] },
    { handle: 'fan_length', display: __('How long have you been a fan?'), icon: 'text-short', iconClass: 'bg-purple-50 text-purple-500 dark:bg-transparent dark:text-purple-400', fields: [] },
    { handle: 'favorite_album', display: __('Which album was your favorite?'), icon: 'fieldtype-radio', iconClass: 'bg-orange-50 text-orange-600 dark:bg-transparent dark:text-orange-400', fields: [] },
    { handle: 'second_favorite_album', display: __('Which album was your second favorite?'), icon: 'fieldtype-radio', iconClass: 'bg-orange-50 text-orange-600 dark:bg-transparent dark:text-orange-400', fields: [] },
    { handle: 'email_notifications_signup', display: __('Sign up for email notifications from The Midnight'), icon: 'fieldtype-checkboxes', iconClass: 'bg-orange-50 text-orange-600 dark:bg-transparent dark:text-orange-400', fields: [] },
    { handle: 'age', display: __('How old are you?'), icon: 'number', iconClass: 'bg-teal-50 text-teal-600 dark:bg-transparent dark:text-teal-400', fields: [] },
    { handle: 'free_drink_voucher', display: __('I want a free drink voucher'), icon: 'fieldtype-toggle', iconClass: 'bg-orange-50 text-orange-600 dark:bg-transparent dark:text-orange-400', fields: [] },
];

const groupConfigs = [
    {
        handle: 'logic',
        display: __('Logic Rules'),
        sets: setConfigs,
    },
];

const setConfigByHandle = computed(() => {
    return setConfigs.reduce((carry, config) => {
        carry[config.handle] = config;
        return carry;
    }, {});
});

const areAllRulesCollapsed = computed(() => {
    if (logicBlocks.value.length === 0) return false;
    return logicBlocks.value.every((block) => collapsed.value.includes(block._id));
});

const allRulesView = computed(() => {
    return areAllRulesCollapsed.value ? 'collapsed' : 'expanded';
});

const remainingSetConfigs = computed(() => {
    const usedHandles = new Set(logicBlocks.value.map((block) => block.type));
    return setConfigs.filter((config) => !usedHandles.has(config.handle));
});

const remainingGroupConfigs = computed(() => {
    return [
        {
            handle: 'logic',
            display: __('Logic Rules'),
            sets: remainingSetConfigs.value,
        },
    ];
});

watchEffect(() => {
    const nextPreviews = {};

    logicBlocks.value.forEach((block, index) => {
        data_set(nextPreviews, `${fieldPath}.${index}.summary_`, block.summary);
    });

    previews.value = nextPreviews;
});

function setFieldValue(path, value) {
    data_set(logicBlocks.value, path.replace(`${fieldPath}.`, ''), value);
}

function setFieldMeta(path, value) {
    data_set(meta.value, path.replace(`${metaPath}.`, ''), value);
}

function addSet(handle, index = logicBlocks.value.length) {
    if (logicBlocks.value.some((block) => block.type === handle)) return;

    loadingSet.value = handle;
    const config = setConfigByHandle.value[handle];

    const newRule = {
        _id: uniqid(),
        type: handle,
        enabled: true,
        summary: config?.display
            ? __('If :rule has matching conditions, continue to the configured destination.', { rule: config.display })
            : __('No conditions yet.'),
    };

    logicBlocks.value.splice(index, 0, newRule);
    collapsed.value.push(newRule._id);

    loadingSet.value = null;
}

function collapseSet(id) {
    if (!collapsed.value.includes(id)) {
        collapsed.value.push(id);
    }
}

function expandSet(id) {
    collapsed.value = collapsed.value.filter(setId => setId !== id);
}

function removeSet(id) {
    logicBlocks.value = logicBlocks.value.filter((block) => block._id !== id);
    collapsed.value = collapsed.value.filter((setId) => setId !== id);
}

function expandAllRules() {
    collapsed.value = [];
}

function collapseAllRules() {
    collapsed.value = logicBlocks.value.map((block) => block._id);
}

provide('replicatorSets', groupConfigs);
provide(publishContextKey, {
    setFieldValue,
    setFieldMeta,
    previews,
});
</script>

<template>
    <Teleport to="#form-layout-actions">
        <Button variant="primary" :aria-label="__('Save')">
            <Icon name="save" class="sm:hidden" />
            <span class="hidden sm:inline">{{ __('Save') }}</span>
        </Button>
    </Teleport>

    <div class="py-4 mx-auto max-w-5xl">
        <Header class="mb-2">
            <template #title>
                <StatusIndicator status="published" />
                {{ formTitle }}
            </template>
            <template #actions>
                <ToggleGroup v-model="logicView" size="sm">
                    <ToggleItem value="list" icon="layout-list" :label="__('List')" />
                    <ToggleItem value="tree" icon="logic-tree" :label="__('Tree')" />
                </ToggleGroup>
            </template>
        </Header>

        <Panel>
            <PanelHeader>
                <div class="flex items-center justify-between gap-3">
                    <Heading :text="__('Form Logic')" />
                    <ToggleGroup :model-value="allRulesView" size="xs">
                        <ToggleItem value="expanded" icon="expand" :aria-label="__('Expand all rules')" @click="expandAllRules" />
                        <ToggleItem value="collapsed" icon="collapse" :aria-label="__('Collapse all rules')" @click="collapseAllRules" />
                    </ToggleGroup>
                </div>
            </PanelHeader>
            <Card>
                <div class="relative space-y-6 mb-0" data-logic-list>
                    <LogicRule
                        v-for="(block, index) in logicBlocks"
                        :id="block._id"
                        :key="block._id"
                        :index
                        :field-path="fieldPath"
                        :meta-path="metaPath"
                        :values="block"
                        :config="setConfigByHandle[block.type]"
                        :sortable-item-class="sortableItemClass"
                        :collapsed="collapsed.includes(block._id)"
                        :enabled="block.enabled"
                        :read-only="false"
                        :can-add-rule="true"
                        :has-error="false"
                        :show-field-previews="true"
                        @collapsed="collapseSet(block._id)"
                        @expanded="expandSet(block._id)"
                        @removed="removeSet(block._id)"
                    />
                </div>
                <LogicAddRuleButton
                    :groups="remainingGroupConfigs"
                    :sets="remainingSetConfigs"
                    :show-connector="logicBlocks.length > 0"
                    :index="logicBlocks.length"
                    :label="__('Add Rule')"
                    :is-first="logicBlocks.length === 0"
                    :loading-set="loadingSet"
                    :search-placeholder="__('Search Fields')"
                    @added="addSet"
                />
            </Card>
        </Panel>
    </div>
</template>

<style scoped>
[data-logic-list]::before {
    content: '';
    position: absolute;
    top: 1.5rem;
    bottom: 0;
    inset-inline-start: 0.875rem;
    border-inline-start: 1px dashed var(--color-gray-400);
}

.dark [data-logic-list]::before {
    border-inline-start-color: var(--color-gray-600);
}
</style>
