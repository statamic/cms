<template>
    <div class="max-w-page mx-auto">
        <Header :title="__(initialTitle) || __('Create Role')" icon="permissions">
            <CommandPaletteItem
                :category="$commandPalette.category.Actions"
                :text="__('Save')"
                icon="save"
                :action="save"
                prioritize
                v-slot="{ text, action }"
            >
                <Button
                    :icon="areAllCheckedInAllGroups() ? 'checkbox-uncheck' : 'checkbox'"
                    @click="toggleAllInAllGroups()"
                    :text="areAllCheckedInAllGroups() ? __('Uncheck All') : __('Check All')"
                />
                <Button type="submit" variant="primary" @click="action" :text="text" />
            </CommandPaletteItem>
        </Header>

        <Panel :heading="__('Settings')">
            <Card class="p-0! divide-y divide-gray-200 dark:divide-gray-800">
                <Field
                    inline
                    :label="__('Title')"
                    :instructions="__('messages.role_title_instructions')"
                    :errors="errors.title"
                    id="role-title"
                >
                    <Input v-model="title" id="role-title" autocomplete="off" focus />
                </Field>

                <Field
                    inline
                    :label="__('Handle')"
                    :instructions="__('messages.role_handle_instructions')"
                    :errors="handleErrors"
                    id="role-handle"
                >
                    <Input v-model="handle" id="role-handle" autocomplete="off" />
                </Field>

                <Field
                    inline
                    v-if="canAssignSuper"
                    :label="__('permissions.super')"
                    :instructions="__('permissions.super_desc')"
                    variant="inline"
                    id="role-super"
                >
                    <Switch v-model="isSuper" id="role-super" />
                </Field>
            </Card>
        </Panel>

        <div v-if="!isSuper" class="space-y-6 mt-6">
            <Panel :heading="group.label" v-for="group in permissions" :key="group.handle">
                <template #header-actions>
                    <Button
                        size="sm"
                        variant="subtle"
                        :icon="areAllChecked(group) ? 'checkbox-uncheck' : 'checkbox'"
                        @click="toggleAllInGroup(group)"
                    >
                        {{ areAllChecked(group) ? __('Uncheck All') : __('Check All') }}
                    </Button>
                </template>
                <Card>
                    <PermissionTree :depth="1" :initial-permissions="group.permissions" />
                </Card>
            </Panel>
        </div>
    </div>
</template>

<script>
import { Header, Button, Panel, PanelHeader, Heading, Card, Switch, Field, Input, CommandPaletteItem } from '@/components/ui';
import { requireElevatedSession } from '@/components/elevated-sessions';
import PermissionTree from '@/components/roles/PermissionTree.vue';
import { router } from '@inertiajs/vue3';

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
        Panel,
        PanelHeader,
        Heading,
        Card,
        Switch,
        Field,
        Input,
        CommandPaletteItem,
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
        areAllChecked(group) {
            const checkAll = (permissions) => {
                return permissions.every(permission => {
                    const childrenChecked = permission.children && permission.children.length
                        ? checkAll(permission.children)
                        : true;
                    return permission.checked && childrenChecked;
                });
            };
            return checkAll(group.permissions);
        },

        areAllCheckedInAllGroups() {
            return this.permissions.every(group => this.areAllChecked(group));
        },

        toggleAllInGroup(group) {
            const allChecked = this.areAllChecked(group);
            const toggle = (permissions, checked) => {
                permissions.forEach(permission => {
                    permission.checked = checked;
                    if (permission.children && permission.children.length) {
                        toggle(permission.children, checked);
                    }
                });
            };
            toggle(group.permissions, !allChecked);
        },

        toggleAllInAllGroups() {
            const allChecked = this.areAllCheckedInAllGroups();
            this.permissions.forEach(group => {
                const toggle = (permissions, checked) => {
                    permissions.forEach(permission => {
                        permission.checked = checked;
                        if (permission.children && permission.children.length) {
                            toggle(permission.children, checked);
                        }
                    });
                };
                toggle(group.permissions, !allChecked);
            });
        },

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
                    router.get(response.data.redirect);
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
