<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { Badge, Icon } from '@ui';
import useNavigation from './navigation.js';
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import DynamicHtmlRenderer from '@/components/DynamicHtmlRenderer.vue';

const { nav, setParentActive, setChildActive } = useNavigation();
const localStorageKey = 'statamic.nav';
const isOpen = ref(localStorage.getItem(localStorageKey) !== 'closed');
const navRef = ref(null);
const isMobile = ref(false);
let clickListenerActive = false;

onMounted(() => {
    // Check if screen is less than lg breakpoint (1024px)
    const mediaQuery = window.matchMedia('(width < 1024px)');
    isMobile.value = mediaQuery.matches;
    
    const handleMediaChange = (e) => {
        isMobile.value = e.matches;
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
    
    onUnmounted(() => {
        document.removeEventListener('click', handleClickOutside);
        mediaQuery.removeEventListener('change', handleMediaChange);
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

Statamic.$keys.bind(['command+\\', ['[']], (e) => {
    e.preventDefault();
    toggle();
});

Statamic.$events.$on('nav.toggle', toggle);
</script>

<template>
    <nav ref="navRef" class="nav-main">
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
                            :is="item.attributes?.target === '_blank' ? 'a' : Link"
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
                                    :is="child.attributes?.target === '_blank' ? 'a' : Link"
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
</template>
