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

const initials = computed(() => {
    if (props.user.initials) return props.user.initials;

    if (props.user.name) {
        const names = props.user.name.split(' ');
        return names.length === 1
            ? names[0].charAt(0).toUpperCase()
            : (names[0].charAt(0) + names[names.length - 1].charAt(0)).toUpperCase();
    }

    return '?';
});

const meshGradientStyle = computed(() => {
    // Get or generate base hue for this user
    const userId = props.user.id || props.user.email || props.user.name || 'anonymous';
    const storageKey = `avatar-hue-${userId}`;

    let baseHue;
    if (typeof sessionStorage !== 'undefined') {
        const storedHue = sessionStorage.getItem(storageKey);
        if (storedHue !== null) {
            baseHue = parseInt(storedHue);
        } else {
            baseHue = Math.floor(Math.random() * 360);
            sessionStorage.setItem(storageKey, baseHue.toString());
        }
    } else {
        // Fallback: generate deterministic hue based on user
        let hash = 0;
        for (let i = 0; i < userId.length; i++) {
            hash = userId.charCodeAt(i) + ((hash << 5) - hash);
        }
        baseHue = Math.abs(hash) % 360;
    }

    // Get or generate color scheme for this user
    const schemeKey = `avatar-scheme-${userId}`;
    let scheme;
    if (typeof sessionStorage !== 'undefined') {
        const storedScheme = sessionStorage.getItem(schemeKey);
        if (storedScheme !== null) {
            scheme = parseInt(storedScheme);
        } else {
            scheme = Math.floor(Math.random() * 3); // 0: complementary, 1: triadic, 2: analogous
            sessionStorage.setItem(schemeKey, scheme.toString());
        }
    } else {
        // Fallback: deterministic scheme based on user
        let hash = 0;
        for (let i = 0; i < userId.length; i++) {
            hash = userId.charCodeAt(i) + ((hash << 5) - hash);
        }
        scheme = Math.abs(hash) % 3;
    }

    let hues = [];
    if (scheme === 0) {
        // Complementary (base + opposite)
        hues = [baseHue, (baseHue + 180) % 360];
    } else if (scheme === 1) {
        // Triadic (base + 120° + 240°)
        hues = [baseHue, (baseHue + 120) % 360, (baseHue + 240) % 360];
    } else {
        // Analogous (base + nearby hues)
        hues = [
            baseHue,
            (baseHue + 30) % 360,
            (baseHue - 30 + 360) % 360,
            (baseHue + 60) % 360
        ];
    }

    // Generate vivid colors with good contrast for white text
    const colors = hues.map(hue => {
        const saturation = 70 + Math.floor(Math.random() * 25); // 70-95% (more vivid)
        const lightness = 40 + Math.floor(Math.random() * 30);  // 40-70% (vivid but not too light)
        return `hsl(${hue}, ${saturation}%, ${lightness}%)`;
    });

    // Generate positions
    const positions = colors.map(() =>
        `${20 + Math.floor(Math.random() * 60)}% ${20 + Math.floor(Math.random() * 60)}%`
    );

    // Build gradient string with larger sizes for small avatars
    const gradients = colors.map((color, index) =>
        `radial-gradient(circle at ${positions[index]}, ${color} 0%, transparent 80%)`
    ).join(', ');

    return {
        background: `${gradients}, ${colors[0]}`
    };
});

const avatarClasses = computed(() => {
    const classes = cva({
        base: 'size-7 rounded-xl [button:has(&)]:rounded-xl shape-squircle',
        variants: {
            type: {
                avatar: '',
                initials: 'antialiased text-white text-2xs font-medium flex flex-shrink-0 items-center justify-center'
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
        <div :aria-label="user.name" :class="avatarClasses" :style="meshGradientStyle">
            {{ initials }}
        </div>
    </template>
</template>
