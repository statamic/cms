<script setup lang="ts">
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';
import { Button, Header, Icon, SplitterGroup, SplitterPanel, SplitterResizeHandle, StatusIndicator, ToggleGroup, ToggleItem } from '@ui';
import FieldNumberingToggle from '@/components/forms/FieldNumberingToggle.vue';
import FieldLogic from '@/components/forms/logic/FieldLogic.vue';
import PageLogic from '@/components/forms/logic/PageLogic.vue';
import LogicTree, { TreeDensity } from '../../components/forms/logic/LogicTree.vue';
import LogicPanel from '../../components/forms/logic/LogicPanel.vue';
import Head from '@/pages/layout/Head.vue';
import { useFieldNumberingPreference } from '@/composables/forms/field-numbering';
import { computed, onMounted, onUnmounted, provide, ref, watch } from 'vue';
import { keys, preferences } from '@api';
import axios from 'axios';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

enum View {
    List = 'list',
    Tree = 'tree',
}

const props = defineProps({
    form: Object,
    pages: Array,
    fields: Array,
    action: String,
    fieldtypes: Array,
});

const pages = ref(props.pages);
const fields = ref(props.fields);
const saving = ref(false);
const saveBinding = ref(null);
const escBinding = ref(null);
const errors = ref({});
const view = ref<View>(preferences.get('forms.logic.view', View.List));
const treeDensity = ref<TreeDensity>(preferences.get('forms.logic.tree.density', TreeDensity.Compressed));
const selected = ref(null); // { type: SelectionType, id }

const { showFieldNumbers } = useFieldNumberingPreference();
const fieldNumbers = computed(() => {
    if (!showFieldNumbers.value) return new Map();

    let number = 0;
    const map = new Map();

    fields.value.forEach((field) => {
        if (field.hidden || field.category === 'information') return;
        map.set(field._id, ++number);
    });

    return map;
});
provide('fieldNumbers', fieldNumbers);

const suggestableFields = computed(() => {
    return fields.value
        .filter(field => !field.import)
        .filter(field => !['information', 'structure'].includes(field.category))
        .map(field => ({
            handle: field.handle,
            icon: field.icon,
            category: field.category,
            pageIndex: field.page_index,
            config: {
                type: field.fieldtype,
                display: field.display,
                options: field.options,
            },
        }));
});

const dirty = () => Statamic.$dirty.add('form-logic');
const clearDirtyState = () => Statamic.$dirty.remove('form-logic');

const save = () => {
    if (saving.value) return;

    errors.value = {};
    saving.value = true;

    axios.patch(props.action, {
        pages: pages.value.map(page => ({
            _id: page._id,
            rules: page.rules || [],
        })),
        fields: fields.value.map(field => ({
            _id: field._id,
            handle: field.handle,
            import: field.import ?? null,
            page_index: field.page_index,
            section_start: field.section_start ?? false,
            section_display: field.section_display ?? null,
            hidden: field.hidden,
            if: field.if,
            unless: field.unless,
            if_any: field.if_any,
            unless_any: field.unless_any,
            always_save: field.always_save || false,
        })),
    })
        .then((response) => {
            clearDirtyState();
            Statamic.$toast.success(__('Saved'));
        })
        .catch((e) => {
            if (e.response?.status === 422) {
                errors.value = e.response.data.errors;
                Statamic.$toast.error(e.response.data.message);
            } else {
                Statamic.$toast.error(__('Something went wrong'));
            }
        })
        .finally(() => saving.value = false);
};

watch(pages, dirty, { deep: true });
watch(fields, dirty, { deep: true });

watch(view, (view: View) => preferences.set('forms.logic.view', view));
watch(treeDensity, (density: TreeDensity) => preferences.set('forms.logic.tree.density', density));

onMounted(() => {
    saveBinding.value = keys.bindGlobal(['return', 'mod+s'], (e) => {
        e.preventDefault();
        save();
    });

    escBinding.value = keys.bindGlobal(['esc'], () => (selected.value = null));
});

onUnmounted(() => {
    clearDirtyState();
    saveBinding.value?.destroy();
    escBinding.value?.destroy();
});
</script>

<template>
    <Head :title="[__('Logic'), __(form.title), __('Forms')]" />

    <Teleport to="#form-layout-actions">
        <Button variant="primary" :aria-label="__('Save')" :disabled="saving" @click="save">
            <Icon name="save" class="sm:hidden" />
            <span class="hidden sm:inline">{{ __('Save') }}</span>
        </Button>
    </Teleport>

    <div class="mx-auto w-full max-w-5xl min-w-0 shrink-0">
        <Header class="mb-2 md:py-9">
            <template #title>
                <StatusIndicator status="published" />
                {{ __(form.title) }}
            </template>
            <template #actions>
                <div class="flex items-center gap-2.5">
                    <FieldNumberingToggle />
                    <ToggleGroup v-if="view === View.Tree" v-model="treeDensity" size="xs">
                        <ToggleItem
                            :value="TreeDensity.Expanded"
                            icon="expand"
                            :aria-label="__('Expanded view')"
                            v-tooltip="__('Expanded view')"
                        />
                        <ToggleItem
                            :value="TreeDensity.Compressed"
                            icon="collapse"
                            :aria-label="__('Collapsed view')"
                            v-tooltip="__('Collapsed view')"
                        />
                    </ToggleGroup>
                    <ToggleGroup v-model="view" size="sm">
                        <ToggleItem :value="View.List" icon="layout-list" :label="__('List')" />
                        <ToggleItem :value="View.Tree" icon="logic-tree" :label="__('Tree')" />
                    </ToggleGroup>
                </div>
            </template>
        </Header>

        <template v-if="view === View.List">
            <PageLogic
                v-if="pages.length > 1"
                class="mb-6"
                v-model:pages="pages"
                :suggestable-fields
                :fieldtypes
            />

            <FieldLogic
                v-model:fields="fields"
                :suggestable-fields
                :fieldtypes
            />
        </template>
    </div>

    <div v-if="view === View.Tree" class="st-full-bleed-content flex flex-col flex-1 min-h-0">
        <div class="flex-1 min-h-0 overflow-hidden pb-2!">
            <SplitterGroup direction="vertical">
                <SplitterPanel>
                    <div class="h-full overflow-y-auto">
                        <LogicTree
                            :pages
                            v-model:fields="fields"
                            :density="treeDensity"
                            :selected
                            @select="selected = $event"
                        />
                    </div>
                </SplitterPanel>

                <template v-if="selected">
                    <SplitterResizeHandle class="mx-auto my-1.5 h-1.5 w-16 shrink-0 rounded-full bg-gray-300 transition-colors hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600" />

                    <SplitterPanel
                        :default-size="35"
                        :min-size="20"
                        collapsible
                        class="rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div class="h-full overflow-y-auto">
                            <LogicPanel
                                :selection="selected"
                                v-model:fields="fields"
                                v-model:pages="pages"
                                :suggestable-fields="suggestableFields"
                                :fieldtypes
                            />
                        </div>
                    </SplitterPanel>
                </template>
            </SplitterGroup>
        </div>
    </div>
</template>
