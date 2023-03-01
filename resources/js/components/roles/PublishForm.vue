<template>

        <div>
            <header class="mb-3">
                <breadcrumb :url="breadcrumbUrl" :title="__('Roles & Permissions')" />
                <h1 v-text="initialTitle || __('Create Role')" />
            </header>

            <publish-fields-container class="card p-0 mb-3 configure-section">

                <form-group
                    handle="title"
                    class="border-b"
                    :display="__('Title')"
                    :errors="errors.title"
                    :instructions="__('messages.role_title_instructions')"
                    v-model="title"
                    :focus="true"
                />

                <form-group
                    class="border-b"
                    fieldtype="slug"
                    handle="handle"
                    :display="__('Handle')"
                    :instructions="__('messages.role_handle_instructions')"
                    :errors="errors.title"
                    v-model="handle"
                />

                <div class="text-xs text-red p-3 pt-0" v-if="initialHandle && handle != initialHandle">
                    {{ __('messages.role_change_handle_warning') }}
                </div>

                <form-group
                    v-if="canAssignSuper"
                    class="toggle-fieldtype"
                    fieldtype="toggle"
                    handle="super"
                    :display="__('permissions.super')"
                    :instructions="__('permissions.super_desc')"
                    v-model="isSuper"
                />

            </publish-fields-container>

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
const checked = function (permissions) {
    return permissions.reduce((carry, permission) => {
        if (! permission.checked) return carry;
        return [...carry, permission.value, ...checked(permission.children)];
    }, []);
};

export default {

    props: {
        initialTitle: String,
        initialHandle: String,
        initialPermissions: Array,
        initialSuper: Boolean,
        canAssignSuper: Boolean,
        action: String,
        method: String,
        breadcrumbUrl: String,
        indexUrl: String
    },

    data() {
        return {
            error: null,
            errors: {},
            title: this.initialTitle,
            handle: this.initialHandle,
            permissions: this.initialPermissions,
            isSuper: this.initialSuper
        }
    },

    watch: {
        'title': function(display) {
            this.handle = this.$slugify(display, '_');
        }
    },

    computed: {

        hasErrors() {
            return this.error || Object.keys(this.errors).length;
        },

        payload() {
            return {
                title: this.title,
                handle: this.handle,
                super: this.isSuper,
                permissions: this.checkedPermissions
            }
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

            this.$axios[this.method](this.action, this.payload).then(response => {
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
