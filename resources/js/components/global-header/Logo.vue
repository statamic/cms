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
                <div class="max-sm:hidden p-1 mr-2 size-7 flex items-center justify-center">
                    <Icon name="burger-menu" class="size-5 opacity-60" />
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
