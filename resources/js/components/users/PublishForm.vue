<template>
    <div>
        <header class="mb-6">
            <breadcrumb :url="cp_url('users')" :title="__('Users')" />
            <div class="flex items-center">
                <h1 class="flex-1" v-text="title" />
                <dropdown-list class="ltr:mr-4 rtl:ml-4" v-if="canEditBlueprint || hasItemActions">
                    <dropdown-item
                        :text="__('Edit Blueprint')"
                        v-if="canEditBlueprint"
                        :redirect="actions.editBlueprint"
                    />
                    <li class="divider" />
                    <data-list-inline-actions
                        v-if="hasItemActions"
                        :item="values.id"
                        :url="itemActionUrl"
                        :actions="itemActions"
                        :is-dirty="isDirty"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />
                </dropdown-list>

                <change-password
                    v-if="canEditPassword"
                    :save-url="actions.password"
                    :requires-current-password="requiresCurrentPassword"
                    class="ltr:mr-4 rtl:ml-4"
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

export default {
    mixins: [HasHiddenFields, HasActions],

    components: {
        ChangePassword,
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
    },

    data() {
        return {
            fieldset: _.clone(this.initialFieldset),
            values: _.clone(this.initialValues),
            meta: _.clone(this.initialMeta),
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
