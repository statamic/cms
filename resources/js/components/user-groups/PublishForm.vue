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
        >
            <PublishTabs />
        </PublishContainer>
    </div>
</template>

<script>
import HasHiddenFields from '../publish/HasHiddenFields';
import clone from '@statamic/util/clone.js';
import { Header, Button, Dropdown, DropdownMenu, DropdownItem, PublishContainer, PublishTabs } from '@statamic/ui';

export default {
    mixins: [HasHiddenFields],

    components: {
        Header,
        Button,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        PublishContainer,
        PublishTabs
    },

    props: {
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
    },

    data() {
        return {
            fieldset: clone(this.initialFieldset),
            values: clone(this.initialValues),
            meta: clone(this.initialMeta),
            error: null,
            errors: {},
            title: this.initialTitle,
        };
    },

    computed: {
        store() {
            return this.$refs.container.store;
        },

        hasErrors() {
            return this.error || Object.keys(this.errors).length;
        },
    },

    methods: {
        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.clearErrors();

            this.$axios[this.method](this.actions.save, this.visibleValues)
                .then((response) => {
                    this.title = response.data.title;
                    this.$refs.container.saved();
                    if (this.isCreating) window.location = response.data.redirect;
                    this.$toast.success(__('Saved'));
                    this.$nextTick(() => this.$emit('saved', response));
                })
                .catch((e) => {
                    if (e.response && e.response.status === 422) {
                        const { message, errors } = e.response.data;
                        this.error = message;
                        this.errors = errors;
                        this.$toast.error(message);
                    } else {
                        this.$toast.error(__('Something went wrong'));
                    }
                });
        },
    },

    mounted() {
        this.$keys.bindGlobal(['mod+s'], (e) => {
            e.preventDefault();
            this.save();
        });
    },
};
</script>
