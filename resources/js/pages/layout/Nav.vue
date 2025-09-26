<script setup>
import { Link } from '@inertiajs/vue3';
import { Icon } from '@ui';

const nav = Statamic.$config.get('nav');

function unsetActiveItem() {
    nav.forEach(section => {
        section.items.forEach(item => {
            item.active = false;
            item.children.forEach(child => child.active = false);
        });
    });
}

function setParentActive(parent) {
    unsetActiveItem();
    parent.active = true;
}

function setChildActive(parent, child) {
    setParentActive(parent);
    child.active = true;
}
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
                </li>
            </ul>
        </div>
    </nav>
</template>
