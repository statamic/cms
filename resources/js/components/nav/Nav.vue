<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { Badge, Button, Icon } from '@ui';
import useNavigation from './navigation.js';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import DynamicHtmlRenderer from '@/components/DynamicHtmlRenderer.vue';

const { nav, setParentActive, setChildActive } = useNavigation();
// temp
const page = usePage();
const localStorageKey = 'statamic.nav';
const isOpen = ref(localStorage.getItem(localStorageKey) !== 'closed');
const navRef = ref(null);
const isMobile = ref(false);
const collapsedByViewport = ref(false);
let clickListenerActive = false;
let navigateEventListener = null;

// temp
const showPrototypeNavBlock = computed(() => {
    const path = (page.url || '').split('?')[0];

    return path.endsWith('/fields');
});

onMounted(() => {
    // Check if screen is less than lg breakpoint (1024px)
    const mediaQuery = window.matchMedia('(width < 1024px)');
    isMobile.value = mediaQuery.matches;

    const handleMediaChange = (e) => {
        isMobile.value = e.matches;
        // Collapse nav when viewport shrinks to mobile size
        if (e.matches && isOpen.value) {
            isOpen.value = false;
            collapsedByViewport.value = true;
            localStorage.setItem(localStorageKey, 'closed');
        }
        // Expand nav when viewport grows back to desktop size (only if it was collapsed by viewport. If the user explicitly set the nav to collapse, we don't want to expand it.)
        if (!e.matches && !isOpen.value && collapsedByViewport.value) {
            isOpen.value = true;
            collapsedByViewport.value = false;
            localStorage.setItem(localStorageKey, 'open');
        }
        // Reset the flag if we're on desktop
        if (!e.matches) {
            collapsedByViewport.value = false;
        }
    };
    
    mediaQuery.addEventListener('change', handleMediaChange);
    
    nextTick(() => {
        watch(isOpen, (isOpen) => {
            const el = document.getElementById('main');
            el.classList.toggle('nav-closed', !isOpen);
            el.classList.toggle('nav-open', isOpen);
            
            // Delay enabling the click-outside listener to avoid catching the toggle click
            if (isOpen) {
                setTimeout(() => {
                    clickListenerActive = true;
                }, 100);
            } else {
                clickListenerActive = false;
            }
        }, { immediate: true });
    });

    // Mark page as fully loaded after all resources are loaded
    if (document.readyState === 'complete') {
        document.documentElement.classList.add('page-fully-loaded');
    } else {
        window.addEventListener('load', () => {
            document.documentElement.classList.add('page-fully-loaded');
        });
    }

    // Close nav when clicking outside (only on mobile)
    document.addEventListener('click', handleClickOutside);
    
    // Close nav on mobile when navigating to a different page
    navigateEventListener = router.on('navigate', () => {
        if (isMobile.value && isOpen.value) {
            isOpen.value = false;
            localStorage.setItem(localStorageKey, 'closed');
        }
    });
    
    onUnmounted(() => {
        document.removeEventListener('click', handleClickOutside);
        mediaQuery.removeEventListener('change', handleMediaChange);
        if (navigateEventListener) {
            navigateEventListener();
        }
    });
});

function handleClickOutside(event) {
    // Only handle click-outside on mobile (less than lg breakpoint)
    if (!isOpen.value || !clickListenerActive || !isMobile.value) return;
    if (navRef.value && !navRef.value.contains(event.target)) {
        isOpen.value = false;
        localStorage.setItem(localStorageKey, 'closed');
    }
}

function toggle() {
    isOpen.value = !isOpen.value;
    // Reset viewport flag since user is explicitly toggling, so we should respect their preference
    // even when viewport size changes (don't auto-expand if user manually closed it)
    collapsedByViewport.value = false;
    localStorage.setItem(localStorageKey, isOpen.value ? 'open' : 'closed');
}

function handleParentClick(event, item) {
	if (event.defaultPrevented) return;

    // Prevent opening in a new tab from updating the active state.
    if (event.ctrlKey || event.metaKey || event.which === 2) return;

    setParentActive(item);

    // Close nav on mobile when clicking a nav item
    if (isMobile.value) {
        isOpen.value = false;
        localStorage.setItem(localStorageKey, 'closed');
    }
}

function handleChildClick(event, item, child) {
	if (event.defaultPrevented) return;

    // Prevent opening in a new tab from updating the active state.
    if (event.ctrlKey || event.metaKey || event.which === 2) return;

    setChildActive(item, child);

    // Close nav on mobile when clicking a child nav item
    if (isMobile.value) {
        isOpen.value = false;
        localStorage.setItem(localStorageKey, 'closed');
    }
}

const cpBaseUrl = Statamic.$config.get('cpUrl');

function isUrlWithinControlPanel(url) {
    return url && (url === cpBaseUrl || url.startsWith(cpBaseUrl + '/'));
}

function shouldRenderAsInertiaLink(item) {
    if (item.attributes?.target === '_blank') return false;
    return isUrlWithinControlPanel(item.url);
}

Statamic.$keys.bind(['command+\\', ['[']], (e) => {
    e.preventDefault();
    toggle();
});

Statamic.$events.$on('nav.toggle', toggle);
</script>

