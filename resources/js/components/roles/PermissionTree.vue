<template>
    <div>
        <div v-for="permission in permissions" :key="permission.value">
            <label
                class="group flex items-center justify-between border-b py-2 hover:bg-gray-100 dark:border-dark-900 dark:hover:bg-dark-700 ltr:pr-4 rtl:pl-4"
                :style="direction === 'ltr' ? { paddingLeft: `${16 * depth}px` } : { paddingRight: `${16 * depth}px` }"
            >
                <div class="flex">
                    <div class="leading-normal">
                        <input
                            type="checkbox"
                            v-model="permission.checked"
                            :value="permission.value"
                            name="permissions[]"
                        />
                    </div>
                    <div class="ltr:pl-2 rtl:pr-2">
                        {{ permission.label }}
                    </div>
                </div>
                <div
                    class="text-xs text-gray-700 opacity-0 group-hover:opacity-100 dark:text-dark-175"
                    v-if="permission.description"
                    v-text="permission.description"
                />
            </label>

            <role-permission-tree
                v-if="permission.children.length"
                :depth="depth + 1"
                :initial-permissions="permission.children"
            />
        </div>
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

    computed: {
        direction() {
            return this.$config.get('direction', 'ltr');
        },
    },
};
</script>
