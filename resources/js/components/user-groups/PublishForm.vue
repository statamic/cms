<template>

    <div>

        <header class="mb-3">
            <breadcrumb :url="cp_url('user-groups')" :title="__('User Groups')" />
            <div class="flex items-center">
                <h1 class="flex-1" v-text="__(title)" />
                    <dropdown-list class="rtl:ml-2 ltr:mr-2" v-if="canEditBlueprint">
                        <dropdown-item :text="__('Edit Blueprint')" :redirect="actions.editBlueprint" />
                    </dropdown-list>

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
import HasHiddenFields from '../publish/HasHiddenFields';

export default {

    mixins: [
        HasHiddenFields,
    ],

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
                this.$refs.container.saved();
                if (this.isCreating) window.location = response.data.redirect;
                this.$toast.success(__('Saved'));
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
