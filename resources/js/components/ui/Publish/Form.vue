<script setup>
import Container from './Container.vue';
import Tabs from './Tabs.vue';
import { Header, Button } from '@statamic/ui';
import uniqid from 'uniqid';
import { ref } from 'vue';
import axios from 'axios';

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
const values = ref(props.initialValues);
const meta = ref(props.initialMeta);
const errors = ref({});

function save() {
    axios
        .patch(props.saveUrl, { values: values.value })
        .then((response) => console.log('Values saved successfully:', response.data))
        .catch((e) => {
            if (e.response && e.response.status === 422) {
                const { errors: messages, message } = e.response.data;
                errors.value = messages;
                Statamic.$toast.error(message);
            }
        });
}
</script>

<template>
    <Header :title="title">
        <Button variant="primary" text="Save" @click="save" />
    </Header>
    <Container
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
