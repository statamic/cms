<template>

    <div>
        <div v-for="permission in permissions" :key="permission.value">
            <div :style="{ paddingLeft: `${16*depth}px` }" class="flex items-center justify-between py-1 pr-2 border-b group hover:bg-grey-10">
                <label class="flex" :class="{ 'text-grey-40': disabled, 'cursor-not-allowed': disabled }">
                    <div class="leading-normal">
                        <input type="checkbox"
                            v-model="permission.checked"
                            :value="permission.value"
                            :disabled="disabled"
                            name="permissions[]"
                            :class="{ 'cursor-not-allowed': disabled }"
                        />
                    </div>
                    <div class="pl-1">
                        {{ permission.label }}
                    </div>
                </label>
                <div class="text-grey-50 text-xs opacity-0 group-hover:opacity-100" v-if="permission.description" v-text="permission.description" />
            </div>

            <role-permission-tree
                v-if="permission.children.length"
                :depth="depth+1"
                :initial-permissions="permission.children"
                :disabled="!permission.checked"
            />
        </div>
    </div>

</template>

<script>
export default {

    props: {
        initialPermissions: Array,
        disabled: Boolean,
        depth: Number
    },

    data() {
        return {
            permissions: this.initialPermissions
        }
    },

    watch: {

        disabled(disabled) {
            if (disabled) {
                this.permissions.map(permission => permission.checked = false);
            }
        }

    }

}
</script>
