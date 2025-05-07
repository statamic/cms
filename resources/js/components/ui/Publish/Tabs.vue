<script setup>
import { Tabs, TabList, TabTrigger, TabContent } from '@statamic/ui';
import TabProvider from './TabProvider.vue';
import { injectContainerContext } from './Container.vue';
import Sections from '@statamic/components/ui/Publish/Sections.vue';
import { computed, useSlots } from 'vue';

const slots = useSlots();
const context = injectContainerContext();
const tabs = context.blueprint.tabs;
const sidebarTab = computed(() => tabs.find((tab) => tab.handle === 'sidebar'));
const mainTabs = computed(() => (sidebarTab ? tabs.filter((tab) => tab.handle !== 'sidebar') : tabs));
const shouldShowSidebar = computed(() => slots.sidebar || sidebarTab.value);
</script>

<template>
    <Tabs :default-tab="mainTabs[0].handle">
        <TabList class="mb-6">
            <TabTrigger v-for="tab in mainTabs" :key="tab.handle" :name="tab.handle" :text="tab.display" />
        </TabList>

        <div :class="{ 'grid grid-cols-[1fr_320px] gap-8': shouldShowSidebar }">
            <TabContent v-for="tab in mainTabs" :key="tab.handle" :name="tab.handle">
                <TabProvider :tab="tab">
                    <slot :tab="tab">
                        <Sections />
                    </slot>
                </TabProvider>
            </TabContent>

            <aside class="space-y-6" v-if="shouldShowSidebar">
                <slot name="sidebar" />
                <TabProvider v-if="sidebarTab" :tab="sidebarTab">
                    <Sections />
                </TabProvider>
            </aside>
        </div>
    </Tabs>
</template>
