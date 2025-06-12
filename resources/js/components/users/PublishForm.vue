<template>
    <div>
        <header class="mb-6">
            <div class="flex items-center">
                <h1 class="flex-1" v-text="title" />

                <ItemActions
                    v-if="canEditBlueprint || hasItemActions"
                    :item="values.id"
                    :url="itemActionUrl"
                    :actions="itemActions"
                    :is-dirty="isDirty"
                    @started="actionStarted"
                    @completed="actionCompleted"
                >
                    <Dropdown class="me-4">
                        <template #trigger>
                            <Button icon="ui/dots" variant="ghost" />
                        </template>
                        <DropdownMenu>
                            <DropdownItem :text="__('Edit Blueprint')" icon="blueprint-edit" v-if="canEditBlueprint" :href="actions.editBlueprint" />
                            <DropdownSeparator v-if="canEditBlueprint && itemActions.length" />
                            <DropdownItem
                                v-for="action in itemActions"
                                :key="action.handle"
                                :text="__(action.title)"
                                :icon="action.icon"
                                :variant="action.dangerous ? 'destructive' : 'default'"
                                @click="action.run"
                            />
                        </DropdownMenu>
                    </Dropdown>
                </ItemActions>

                <TwoFactor v-if="twoFactor" v-bind="twoFactor" trigger-class="ltr:mr-4 rtl:ml-4" />

                <change-password
                    v-if="canEditPassword"
                    :save-url="actions.password"
                    :requires-current-password="requiresCurrentPassword"
                    trigger-class="ltr:mr-4 rtl:ml-4"
                />

                <button class="btn-primary" @click.prevent="save" v-text="__('Save')" />

                <slot name="action-buttons-right" />
            </div>
        </header>

        <publish-container
            v-if="fieldset"
            ref="container"
            :name="publishContainer"
            :blueprint="fieldset"
            :values="values"
            :reference="initialReference"
            :meta="meta"
            :errors="errors"
            @updated="values = $event"
            v-slot="{ container, setFieldValue, setFieldMeta }"
        >
            <div>
                <publish-tabs
                    :enable-sidebar="false"
                    @updated="setFieldValue"
                    @meta-updated="setFieldMeta"
                    @focus="container.$emit('focus', $event)"
                    @blur="container.$emit('blur', $event)"
                ></publish-tabs>
            </div>
        </publish-container>
    </div>
</template>

<script>
import ChangePassword from './ChangePassword.vue';
import HasHiddenFields from '../publish/HasHiddenFields';
import HasActions from '../publish/HasActions';
import TwoFactor from '@statamic/components/two-factor/TwoFactor.vue';
import clone from '@statamic/util/clone.js';
import { Button, Dropdown, DropdownMenu, DropdownItem, DropdownSeparator } from '@statamic/ui';
import ItemActions from '@statamic/components/actions/ItemActions.vue';

export default {
    mixins: [HasHiddenFields, HasActions],

    components: {
        ItemActions,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownSeparator,
        Button,
        ChangePassword,
        TwoFactor,
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
        canEditPassword: Boolean,
        canEditBlueprint: Boolean,
        requiresCurrentPassword: Boolean,
        twoFactor: Object,
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

        isDirty() {
            return this.$dirty.has(this.publishContainer);
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
                    this.values = this.resetValuesFromResponse(response.data.data.values);
                    if (!response.data.saved) {
                        return this.$toast.error(`Couldn't save user`);
                    }
                    if (!this.isCreating) this.$toast.success(__('Saved'));
                    this.$refs.container.saved();
                    this.$nextTick(() => this.$emit('saved', response));
                })
                .catch((e) => {
                    if (e.response && e.response.status === 422) {
                        const { message, errors } = e.response.data;
                        this.error = message;
                        this.errors = errors;
                        this.$toast.error(message);
                        this.$reveal.invalid();
                    } else {
                        this.$toast.error(__('Something went wrong'));
                    }
                });
        },

        afterActionSuccessfullyCompleted(response) {
            if (response.data) {
                this.title = response.data.title;
                this.values = this.resetValuesFromResponse(response.data.values);
            }
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
