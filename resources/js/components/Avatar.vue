<template>

    <div class="rounded-sm overflow-hidden">
        <img v-if="user.avatar" :src="user.avatar" class="block" />
        <div v-else v-text="initials" class="text-center bg-pink text-white" />
    </div>

</template>

<script>
export default {

    props: {
        user: Object
    },

    computed: {

        initials() {
            return this.user.initials || '?';
        }

    }

}
</script>
