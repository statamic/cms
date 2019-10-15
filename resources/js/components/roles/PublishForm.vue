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
                    {{ __('role_change_handle_warning') }}
                </div>

            </div>

            <div class="card">

                <role-permissions
                    :initial-super="isSuper"
                    v-model="permissions"
                    @super-updated="isSuper = $event"
                ></role-permissions>

            </div>

        </div>
</template>


<script>
import RolePermissions from './Permissions.vue';

const checked = function (permissions) {
    return permissions.reduce((carry, permission) => {
        if (! permission.checked) return carry;
        return [...carry, permission.value, ...checked(permission.children)];
    }, []);
};

export default {

    components: {
        RolePermissions
    },

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
            return checked(this.permissions);
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
