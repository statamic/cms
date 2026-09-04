<script setup>
import { onMounted, onUnmounted, provide, reactive, ref, watch } from 'vue';
import axios from 'axios';
import { keys } from '@api';
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from '../Layout.vue';
import Head from '@/pages/layout/Head.vue';
import { Badge, Button, Card, Header, Heading, Icon, Panel, PanelHeader } from '@ui';
import FormStatusIndicator from '@/components/forms/FormStatusIndicator.vue';
import { Link } from '@inertiajs/vue3';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

const props = defineProps({
    form: Object,
    connection: Object,
    component: Object,
    value: [Array, Object],
    action: String,
    isConfigured: Boolean,
    suggestableFields: Array,
});

const connectionRowsApi = reactive({ expandAll: null, collapseAll: null, allCollapsed: false, count: 0 });
provide('connectionRowsApi', connectionRowsApi);

const errors = ref({});
const saving = ref(false);
const saveBinding = ref(null);
const value = ref(props.value);

const save = () => {
    if (saving.value) return;

    errors.value = {};
    saving.value = true;

    axios.patch(props.action, value.value)
        .then(() => {
            Statamic.$dirty.remove('connection');
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
        .finally(() => (saving.value = false));
};

watch(value, () => Statamic.$dirty.add('connection'), { deep: true });

onMounted(() => {
    if (!props.isConfigured) return;

    saveBinding.value = keys.bindGlobal(['mod+s'], (e) => {
        e.preventDefault();
        save();
    });
});

onUnmounted(() => {
    saveBinding.value?.destroy();
    Statamic.$dirty.remove('connection');
});
</script>

<template>
    <Head :title="[__(connection.title), __('Connect'), __(form.title), __('Forms')]" />

    <Teleport v-if="isConfigured" to="#form-layout-actions">
        <Button variant="primary" :aria-label="__('Save')" :disabled="saving" @click="save">
            <Icon name="save" class="sm:hidden" />
            <span class="hidden sm:inline">{{ __('Save') }}</span>
        </Button>
    </Teleport>

    <div class="mx-auto max-w-5xl">
        <Header class="mb-2">
            <template #title>
                <FormStatusIndicator :status="form.status" />
                {{ __(form.title) }}
            </template>
        </Header>

        <Panel>
            <PanelHeader>
                <div class="flex items-center justify-between gap-3">
                <Heading>
                    <Link
                        :href="cp_url(`forms/${form.handle}/connect`)"
                        class="inline-flex items-center gap-2 text-inherit my-0! cursor-pointer hover:opacity-80"
                    >
                        <Icon name="connection" class="size-4! opacity-60! text-gray-925 dark:text-white" aria-hidden="true" />
                        {{ __('Connect') }}
                    </Link>
                    <Icon name="chevron-right" class="size-3.5 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                    <span class="relative inline-flex items-center gap-1.5">
                        <span
                            v-if="connection.icon"
                            :class="[connection.iconColor || 'text-gray-700 dark:text-gray-300', 'size-4 [&_svg]:size-4']"
                            aria-hidden="true"
                            v-html="connection.icon"
                        />
                        <span>{{ __(connection.title) }}</span>
                        <Badge v-if="Array.isArray(value)" pill class="absolute start-full top-1/2 ms-1.5 size-6 -translate-y-1/2">
                            {{ value.length }}
                        </Badge>
                    </span>
                </Heading>
                <div v-if="connectionRowsApi.count > 1" class="flex items-center gap-2">
                    <Button
                        size="xs"
                        variant="ghost"
                        :icon="connectionRowsApi.allCollapsed ? 'expand' : 'collapse'"
                        :aria-label="connectionRowsApi.allCollapsed ? __('Expand all') : __('Collapse all')"
                        @click="connectionRowsApi.allCollapsed ? connectionRowsApi.expandAll() : connectionRowsApi.collapseAll()"
                    />
                </div>
                </div>
            </PanelHeader>
            <Card>
                <component
                    :is="component.name"
                    :form
                    :errors
                    v-model="value"
                    v-bind="component.props"
                />
            </Card>
        </Panel>
    </div>
</template>
