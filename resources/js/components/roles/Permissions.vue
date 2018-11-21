<template>

    <div>

        <label class="flex">
            <div class="leading-normal">
                <input type="checkbox" v-model="isSuper" name="super" />
            </div>
            <div class="pl-1">
                {{ __('Super User') }}
                <div class="text-grey text-xs">{{ __('permission_super') }}</div>
            </div>
        </label>

        <role-permission-tree
            v-if="!isSuper"
            class="mt-3 pt-3 border-t"
            :initial-permissions="permissions"
        />

    </div>

</template>

<script>
export default {

    props: {
        initialSuper: Boolean,
        initialPermissions: Array
    },

    data() {
        return {
            isSuper: this.initialSuper,
            permissions: this.initialPermissions
        }
    }

}
</script>
