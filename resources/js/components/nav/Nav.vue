<script setup>
import { Link } from '@inertiajs/vue3';
import { Icon } from '@ui';
import useNavigation from './navigation.js';
import { nextTick, onMounted, ref, watch } from 'vue';
import DynamicHtmlRenderer from '@/components/DynamicHtmlRenderer.vue';

const { nav, setParentActive, setChildActive } = useNavigation();
const localStorageKey = 'statamic.nav';
const isOpen = ref(localStorage.getItem(localStorageKey) !== 'closed');

onMounted(() => {
    nextTick(() => {
        watch(isOpen, (isOpen) => {
            const el = document.getElementById('main');
            el.classList.toggle('nav-closed', !isOpen);
            el.classList.toggle('nav-open', isOpen);
        }, { immediate: true });
    });
});

function toggle() {
    isOpen.value = !isOpen.value;
    localStorage.setItem(localStorageKey, isOpen.value ? 'open' : 'closed');
}

Statamic.$keys.bind(['command+\\'], (e) => {
    e.preventDefault();
    toggle();
});
</script>

<template>
    <nav class="nav-main">
        <div v-for="(section, i) in nav" :key="i">
            <div
                class="section-title"
                v-if="section.display !== 'Top Level'"
                v-text="section.display"
            />
            <ul>
                <li v-for="(item, i) in section.items" :key="i">
                    <DynamicHtmlRenderer v-if="item.view" :html="item.view" />
                    <template v-else>
                        <Link
                            :href="item.url"
                            v-bind="item.attributes"
                            :class="{ 'active': item.active }"
                            @click="setParentActive(item)"
                        >
                            <Icon :name="item.icon" />
                            <span v-text="item.display" />
                        </Link>
                        <ul v-if="item.children.length && item.active">
                            <li v-for="(child, i) in item.children" :key="i">
                                <Link
                                    :href="child.url"
                                    v-bind="child.attributes"
                                    v-text="child.display"
                                    :class="{ 'active': child.active }"
                                    @click="setChildActive(item, child)"
                                />
                            </li>
                        </ul>
                    </template>
                </li>
            </ul>
        </div>
    </nav>
</template>
