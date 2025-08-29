<script setup>
import { cva } from 'cva';
import { twMerge } from 'tailwind-merge';
import {computed, ref} from "vue";

const props = defineProps({
    user: Object,
    class: {
        type: String,
        default: ''
    }
})

const hasAvatarError = ref(false);

const hasAvatar = computed(() => {
    return !!props.user.avatar && !hasAvatarError.value
});

const avatarSrc = computed(() => {
    if (!hasAvatar.value) return null;

    return props.user.avatar.permalink || props.user.avatar;
})

const avatarClasses = computed(() => {
    const classes = cva({
        base: 'size-7 rounded-full [button:has(&)]:rounded-full',
        variants: {
            type: {
                avatar: '',
                initials: 'text-white text-2xs font-medium flex flex-shrink-0 items-center justify-center bg-gradient-to-tr from-purple-500 to-red-600'
            }
        }
    })({
        type: hasAvatar.value ? 'avatar' : 'initials'
    })

    return twMerge(classes, props.class);
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
