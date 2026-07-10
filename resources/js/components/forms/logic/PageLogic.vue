<script setup>
import { Button, Card, Heading, Icon, Panel, PanelHeader } from '@ui';
import AddLogicRuleButton from './AddLogicRuleButton.vue';
import PageLogicRule from './PageLogicRule.vue';
import LogicEmptyState from './LogicEmptyState.vue';
import { computed, nextTick, ref, watch } from 'vue';
import { nanoid as uniqid } from 'nanoid';

const emit = defineEmits(['update:pages']);

const props = defineProps({
    pages: { type: Array, required: true },
    suggestableFields: { type: Array, required: true },
    fieldtypes: Array,
});

const collapsed = ref([]);

const rules = computed(() => {
    const rules = [];

    props.pages.forEach((page, pageIndex) => {
        if (page.rules && page.rules.length > 0) {
            page.rules.forEach(rule => {
                rules.push({
                    ...rule,
                    _pageId: page._id,
                    _pageDisplay: page.display || __('Page :number', { number: pageIndex + 1 }),
                });
            });
        }
    });

    return rules;
});

const expand = (id) => collapsed.value = collapsed.value.filter(ruleId => ruleId !== id);
const expandAll = () => collapsed.value = [];
const collapseAll = () => collapsed.value = rules.value.map(rule => rule._id);

const collapse = (id) => {
    if (!collapsed.value.includes(id)) {
        collapsed.value.push(id);
    }
};

const allRulesAreCollapsed = computed(() => rules.value.every(rule => collapsed.value.includes(rule._id)));
const rulesView = computed(() => allRulesAreCollapsed.value ? 'collapsed' : 'expanded');

const getSuggestableFieldsForPage = (pageId) => {
    const pageIndex = props.pages.findIndex(page => page._id === pageId);
    return props.suggestableFields.filter(field => field.pageIndex <= pageIndex);
};

const getPageDestinationOptions = (pageId) => {
    const pageIndex = props.pages.findIndex(page => page._id === pageId);
    return props.pages
        .slice(pageIndex + 1)
        .map((page, index) => ({
            label: page.display || __('Page :number', { number: pageIndex + index + 2 }),
            value: page._id,
            icon: 'page',
        }));
};

const availablePages = computed(() => {
    return props.pages.slice(0, -1).map((page, index) => ({
        handle: page._id,
        display: page.display || __('Page :number', { number: index + 1 }),
        icon: 'page',
    }));
});

const addRule = (pageId) => {
    const newRule = {
        _id: uniqid(),
        conditions: [{
            _id: uniqid(),
            field: null,
            operator: 'equals',
            value: null,
        }],
        destination: null,
    };

    const pages = props.pages.map(page =>
        page._id === pageId
            ? { ...page, rules: [...(page.rules || []), newRule] }
            : page
    );

    emit('update:pages', pages);

    nextTick(() => expand(newRule._id));
};

const removeRule = (ruleId, pageId) => {
    const pages = props.pages.map(page => {
        if (page._id !== pageId) return page;
        return {
            ...page,
            rules: page.rules.filter(rule => rule._id !== ruleId),
        };
    });

    emit('update:pages', pages);

    collapsed.value = collapsed.value.filter(id => id !== ruleId);
};

const updateRule = (ruleId, pageId, updatedRule) => {
    const pages = props.pages.map(page => {
        if (page._id !== pageId) return page;

        return {
            ...page,
            rules: page.rules.map(rule =>
                rule._id === ruleId ? { ...updatedRule, _id: ruleId } : rule
            ),
        };
    });

    emit('update:pages', pages);
};

watch(
    rules,
    (rules, oldRules) => {
        const oldIds = new Set((oldRules || []).map(rule => rule._id));

        rules.forEach(rule => {
            if (!oldIds.has(rule._id)) {
                collapsed.value.push(rule._id);
            }
        });
    },
    { immediate: true }
);
</script>

<template>
    <Panel>
        <PanelHeader>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                    <Heading :text="__('Page Logic')" />
                </div>
                <div v-if="rules.length > 0" class="flex items-center gap-2">
                    <Button
                        size="xs"
                        variant="ghost"
                        :icon="rulesView === 'collapsed' ? 'expand' : 'collapse'"
                        :aria-label="rulesView === 'collapsed' ? __('Expand all rules') : __('Collapse all rules')"
                        @click="rulesView === 'collapsed' ? expandAll() : collapseAll()"
                    />
                </div>
            </div>
        </PanelHeader>
        <Card>
            <LogicEmptyState
                v-if="rules.length === 0"
                :heading="__('No page logic yet')"
                :description="__('Send users to different pages based on their answers.')"
            >
                <AddLogicRuleButton
                    v-if="availablePages.length > 0"
                    flush
                    :items="availablePages"
                    :show-connector="false"
                    :label="__('Add Rule')"
                    :search-placeholder="__('Search Pages')"
                    @added="addRule"
                />
            </LogicEmptyState>

            <template v-else>
                <div class="relative space-y-6 mb-0" data-logic-list>
                    <PageLogicRule
                        v-for="rule in rules"
                        :id="rule._id"
                        :key="rule._id"
                        :rule="rule"
                        :page-id="rule._pageId"
                        :page-display="rule._pageDisplay"
                        :collapsed="collapsed.includes(rule._id)"
                        :suggestable-fields="getSuggestableFieldsForPage(rule._pageId)"
                        :page-destination-options="getPageDestinationOptions(rule._pageId)"
                        :fieldtypes
                        @collapsed="collapse(rule._id)"
                        @expanded="expand(rule._id)"
                        @removed="removeRule(rule._id, rule._pageId)"
                        @update:rule="updateRule(rule._id, rule._pageId, $event)"
                    />
                </div>
                <AddLogicRuleButton
                    v-if="availablePages.length > 0"
                    :items="availablePages"
                    :show-connector="rules.length > 0"
                    :label="__('Add Rule')"
                    :search-placeholder="__('Search Pages')"
                    @added="addRule"
                />
            </template>
        </Card>
    </Panel>
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
