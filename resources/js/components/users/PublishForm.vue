<template>

    <div>

        <header class="mb-3">
            <breadcrumb :url="cp_url('users')" :title="__('Users')" />
            <div class="flex items-center">
                <h1 class="flex-1" v-text="title" />
                    <dropdown-list class="mr-2" v-if="canEditBlueprint">
                        <dropdown-item :text="__('Edit Blueprint')" :redirect="actions.editBlueprint" />
                    </dropdown-list>

                    <change-password
                        v-if="canEditPassword"
                        :save-url="actions.password"
                        class="mr-2"
                    />

                    <button
                        class="btn-primary"
                        @click.prevent="save"
                        v-text="__('Save')" />

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
        >
            <div slot-scope="{ container, setFieldValue, setFieldMeta }">
                <publish-sections
                    :enable-sidebar="false"
                    :can-toggle-labels="true"
                    @updated="setFieldValue"
                    @meta-updated="setFieldMeta"
                    @focus="container.$emit('focus', $event)"
                    @blur="container.$emit('blur', $event)"
                ></publish-sections>
            </div>
        </publish-container>

    </div>
</template>


<script>
import ChangePassword from './ChangePassword.vue';
import HasHiddenFields from '../publish/HasHiddenFields';

export default {

    mixins: [
        HasHiddenFields,
    ],

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
        canEditBlueprint: Boolean
    },

    data() {
        return {
            fieldset: _.clone(this.initialFieldset),
            values: _.clone(this.initialValues),
            meta: _.clone(this.initialMeta),
            error: null,
            errors: {},
            title: this.initialTitle,
        }
    },

    computed: {

        hasErrors() {
            return this.error || Object.keys(this.errors).length;
        }

    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.clearErrors();

            this.$axios[this.method](this.actions.save, this.visibleValues).then(response => {
                this.title = response.data.title;
                if (!this.isCreating) this.$toast.success(__('Saved'));
                this.$refs.container.saved();
                this.$nextTick(() => this.$emit('saved', response));
            }).catch(e => {
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$toast.error(message);
                } else {
                    this.$toast.error(__('Something went wrong'));
                }
            });
        }

    },

    mounted() {
        this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.save();
        });
    }

}
</script>
