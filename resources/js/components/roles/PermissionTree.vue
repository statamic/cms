<template>
    <div>
        <template v-for="permission in permissions" :key="permission.value">
            <ui-checkbox-item
                class="pb-2.5 [&_label]:text-gray-900 dark:[&_label]:text-gray-200"
                :description="permission.description"
                :label="permission.label"
                :style="`padding-left: ${24 * depth - 24}px`"
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

    data() {
        return {
            permissions: this.initialPermissions,
        };
    },

    methods: {
        updatePermission(permission, checked) {
            permission.checked = checked;
        }
    }
};
</script>
