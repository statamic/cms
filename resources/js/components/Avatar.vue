<template>

    <div class="rounded-sm overflow-hidden" v-tooltip="user.name">
        <img v-if="user.avatar" :src="user.avatar.permalink || user.avatar" class="block" />
        <div v-else class="text-center flex items-center justify-center h-full w-full bg-pink text-white"><span>{{ initials }}</span></div>
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
