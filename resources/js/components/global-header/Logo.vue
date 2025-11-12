<script setup>
import { computed } from 'vue';
import { Icon } from '@ui';
import StatamicLogo from '@/../svg/statamic-mark-lime.svg';
import ProBadge from './ProBadge.vue';
import { Link } from '@inertiajs/vue3';
import useStatamicPageProps from '@/composables/page-props.js';

const { logos, isPro, cmsName } = useStatamicPageProps();
const customLogo = computed(() => {
    if (! logos) return null
    return logos.dark.nav ?? logos.light.nav;
});

function toggleNav() {
    Statamic.$events.$emit('nav.toggle');
}
</script>

<template>
    <template v-if="customLogo">
        <button class="flex items-center group rounded-xs cursor-pointer" type="button" @click="toggleNav" :aria-label="__('Toggle Nav')" style="--focus-outline-offset: 0.2rem;">
            <div class="p-1 max-sm:ps-2 mr-2 size-5 flex items-center justify-center lg:inset-0">
                <Icon name="burger-menu-no-border" class="size-3.5! sm:size-3.25! opacity-75 hover:opacity-100" />
            </div>
            <img :src="customLogo" :alt="cmsName" class="max-w-[260px] max-h-8">
        </button>
    </template>
    <template v-else>
        <div class="flex items-center gap-1.5 sm:gap-2 relative">
            <button class="flex items-center group rounded-xs cursor-pointer" type="button" @click="toggleNav" :aria-label="__('Toggle Nav')" style="--focus-outline-offset: 0.2rem;">
                <div class="p-1 max-sm:ps-2 mr-2 size-5 flex items-center justify-center lg:inset-0">
                    <Icon name="burger-menu-no-border" class="size-3.5! sm:size-3.25! opacity-75 hover:opacity-100" />
                </div>
                <StatamicLogo class="size-7" />
            </button>
            <Link :href="cp_url('/')" class="max-[350px]:hidden text-white/85 rounded-xs whitespace-nowrap" style="--focus-outline-offset: var(--outline-offset-button);">
                {{ logos.text ?? logos.siteName }}
            </Link>
            <ProBadge v-if="isPro" />
        </div>
    </template>
</template>