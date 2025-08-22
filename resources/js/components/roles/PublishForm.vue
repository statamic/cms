<template>
    <div class="max-w-5xl mx-auto">
        <Header :title="__(initialTitle) || __('Create Role')" icon="permissions">
            <Button type="submit" variant="primary" @click="save" :text="__('Save')" />
        </Header>

        <Panel>
            <div class="publish-fields-fluid">
                <Field
                    as="card"
                    class="field-w-50"
                    :label="__('Title')"
                    :instructions="__('messages.role_title_instructions')"
                    :errors="errors.title"
                    id="role-title"
                >
                    <Input v-model="title" id="role-title" autocomplete="off" focus />
                </Field>

                <Field
                    as="card"
                    class="field-w-50"
                    :label="__('Handle')"
                    :instructions="__('messages.role_handle_instructions')"
                    :errors="handleErrors"
                    id="role-handle"
                >
                    <Input v-model="handle" id="role-handle" autocomplete="off" />
                </Field>

                <Field
                    as="card"
                    v-if="canAssignSuper"
                    :label="__('permissions.super')"
                    :instructions="__('permissions.super_desc')"
                    id="role-super"
                >
                    <Switch v-model="isSuper" id="role-super" />
                </Field>
            </div>
        </Panel>

        <div v-if="!isSuper" class="space-y-6 mt-6">
            <CardPanel v-for="group in permissions" :key="group.handle" :heading="group.label">
                <PermissionTree :depth="1" :initial-permissions="group.permissions" />
            </CardPanel>
        </div>
    </div>
</template>

<script>
import { Header, Button, CardPanel, Panel, PanelHeader, Heading, Card, Switch, Field, Input } from '@/components/ui';
import { requireElevatedSession } from '@/components/elevated-sessions';
import PermissionTree from '@/components/roles/PermissionTree.vue';

const checked = function (permissions) {
    return permissions.reduce((carry, permission) => {
        if (!permission.checked) return carry;
        return [...carry, permission.value, ...checked(permission.children)];
    }, []);
};

export default {
    components: {
        PermissionTree,
        Header,
        Button,
        CardPanel,
        Panel,
        PanelHeader,
        Heading,
        Card,
        Switch,
        Field,
        Input,
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
        handleErrors() {
            let errors = this.errors.handle || [];

            if (this.initialHandle && this.handle !== this.initialHandle) {
                errors = errors.concat(__('messages.role_change_handle_warning'));
            }

            return errors;
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
