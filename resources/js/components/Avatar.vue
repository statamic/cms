<template>
    <template v-if="hasAvatar">
        <img :src="avatarSrc" class="size-7 rounded-full [button:has(&)]:rounded-full" :alt="user.name" @error="hasAvatarError = true" />
    </template>
    <template v-else>
        <div :aria-label="user.name" class="size-7 text-white text-2xs font-medium flex items-center justify-center rounded-full [button:has(&)]:rounded-full bg-gradient-to-tr from-purple-500 to-red-600">{{ initials }}</div>
    </template>
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
