<script setup>
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';
import { Button, Header, Icon, StatusIndicator } from '@ui';
import FieldLogic from '@/components/forms/logic/FieldLogic.vue';
import PageLogic from '@/components/forms/logic/PageLogic.vue';
import Head from '@/pages/layout/Head.vue';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { keys } from '@api';
import axios from 'axios';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

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
const errors = ref({});

const suggestableFields = computed(() => {
    return fields.value
        .filter(field => field.category !== 'information')
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

onMounted(() => {
    saveBinding.value = keys.bindGlobal(['return', 'mod+s'], (e) => {
        e.preventDefault();
        save();
    });
});

onUnmounted(() => {
    clearDirtyState();
    saveBinding.value?.destroy();
});
</script>

<template>
    <Head :title="[__('Logic'), form.title, __('Forms')]" />

    <Teleport to="#form-layout-actions">
        <Button variant="primary" :aria-label="__('Save')" :disabled="saving" @click="save">
            <Icon name="save" class="sm:hidden" />
            <span class="hidden sm:inline">{{ __('Save') }}</span>
        </Button>
    </Teleport>

    <div class="py-4 mx-auto max-w-5xl">
        <Header class="mb-2">
            <template #title>
                <StatusIndicator status="published" />
                {{ form.title }}
            </template>
        </Header>

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
    </div>
</template>
