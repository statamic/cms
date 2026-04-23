<script setup>
import Layout from '@/pages/layout/Layout.vue';
import FormsLayout from './Layout.vue';
import { Button, Card, Header, Heading, Icon, Panel, PanelHeader, StatusIndicator, ToggleGroup, ToggleItem, publishContextKey } from '@ui';
import AddSetButton from '@/components/fieldtypes/replicator/AddSetButton.vue';
import ReplicatorSet from '@/components/fieldtypes/replicator/Set.vue';
import { SortableList } from '@/components/sortable/Sortable';
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
const sortableHandleClass = 'logic-rule-handle';
const loadingSet = ref(null);

const logicBlocks = ref([
    { _id: 'heard_about_us', type: 'heard_about_us', enabled: true, summary: __('If answer is "Friend", then continue to fan duration question.') },
    { _id: 'fan_length', type: 'fan_length', enabled: true, summary: __('If answer contains "ages", then go to email notifications.') },
    { _id: 'favorite_album', type: 'favorite_album', enabled: true, summary: __('If favorite album equals Days of Thunder, then go to second favorite album.') },
    { _id: 'second_favorite_album', type: 'second_favorite_album', enabled: false, summary: __('No conditions yet.') },
    { _id: 'age', type: 'age', enabled: true, summary: __('If age is greater than 21, then go to free drink voucher.') },
]);

const collapsed = ref(['second_favorite_album', 'age']);
const previews = ref({});
const meta = ref({});

const setConfigs = [
    { handle: 'heard_about_us', display: __('How did you hear about us?'), fields: [] },
    { handle: 'fan_length', display: __('How long have you been a fan?'), fields: [] },
    { handle: 'favorite_album', display: __('Which album was your favorite?'), fields: [] },
    { handle: 'second_favorite_album', display: __('Which album was your second favorite?'), fields: [] },
    { handle: 'age', display: __('How old are you?'), fields: [] },
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
    loadingSet.value = handle;
    const config = setConfigByHandle.value[handle];

    logicBlocks.value.splice(index, 0, {
        _id: uniqid(),
        type: handle,
        enabled: true,
        summary: config?.display
            ? __('If :rule has matching conditions, continue to the configured destination.', { rule: config.display })
            : __('No conditions yet.'),
    });

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

function duplicateSet(id) {
    const index = logicBlocks.value.findIndex(block => block._id === id);
    if (index === -1) return;

    const original = logicBlocks.value[index];
    logicBlocks.value.splice(index + 1, 0, {
        ...JSON.parse(JSON.stringify(original)),
        _id: uniqid(),
    });
}

function removeSet(id, index) {
    collapsed.value = collapsed.value.filter(setId => setId !== id);
    logicBlocks.value.splice(index, 1);
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
                <ToggleGroup v-model="logicView" size="xs">
                    <ToggleItem value="list" icon="layout-list" :label="__('List')" />
                    <ToggleItem value="tree" icon="logic-tree" :label="__('Tree')" />
                </ToggleGroup>
            </template>
        </Header>

        <Panel>
            <PanelHeader>
                <Heading :text="__('Form Logic')" />
            </PanelHeader>
            <Card>
                <SortableList
                    v-model="logicBlocks"
                    :item-class="sortableItemClass"
                    :handle-class="sortableHandleClass"
                    :vertical="true"
                    append-to="body"
                    constrain-dimensions
                >
                    <div class="relative">
                        <ReplicatorSet
                            v-for="(block, index) in logicBlocks"
                            :id="block._id"
                            :key="block._id"
                            :index
                            :field-path="fieldPath"
                            :meta-path="metaPath"
                            :values="block"
                            :config="setConfigByHandle[block.type]"
                            :sortable-item-class="sortableItemClass"
                            :sortable-handle-class="sortableHandleClass"
                            :collapsed="collapsed.includes(block._id)"
                            :enabled="block.enabled"
                            :read-only="false"
                            :can-add-set="true"
                            :has-error="false"
                            :show-field-previews="true"
                            @collapsed="collapseSet(block._id)"
                            @expanded="expandSet(block._id)"
                            @duplicated="duplicateSet(block._id)"
                            @removed="removeSet(block._id, index)"
                        >
                            <template #picker>
                                <AddSetButton
                                    variant="between"
                                    :groups="groupConfigs"
                                    :sets="setConfigs"
                                    :index
                                    :enabled="true"
                                    :is-first="index === 0"
                                    :show-connector="true"
                                    :loading-set="loadingSet"
                                    :search-placeholder="__('Search Fields')"
                                    @added="addSet"
                                />
                            </template>
                        </ReplicatorSet>
                    </div>
                </SortableList>
                <AddSetButton
                    :groups="groupConfigs"
                    :sets="setConfigs"
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
