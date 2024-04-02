<template>

    <div>
        <div v-for="permission in permissions" :key="permission.value">
            <label
                class="flex items-center justify-between py-2 rtl:pl-4 ltr:pr-4 border-b group hover:bg-gray-100"
                :style="direction === 'ltr' ? { paddingLeft: `${16*depth}px` } : { paddingRight: `${16*depth}px` }"
            >
                <div class="flex">
                    <div class="leading-normal">
                        <input type="checkbox"
                            v-model="permission.checked"
                            :value="permission.value"
                            name="permissions[]"
                        />
                    </div>
                    <div class="rtl:pr-2 ltr:pl-2">
                        {{ permission.label }}
                    </div>
                </div>
                <div class="text-gray-700 text-xs opacity-0 group-hover:opacity-100" v-if="permission.description" v-text="permission.description" />
            </label>

            <role-permission-tree
                v-if="permission.children.length"
                :depth="depth+1"
                :initial-permissions="permission.children"
            />
        </div>
    </div>

</template>

<script>
export default {

    props: {
        initialPermissions: Array,
        depth: Number
    },

    data() {
        return {
            permissions: this.initialPermissions
        }
    },

    computed: {
        direction() {
            return this.$config.get('direction', 'rtl');
        }
    }

}
</script>