<template>
    <div class="cp-sidebar-start" :class="sidebarStartSizeClass">
        <nav v-if="showPrototypeNavBlock" style="--graph-paper-y-offset: 2.5rem;" class="bg-graph-paper [&_button]:rounded-xl [&_button]:w-full [&_button]:font-normal [&_button]:justify-start [&_button]:h-9 [&_button_svg]:size-3.5">
            <ul class="px-0.5 grid gap-8">
                <li>
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Information</h2>
                    <ul class="grid gap-2 grid-cols-2">
                        <li>
                            <Button :text="__('Heading')" icon="heading" />
                        </li>
                        <li>
                            <Button :text="__('Paragraph')" icon="text-short" />
                        </li>
                        <li>
                            <Button :text="__('Banner')" icon="banner" />
                        </li>
                        <li>
                            <Button :text="__('Legal')" icon="list" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Text</h2>
                    <ul class="grid gap-2 grid-cols-2">
                        <li>
                            <Button :text="__('Short Answer')" icon="text-short" />
                        </li>
                        <li>
                            <Button :text="__('Long Answer')" icon="text-long" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Choice</h2>
                    <ul class="grid gap-2 grid-cols-2">
                        <li>
                            <Button :text="__('Dropdown')" icon="fieldtype-select" />
                        </li>
                        <li>
                            <Button :text="__('Yes/No')" icon="like" />
                        </li>
                        <li>
                            <Button :text="__('Multi Choice')" icon="fieldtype-radio" />
                        </li>
                        <li>
                            <Button :text="__('Checkboxes')" icon="fieldtype-checkboxes" />
                        </li>
                        <li>
                            <Button :text="__('Toggle')" icon="fieldtype-toggle" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Rate</h2>
                    <ul class="grid gap-2 grid-cols-2">
                        <li>
                            <Button :text="__('Star Rating')" icon="star" />
                        </li>
                        <li>
                            <Button :text="__('Ranking')" icon="rank" />
                        </li>
                        <li>
                            <Button :text="__('Opinion Scale')" icon="scale-up" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Number</h2>
                    <ul class="grid gap-2 grid-cols-2">
                        <li>
                            <Button :text="__('Number')" icon="number" />
                        </li>
                        <li>
                            <Button :text="__('Currency')" icon="currency" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Date and Time</h2>
                    <ul class="grid gap-2 grid-cols-2">
                        <li>
                            <Button :text="__('Date Picker')" icon="calendar" />
                        </li>
                        <li>
                            <Button :text="__('Time Picker')" icon="time-clock" />
                        </li>
                        <li>
                            <Button :text="__('Range')" icon="calendar-range" />
                        </li>
                        <li>
                            <Button :text="__('SavvyCal')" icon="calendar" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Contact Info</h2>
                    <ul class="grid gap-2 grid-cols-2">
                        <li>
                            <Button :text="__('Name')" icon="user-avatar-flush" />
                        </li>
                        <li>
                            <Button :text="__('Email')" icon="mail-sign-at" />
                        </li>
                        <li>
                            <Button :text="__('Website')" icon="website" />
                        </li>
                        <li>
                            <Button :text="__('Phone')" icon="mail-sign-hashtag" />
                        </li>
                        <li>
                            <Button :text="__('Address')" icon="location-pin" />
                        </li>
                        <li>
                            <Button :text="__('Signature')" icon="edit-pen-draw-scribble" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Media</h2>
                    <ul class="grid gap-2 grid-cols-2">
                        <li>
                            <Button :text="__('Image Choice')" icon="image-select" />
                        </li>
                        <li>
                            <Button :text="__('Video')" icon="fieldtype-video" />
                        </li>
                        <li>
                            <Button :text="__('Audio')" icon="media-music-sound-equalizer" />
                        </li>
                        <li>
                            <Button :text="__('Upload')" icon="upload-arrow-up" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Payment</h2>
                    <ul class="grid gap-2 grid-cols-2">
                        <li>
                            <Button :text="__('Stripe')" icon="credit-card" />
                        </li>
                        <li>
                            <Button :text="__('PayPal')" icon="credit-card" />
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <nav v-else ref="navRef" class="cp-sidebar-nav-main">
            <div v-for="(section, i) in nav" :key="i">
                <div
                    class="section-title"
                    v-if="section.display !== 'Top Level'"
                    v-text="__(section.display)"
                />
                <ul>
                    <li v-for="(item, i) in section.items" :key="i">
                        <DynamicHtmlRenderer v-if="item.view" :html="item.view" />
                        <template v-else>
                            <component
                                :is="shouldRenderAsInertiaLink(item) ? Link : 'a'"
                                :href="item.url"
                                v-bind="item.attributes"
                                :class="{ 'active': item.active }"
                                @click="handleParentClick($event, item)"
                            >
                                <Icon :name="item.icon ?? 'fieldtype-spacer'" />
                                <span v-text="__(item.display)" />
                            </component>
                            <ul v-if="item.children.length && item.active">
                                <li v-for="(child, i) in item.children" :key="i">
                                    <component
                                        :is="shouldRenderAsInertiaLink(child) ? Link : 'a'"
                                        :href="child.url"
                                        v-bind="child.attributes"
                                        v-text="__(child.display)"
                                        :class="{ 'active': child.active }"
                                        @click="handleChildClick($event, item, child)"
                                    />
                                </li>
                            </ul>
                        </template>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</template>
