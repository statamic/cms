<script setup>
import Container from './Container.vue';
import Tabs from './Tabs.vue';
import { Header, Button } from '@statamic/ui';
import uniqid from 'uniqid';
import { ref, useTemplateRef } from 'vue';
import { SavePipeline } from '@statamic/exports.js';
const { Pipeline, Request, BeforeSaveHooks, AfterSaveHooks } = SavePipeline;

const props = defineProps({
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
    saveUrl: {
        type: String,
        required: true,
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
            new Request(props.saveUrl, 'patch', { values: values.value }),
            new AfterSaveHooks('entry'),
        ])
        .then((response) => {
            Statamic.$toast.success('Saved');
        });
}
</script>

<template>
    <Header :title="title">
        <Button variant="primary" text="Save" @click="save" :disabled="saving" />
    </Header>
    <Container
        ref="container"
        :name="containerName"
        :blueprint="blueprint"
        :values="values"
        :meta="meta"
        :errors="errors"
        @updated="values = $event"
    >
        <Tabs />
    </Container>
</template>
