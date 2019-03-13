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

                <form-group
                    fieldtype="user_roles"
                    :display="__('Roles')"
                    handle="roles"
                    :errors="errors.roles"
                    v-model="roles"
                />

            </div>

        </div>
</template>


<script>
import axios from 'axios';

export default {

    props: {
        initialTitle: String,
        initialHandle: String,
        initialRoles: Array,
        initialUsers: Array,
        action: String,
        method: String
    },

    data() {
        return {
            error: null,
            errors: {},
            title: this.initialTitle,
            handle: this.initialHandle,
            roles: this.initialRoles,
            users: this.initialUsers,
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
                roles: this.roles,
                users: this.users,
            }
        }

    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.clearErrors();

            axios[this.method](this.action, this.payload).then(response => {
                this.$notify.success('Saved');
                if (!this.initialHandle || (this.initialHandle !== this.handle)) {
                    window.location = response.data.redirect;
                }
            }).catch(e => {
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$notify.error(message, { timeout: 2000 });
                } else {
                    this.$notify.error('Something went wrong');
                }
            });
        }

    },

    mounted() {
        this.$mousetrap.bindGlobal(['command+s'], e => {
            e.preventDefault();
            this.save();
        });
    }

}
</script>
