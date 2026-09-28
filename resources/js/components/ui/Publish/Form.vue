<script setup>
import Container from './Container.vue';
import Tabs from './Tabs.vue';
import { Header, Button } from '@ui';
import { nanoid as uniqid } from 'nanoid';
import { onMounted, onUnmounted, ref, useTemplateRef } from 'vue';
import { Pipeline, Request, BeforeSaveHooks, AfterSaveHooks } from '@ui/Publish/SavePipeline.js';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    icon: {
        type: String,
    },
    title: {
        type: String,
        default: () => uniqid(),
    },
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
    submitUrl: {
        type: [String, null],
        default: null,
    },
    submitMethod: {
        type: String,
        default: 'patch',
    },
    readOnly: {
        type: Boolean,
        default: false,
    },
    asConfig: {
        type: Boolean,
        default: false,
    },
    rememberTab: {
        type: Boolean,
        default: true,
    }
});

const containerName = Statamic.$slug.separatedBy('_').create(props.title);
const container = useTemplateRef('container');
const values = ref(props.initialValues);
const meta = ref(props.initialMeta);
const errors = ref({});
const saving = ref(false);

function save() {
    new Pipeline()
        .provide({ container, errors, saving })
        .through([
            new Request(props.submitUrl, props.submitMethod),
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
    <Header :title="title" :icon="icon">
        <Button v-if="!readOnly" variant="primary" :text="__('Save')" @click="save" :disabled="saving" />
    </Header>
    <Container
        ref="container"
        :name="containerName"
        :blueprint="blueprint"
        :meta="meta"
        :errors="errors"
        :read-only="readOnly"
        :as-config="asConfig"
        :remember-tab="rememberTab"
        v-model="values"
    >
        <Tabs />
    </Container>
</template>
