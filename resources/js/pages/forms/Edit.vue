<script setup>
import { Header, Button, Icon } from '@ui';
import { onMounted, onUnmounted, ref, useTemplateRef } from 'vue';
import { Pipeline, Request } from '@ui/Publish/SavePipeline.js';
import { router } from '@inertiajs/vue3';
import { PublishContainer as Container, PublishTabs as Tabs } from '@/components/ui';
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';
import Head from '@/pages/layout/Head.vue';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

const props = defineProps({
    blueprint: {
        type: Object,
        required: true,
    },
    initialValues: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    initialMeta: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    action: {
        type: String,
        required: true,
    }
});

const container = useTemplateRef('container');
const values = ref(props.initialValues);
const meta = ref(props.initialMeta);
const errors = ref({});
const saving = ref(false);

function save() {
    new Pipeline()
        .provide({ container, errors, saving })
        .through([
            new Request(props.action, 'PATCH'),
        ])
        .then((response) => {
            Statamic.$toast.success(__('Saved'));

            if (response.data.redirect) {
                router.get(response.data.redirect);
            }
        });
}

let saveKeyBinding;

onMounted(() => {
    saveKeyBinding = Statamic.$keys.bindGlobal(['mod+s'], (e) => {
        e.preventDefault();
        save();
    });
});

onUnmounted(() => saveKeyBinding.destroy());
</script>

<template>
    <Head :title="[__('Configure'), __(initialValues.title), __('Forms')]" />

    <Teleport to="#form-layout-actions">
        <Button variant="primary" @click="save" :disabled="saving" :aria-label="__('Save')">
            <Icon name="save" class="sm:hidden" />
            <span class="hidden sm:inline">{{ __('Save') }}</span>
        </Button>
    </Teleport>

    <Header :title="__('Configure Form')" icon="cog">
        <Button variant="primary" :text="__('Save')" @click="save" :disabled="saving" />
    </Header>
    <Container
        ref="container"
        :blueprint
        :meta
        :errors
        as-config
        v-model="values"
    >
        <Tabs />
    </Container>
</template>
