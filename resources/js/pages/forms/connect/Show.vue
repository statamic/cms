<script setup>
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from '../Layout.vue';
import Head from '@/pages/layout/Head.vue';
import { Card, Header, Heading, Icon, Panel, PanelHeader } from '@ui';
import FormStatusIndicator from '@/components/forms/FormStatusIndicator.vue';
import { Link } from '@inertiajs/vue3';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

const props = defineProps({
    form: Object,
    connection: Object,
    component: Object,
    config: Object,
    suggestableFields: Array,
});
</script>

<template>
    <Head :title="[__(connection.title), __('Connect'), __(form.title), __('Forms')]" />

    <div class="mx-auto max-w-5xl">
        <Header class="mb-2">
            <template #title>
                <FormStatusIndicator :status="form.status" />
                {{ __(form.title) }}
            </template>
        </Header>

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
                <component :is="component.name" :form :config v-bind="component.props" />
            </Card>
        </Panel>
    </div>
</template>
