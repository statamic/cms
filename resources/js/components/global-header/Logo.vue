<script setup>
import { computed } from 'vue';
import { Icon } from '@ui';
import StatamicLogo from '@/../svg/statamic-mark-lime.svg';
import ProBadge from './ProBadge.vue';
import { Link } from '@inertiajs/vue3';

const logos = Statamic.$config.get('logos');
const isPro = Statamic.$config.get('isPro');
const cmsName = Statamic.$config.get('cmsName');

const customLogo = computed(() => {
    if (! logos) return null
    return logos.dark.nav ?? logos.light.nav;
});

function toggleNav() {
    //
}
</script>

<template>
    <template v-if="customLogo">
        <button class="flex items-center group cursor-pointer text-white/85 hover:text-white" type="button" @click="toggleNav" aria-label="{{ __('Toggle Nav') }}">
            <div class="p-1 size-7 inset-0 flex items-center justify-center">
                <Icon name="burger-menu" class="size-5" />
            </div>
        </button>
        <img :src="customLogo" :alt="cmsName" class="max-w-[260px] max-h-9">
    </template>
    <template v-else>
        <div class="flex items-center gap-2 relative">
            <button class="flex items-center group rounded-lg cursor-pointer" type="button" @click="toggleNav" aria-label="{{ __('Toggle Nav') }}" style="--focus-outline-offset: 0.2rem;">
                <div class="opacity-0 group-hover:opacity-100 p-1 size-7 transition-opacity duration-150 absolute inset-0 flex items-center justify-center">
                    <Icon name="burger-menu" class="size-5" />
                </div>
                <StatamicLogo class="size-7 group-hover:opacity-0 transition-opacity duration-150" />
            </button>
            <Link :href="cp_url('/')" class="hidden sm:block text-white/85 rounded-xs whitespace-nowrap" style="--focus-outline-offset: var(--outline-offset-button);">
                {{ logos.text }}
            </Link>
            <ProBadge v-if="isPro" />
        </div>
    </template>
</template>
