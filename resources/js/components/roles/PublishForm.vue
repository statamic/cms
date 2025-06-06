<template>
    <div>
        <Header :title="__(initialTitle) || __('Create Role')">
            <Button type="submit" variant="primary" @click="save" :text="__('Save')" />
        </Header>

        <div class="card configure-tab publish-fields @container mb-6 p-0">
            <form-group
                handle="title"
                class="dark:border-dark-900 border-b"
                :display="__('Title')"
                :errors="errors.title"
                :instructions="__('messages.role_title_instructions')"
                v-model="title"
                :focus="true"
            />

            <form-group
                class="dark:border-dark-900 border-b"
                fieldtype="slug"
                handle="handle"
                :display="__('Handle')"
                :instructions="__('messages.role_handle_instructions')"
                :errors="errors.title"
                v-model="handle"
            />

            <div class="p-6 pt-0 text-xs text-red-500" v-if="initialHandle && handle != initialHandle">
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
        </div>

        <div v-if="!isSuper">
            <div class="content mt-6" v-for="group in permissions" :key="group.handle">
                <h2 class="mt-10 mb-2 text-base">{{ group.label }}</h2>
                <role-permission-tree class="card p-0" :depth="1" :initial-permissions="group.permissions" />
            </div>
        </div>
    </div>
</template>

<script>
import { Header, Button } from '@statamic/ui';
import { requireElevatedSession } from '@statamic/components/elevated-sessions';

const checked = function (permissions) {
    return permissions.reduce((carry, permission) => {
        if (!permission.checked) return carry;
        return [...carry, permission.value, ...checked(permission.children)];
    }, []);
};

export default {
    components: {
        Header,
        Button,
    },

    props: {
        initialTitle: String,
        initialHandle: String,
        initialPermissions: Array,
        initialSuper: Boolean,
        canAssignSuper: Boolean,
        action: String,
        method: String,
        indexUrl: String,
    },

    data() {
        return {
            error: null,
            errors: {},
            title: this.initialTitle,
            handle: this.initialHandle,
            permissions: this.initialPermissions,
            isSuper: this.initialSuper,
        };
    },

    watch: {
        title: function (display) {
            this.handle = snake_case(display);
        },
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
                permissions: this.checkedPermissions,
            };
        },

        checkedPermissions() {
            return this.permissions.reduce((carry, group) => {
                return [...carry, ...checked(group.permissions)];
            }, []);
        },
    },

    methods: {
        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            requireElevatedSession()
                .then(() => this.performSaveAction())
                .catch(() => this.$toast.error(__('Unable to save role')));
        },

        performSaveAction() {
            this.clearErrors();

            this.$axios[this.method](this.action, this.payload)
                .then((response) => {
                    window.location = response.data.redirect;
                })
                .catch((e) => {
                    if (e.response && e.response.status === 422) {
                        const { message, errors } = e.response.data;
                        this.error = message;
                        this.errors = errors;
                        this.$toast.error(message);
                    } else {
                        this.$toast.error(__('Unable to save role'));
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
