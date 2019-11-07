<template>

        <div>
            <div class="flex items-center mb-3">
                <slot name="heading" :title="initialTitle" />
                <button type="submit" class="btn btn-primary" @click.prevent="save">{{ __('Save') }}</button>
            </div>

            <div class="card p-0 mb-3 publish-fields">

                <form-group
                    :display="__('Title')"
                    handle="title"
                    width="50"
                    :errors="errors.title"
                    v-model="title"
                    autofocus
                />

                <form-group
                    fieldtype="slug"
                    :display="__('Handle')"
                    handle="handle"
                    width="50"
                    :errors="errors.title"
                    v-model="handle"
                />

                <div class="text-xs text-red p-3 pt-0" v-if="initialHandle && handle != initialHandle">
                    {{ __('messages.role_change_handle_warning') }}
                </div>

                <form-group
                    fieldtype="toggle"
                    handle="super"
                    :display="__('permissions.super')"
                    :instructions="__('permissions.super_desc')"
                    v-model="isSuper"
                />

            </div>

            <div v-if="!isSuper">
                <div class="mt-3" v-for="group in permissions" :key="group.handle">
                    <h2 class="mt-4 mb-2 font-bold text-xl">{{ group.label }}</h2>
                    <role-permission-tree class="card p-0" :depth="1" :initial-permissions="group.permissions" />
                </div>
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
        action: String,
        method: String
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
                this.$toast.success('Saved');
                if (!this.initialHandle || (this.initialHandle !== this.handle)) {
                    window.location = response.data.redirect;
                }
            }).catch(e => {
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$toast.error(message);
                } else {
                    this.$toast.error('Something went wrong');
                }
            });
        }

    },

    mounted() {
        this.$mousetrap.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.save();
        });
    }

}
</script>
