<script setup>
import { computed } from 'vue';
import { Icon } from '@ui';
import StatamicLogo from '@/../svg/statamic-mark-lime.svg';
import ProBadge from './ProBadge.vue';
import { Link } from '@inertiajs/vue3';
import useStatamicPageProps from '@/composables/page-props.js';
import { invokeCpNavToggle } from '@/components/nav/nav-toggle-channel.js';

const { logos, isPro, cmsName, version } = useStatamicPageProps();
const customLogoImage = computed(() => {
    if (! logos) return null
    return logos.dark.nav ?? logos.light.nav;
});
const customLogoText = computed(() => logos?.text);
const customLogo = computed(() => customLogoImage.value || customLogoText.value);

function toggleNav() {
    invokeCpNavToggle();
}
</script>

<template>
    <template v-if="customLogo">
        <div class="flex items-center gap-3 relative rounded-xs">
            <button
                type="button"
                class="flex items-center group rounded-xs cursor-pointer"
                data-cp-global-nav-toggle
                :aria-label="__('Toggle Nav')"
                v-tooltip="__('Toggle Nav')"
                style="--focus-outline-offset: 0.2rem;"
                @click.stop="toggleNav"
            >
                <div class="p-1 max-sm:ps-2 size-5 flex items-center justify-center lg:inset-0">
                    <Icon name="burger-menu-no-border" class="size-3.5! sm:size-3.25! opacity-75 hover:opacity-100" />
                </div>
            </button>
            <Link :href="cp_url('/')" v-tooltip="version" style="--focus-outline-offset: var(--outline-offset-button);">
                <img v-if="customLogoImage" :src="customLogoImage" :alt="cmsName" class="w-full max-w-[260px] max-h-7">
                <span v-if="customLogoText && !customLogoImage" class="font-medium text-white whitespace-nowrap">{{ customLogoText }}</span>
            </Link>
        </div>
    </template>
    <template v-else>
        <div class="flex items-center gap-1.5 sm:gap-2.5 relative">
            <button
                type="button"
                class="flex items-center group rounded-xs cursor-pointer"
                data-cp-global-nav-toggle
                :aria-label="__('Toggle Nav')"
                v-tooltip="__('Toggle Nav')"
                style="--focus-outline-offset: 0.2rem;"
                @click.stop="toggleNav"
            >
                <div class="p-1 max-sm:ps-2 size-5 flex items-center justify-center lg:inset-0">
                    <Icon name="burger-menu-no-border" class="size-3.5! sm:size-3.25! opacity-75 hover:opacity-100" />
                </div>
            </button>
            <Link :href="cp_url('/')" class="flex items-center gap-1.5 max-[350px]:hidden text-white/85 rounded-xs whitespace-nowrap" style="--focus-outline-offset: var(--outline-offset-button);">
                <StatamicLogo class="size-7 site-logo [&:focus:not(:focus-visible)]:outline-none" v-tooltip="version" />
                <span>{{ logos.text ?? logos.siteName }}</span>
            </Link>
            <ProBadge v-if="isPro" class="-ms-0.25" />
        </div>
    </template>
</template>
