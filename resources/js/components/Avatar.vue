<template>
    <div class="overflow-hidden rounded-full" v-tooltip="user.name">
        <img v-if="useAvatar" :src="avatarSrc" class="block" @error="hasAvatarError = true" />
        <div v-if="useInitials" class="bg-pink flex h-full w-full items-center justify-center text-center text-white">
            <span>{{ initials }}</span>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        user: Object,
    },

    data() {
        return {
            hasAvatarError: false,
        };
    },

    computed: {
        initials() {
            return this.user.initials || '?';
        },

        useAvatar() {
            return this.hasAvatar && !this.hasAvatarError;
        },

        hasAvatar() {
            return !!this.user.avatar;
        },

        avatarSrc() {
            if (!this.hasAvatar) return null;

            return this.user.avatar.permalink || this.user.avatar;
        },

        useInitials() {
            return !this.hasAvatar || this.hasAvatarError;
        },
    },
};
</script>
