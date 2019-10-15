<template>

    <div>

        <div class="card">
            <label class="flex">
                <div class="leading-normal">
                    <input type="checkbox" v-model="isSuper" name="super" />
                </div>
                <div class="pl-1">
                    {{ __('permissions.super') }}
                    <div class="text-grey text-xs">{{ __('permissions.super_desc') }}</div>
                </div>
            </label>
        </div>

        <div v-if="!isSuper">
            <div class="mt-3" v-for="group in groups" :key="group.handle">
                <h2 class="mt-4 mb-2 font-bold text-xl">{{ group.label }}</h2>
                <role-permission-tree class="card p-0" :depth="1" :initial-permissions="group.permissions" />
            </div>
        </div>

    </div>

</template>

<script>
export default {

    props: {
        initialSuper: Boolean,
        value: Array
    },

    data() {
        return {
            isSuper: this.initialSuper,
            groups: this.value
        }
    },

    watch: {
        isSuper(isSuper) {
            this.$emit('super-updated', isSuper);
        }
    }

}
</script>
