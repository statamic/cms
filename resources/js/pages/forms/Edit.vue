<script setup>
import { Header, Button } from '@ui';
import { onMounted, onUnmounted, ref, useTemplateRef } from 'vue';
import { Pipeline, Request } from '@ui/Publish/SavePipeline.js';
import { router } from '@inertiajs/vue3';
import { PublishContainer as Container, PublishTabs as Tabs } from '@/components/ui';
import FormLayout from './Layout.vue';

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
    <FormLayout>
        <template #actions>
            <Button class="ms-2" :text="__('Save')" variant="primary" @click="save" :disabled="saving" />
        </template>

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
    </FormLayout>
</template>
