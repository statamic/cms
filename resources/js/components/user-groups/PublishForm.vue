<script setup>
import { SavePipeline } from 'statamic';
import { Header, Button, Dropdown, DropdownMenu, DropdownItem, PublishContainer } from '@statamic/ui';
import { ref, useTemplateRef } from 'vue';
const { Pipeline, Request } = SavePipeline;

let saving = ref(false);
let errors = ref({});
let container = useTemplateRef('container');

const props = defineProps({
    publishContainer: String,
    initialFieldset: Object,
    initialValues: Object,
    initialMeta: Object,
    initialReference: String,
    initialTitle: String,
    actions: Object,
    method: String,
    canEditBlueprint: Boolean,
    isCreating: Boolean,
});

const fieldset = ref(props.initialFieldset);
const values = ref(props.initialValues);
const meta = ref(props.initialMeta);
const title = ref(props.initialTitle);

function save() {
    new Pipeline()
        .provide({ container, errors, saving })
        .through([new Request(props.actions.save, props.method)])
        .then((response) => {
            if (props.isCreating) window.location = response.data.redirect;
            Statamic.$toast.success('Saved');
            title.value = response.data.title;
        });
}
</script>

<template>
    <div class="max-w-5xl mx-auto">
        <Header :title="__(title)" icon="groups">
            <Dropdown v-if="canEditBlueprint" class="me-2">
                <template #trigger>
                    <Button icon="ui/dots" variant="ghost" />
                </template>
                <DropdownMenu>
                    <DropdownItem :text="__('Edit Blueprint')" icon="blueprint-edit" :href="actions.editBlueprint" />
                </DropdownMenu>
            </Dropdown>

            <Button variant="primary" @click.prevent="save" :text="__('Save')" />

            <slot name="action-buttons-right" />
        </Header>

        <PublishContainer
            v-if="fieldset"
            ref="container"
            :name="publishContainer"
            :blueprint="fieldset"
            v-model="values"
            :reference="initialReference"
            :meta="meta"
            :errors="errors"
            as-config
        />
    </div>
</template>
