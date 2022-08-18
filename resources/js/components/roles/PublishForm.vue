<template>

        <div>
            <header class="mb-3">
                <breadcrumb :url="breadcrumbUrl" :title="__('Roles & Permissions')" />
                <div class="flex items-center justify-between">
                    <h1 v-text="initialTitle || __('Create Role')" />

                     <dropdown-list v-if="canEditBlueprint">
                         <dropdown-item :text="__('Edit Blueprint')" :redirect="actions.editBlueprint" />
                     </dropdown-list>

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

            <div v-if="!isSuper">
                <div class="mt-3 content" v-for="group in permissions" :key="group.handle">
                    <h2 class="mt-5 text-base mb-1">{{ group.label }}</h2>
                    <role-permission-tree class="card p-0" :depth="1" :initial-permissions="group.permissions" />
                </div>
            </div>

            <div class="py-2 mt-3 border-t flex justify-between">
                <a :href="indexUrl" class="btn" v-text="__('Cancel') "/>
                <button type="submit" class="btn-primary" @click="save">{{ __('Save') }}</button>
            </div>

        </div>
</template>


<script>
import HasHiddenFields from '../data-list/HasHiddenFields';

const checked = function (permissions) {
    return permissions.reduce((carry, permission) => {
        if (! permission.checked) return carry;
        return [...carry, permission.value, ...checked(permission.children)];
    }, []);
};

export default {

    mixins: [
         HasHiddenFields,
    ],

    props: {
        publishContainer: String,
        actions: Object,
        canEditBlueprint: Boolean,
        initialFieldset: Object,
        initialValues: Object,
        initialMeta: Object,
        initialReference: String,
        initialTitle: String,
        initialHandle: String,
        initialPermissions: Array,
        initialSuper: Boolean,
        method: String,
        breadcrumbUrl: String,
        indexUrl: String
    },

    data() {
        return {
            fieldset: _.clone(this.initialFieldset),
            values: _.clone(this.initialValues),
            meta: _.clone(this.initialMeta),
            error: null,
            errors: {},
            title: this.initialTitle,
            handle: this.initialHandle,
            permissions: this.initialPermissions,
            isSuper: this.initialSuper
        }
    },

    watch: {
        'values.super': function(checked) {
            this.isSuper = checked;
        }
    },

    computed: {

        hasErrors() {
            return this.error || Object.keys(this.errors).length;
        },

        payload() {
            return _.assign(this.visibleValues, {
                permissions: this.checkedPermissions
            })
        },

        checkedPermissions() {
            return this.permissions.reduce((carry, group) => {
                return [...carry, ...checked(group.permissions)];
            }, []);
        }

    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.clearErrors();

            this.$axios[this.method](this.actions.save, this.payload).then(response => {
                window.location = response.data.redirect;
            }).catch(e => {
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$toast.error(message);
                } else {
                    this.$toast.error(__('Unable to save role'));
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
