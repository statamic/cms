<script setup lang="ts">
import { cva } from 'cva';
import { twMerge } from 'tailwind-merge';
import { computed, ref, useAttrs } from "vue";

interface Asset {
    permalink: string;
}

interface User {
    initials?: string;
    avatar?: string | Asset;
    name?: string;
}

interface Props {
    user: User;
}

const props = withDefaults(defineProps<Props>(), {
    user: () => ({})
})

const attrs = useAttrs();
const hasAvatarError = ref(false);
const hasAvatar = computed(() => !!props.user.avatar && !hasAvatarError.value);

const avatarSrc = computed(() => {
    if (!hasAvatar.value) return null;

    return typeof props.user.avatar === 'object' ? props.user.avatar.permalink : props.user.avatar;
})

const avatarClasses = computed(() => {
    const classes = cva({
        base: 'size-7 rounded-xl [button:has(&)]:rounded-xl shape-squircle',
        variants: {
            type: {
                avatar: '',
                initials: 'antialiased text-white text-2xs font-medium flex flex-shrink-0 items-center justify-center bg-gradient-to-tr from-purple-500 to-red-600'
            }
        }
    })({
        type: hasAvatar.value ? 'avatar' : 'initials'
    })

    return twMerge(classes, attrs.class as string);
})
</script>

<template>
    <template v-if="hasAvatar">
        <img :src="avatarSrc" :class="avatarClasses" :alt="user.name" @error="hasAvatarError = true" />
    </template>
    <template v-else>
        <div :aria-label="user.name" :class="avatarClasses">
            {{ user.initials || '?' }}
        </div>
    </template>
</template>
