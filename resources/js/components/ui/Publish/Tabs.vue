<script setup>
import { Tabs, TabList, TabTrigger, TabContent } from '@statamic/ui';
import TabProvider from './TabProvider.vue';
import { injectContainerContext } from './Container.vue';
import Sections from '@statamic/components/ui/Publish/Sections.vue';
import { ref, computed, useSlots, onMounted, watch } from 'vue';
import ElementContainer from '@statamic/components/ElementContainer.vue';
import ShowField from '@statamic/components/field-conditions/ShowField.js';

const slots = useSlots();
const { blueprint, store } = injectContainerContext();
const tabs = ref(blueprint.tabs);
const width = ref(null);
const sidebarTab = computed(() => tabs.value.find((tab) => tab.handle === 'sidebar'));
const mainTabs = computed(() =>
    shouldShowSidebar.value && sidebarTab.value ? tabs.value.filter((tab) => tab.handle !== 'sidebar') : tabs.value,
);
const visibleMainTabs = computed(() => {
    return mainTabs.value.filter((tab) => {
        return tab.sections.some((section) => {
            return section.fields.some((field) => {
                return new ShowField(store, store.values, store.extraValues).showField(field, field.handle);
            });
        });
    });
});
const shouldShowSidebar = computed(() => (slots.sidebar || sidebarTab.value) && width.value > 920);
const tab = ref(visibleMainTabs.value[0].handle);

onMounted(() => setActiveTabFromHash());

function setActiveTabFromHash() {
    if (window.location.hash.length === 0) return;

    const handle = window.location.hash.substr(1);

    if (visibleMainTabs.value.some((tab) => tab.handle === handle)) {
        tab.value = handle;
    } else {
        tab.value = visibleMainTabs.value[0].handle;
    }
}

watch(
    () => tab.value,
    (tab) => window.location.hash = tab,
);

const fieldTabMap = computed(() => {
    let map = {};

    Object.values(tabs.value).forEach((tab) => {
        tab.sections.forEach((section) => {
            section.fields.forEach((field) => {
                map[field.handle] = tab.handle;
            });
        });
    });

    return map;
});

const tabsWithErrors = computed(() => {
    return [
        ...new Set(
            Object.keys(store.errors)
                .map((handle) => handle.split('.')[0])
                .filter((handle) => fieldTabMap.value[handle])
                .map((handle) => fieldTabMap.value[handle]),
        ),
    ];
});

function tabHasError(tab) {
    return tabsWithErrors.value.includes(tab.handle);
}
</script>

<template>
    <ElementContainer @resized="width = $event.width">
        <Tabs v-model:modelValue="tab">
            <TabList v-if="visibleMainTabs.length > 1" class="mb-6">
                <TabTrigger
                    v-for="tab in visibleMainTabs"
                    :key="tab.handle"
                    :name="tab.handle"
                    :text="__(tab.display)"
                    :class="{ '!text-red-500': tabHasError(tab) }"
                />
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
                    <slot name="actions" />
                    <TabProvider v-if="sidebarTab" :tab="sidebarTab">
                        <Sections />
                    </TabProvider>
                </aside>
            </div>
        </Tabs>
    </ElementContainer>
</template>
