<script setup>
import Container from './Container.vue';
import Tabs from './Tabs.vue';
import { Header, Button } from '@statamic/ui';
import uniqid from 'uniqid';
import { onMounted, onUnmounted, ref, useTemplateRef } from 'vue';
import { SavePipeline } from '@statamic/exports.js';
const { Pipeline, Request, BeforeSaveHooks, AfterSaveHooks } = SavePipeline;

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
        type: String,
        required: true,
    },
    submitMethod: {
        type: String,
        default: 'patch',
    },
    readOnly: {
        type: Boolean,
        default: false,
    },
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
            new BeforeSaveHooks('entry'),
            new Request(props.submitUrl, props.submitMethod),
            new AfterSaveHooks('entry'),
        ])
        .then((response) => {
            Statamic.$toast.success('Saved');

            if (response.data.redirect) {
                window.location = response.data.redirect;
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
        <Button v-if="!readOnly" variant="primary" text="Save" @click="save" :disabled="saving" />
    </Header>
    <Container
        ref="container"
        :name="containerName"
        :blueprint="blueprint"
        :meta="meta"
        :errors="errors"
        :read-only="readOnly"
        v-model="values"
    >
        <Tabs />
    </Container>
</template>
