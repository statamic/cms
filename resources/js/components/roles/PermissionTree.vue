<template>

    <ul class="list-reset">
        <li v-for="permission in permissions" :key="permission.value">
            <label class="flex mb-1" :class="{ 'text-grey-light': disabled, 'cursor-not-allowed': disabled }">
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

            <role-permission-tree
                v-if="permission.children.length"
                class="ml-3"
                :initial-permissions="permission.children"
                :disabled="!permission.checked"
            />
        </li>
    </ul>

</template>

<script>
export default {

    props: {
        initialPermissions: Array,
        disabled: Boolean
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
