<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import axios from 'axios';
import { keys } from '@api';
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from '../Layout.vue';
import Head from '@/pages/layout/Head.vue';
import { Alert, Button, Card, Header, Heading, Icon, Panel, PanelHeader } from '@ui';
import FormStatusIndicator from '@/components/forms/FormStatusIndicator.vue';
import ConnectionEditor from '@/components/forms/connections/ConnectionEditor.vue';
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
    uniqueInstancesEnabled: Boolean,
});

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
        <Header>
            <template #title>
                <FormStatusIndicator :status="form.status" />
                {{ __(form.title) }}
            </template>
        </Header>

        <Alert
            v-if="uniqueInstancesEnabled"
            variant="warning"
            class="mb-6"
            :text="__('messages.form_connect_unique_instances_warning')"
        />

        <Panel>
            <PanelHeader>
                <Heading>
                    <Link
                        :href="cp_url(`forms/${form.handle}/connect`)"
                        class="inline-flex items-center gap-2 text-inherit my-0! cursor-pointer hover:opacity-80"
                    >
                        <Icon name="connection" class="size-4! opacity-60! text-gray-925 dark:text-white" aria-hidden="true" />
                        {{ __('Connect') }}
                    </Link>
                    <Icon name="chevron-right" class="size-3.5 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                    <span class="inline-flex items-center gap-1.5">
                        <span
                            v-if="connection.icon"
                            class="size-4 text-gray-700 dark:text-gray-300 [&_svg]:size-4"
                            aria-hidden="true"
                            v-html="connection.icon"
                        />
                        <span>{{ __(connection.title) }}</span>
                    </span>
                </Heading>
            </PanelHeader>
            <Card>
                <ConnectionEditor :form :component :errors v-model="value" />
            </Card>
        </Panel>
    </div>
</template>
