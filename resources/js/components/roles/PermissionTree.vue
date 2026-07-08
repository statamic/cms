<template>
    <div :style="`padding-inline-start: ${12 * depth - 12}px`">
        <template v-for="permission in visiblePermissions" :key="permission.value">
            <ui-checkbox
                :class="[
                    permission.description
                        ? '[&_label]:font-medium pb-2.75'
                        : '[&_label]:text-gray-925 dark:[&_label]:text-gray-200 pb-2.5'
                ]"
                :description="permission.description"
                :label="permission.label"
                :value="permission.value"
                :modelValue="permission.checked"
                @update:modelValue="updatePermission(permission, $event)"
            />

            <PermissionTree
                v-if="permission.children.length"
                :depth="depth + 1"
                :initial-permissions="permission.children"
            />
        </template>
    </div>
</template>

<script>
export default {
    props: {
        initialPermissions: Array,
        depth: Number,
    },

    inject: ['checkedPermissionValues'],

    data() {
        return {
            permissions: this.initialPermissions,
        };
    },

    computed: {
        visiblePermissions() {
            return this.permissions.filter(
                (permission) =>
                    !permission.superseded_by || !this.checkedPermissionValues.includes(permission.superseded_by)
            );
        },
    },

    watch: {
        initialPermissions: {
            handler(newPermissions) {
                this.permissions = newPermissions;
            },
            deep: true,
        },
    },

    methods: {
        updatePermission(permission, checked) {
            permission.checked = checked;
        }
    }
};
</script>
