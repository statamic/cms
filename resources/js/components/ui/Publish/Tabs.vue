<script setup>
import {
    Tabs,
    TabList,
    TabTrigger,
    TabProvider,
} from '@ui';
import TabContent from './TabContent.vue';
import { injectContainerContext } from './Container.vue';
import Sections from './Sections.vue';
import { ref, computed, useSlots, onMounted, watch } from 'vue';
import ElementContainer from '@/components/ElementContainer.vue';
import ShowField from '@/components/field-conditions/ShowField.js';

const slots = useSlots();
const { blueprint, visibleValues, extraValues, revealerValues, errors, hiddenFields, setHiddenField, container, rememberTab } = injectContainerContext();
const tabs = ref(blueprint.value.tabs);
const width = ref(null);
const sidebarTab = computed(() => tabs.value.find((tab) => tab.handle === 'sidebar'));
const mainTabs = computed(() =>
    shouldShowSidebar.value && sidebarTab.value ? tabs.value.filter((tab) => tab.handle !== 'sidebar') : tabs.value,
);
const visibleMainTabs = computed(() => {
    return mainTabs.value.filter((tab) => {
        return tab.sections.some((section) => {
            return section.fields.some((field) => {
                return new ShowField(
                    visibleValues.value,
                    extraValues.value,
                    visibleValues.value,
                    revealerValues.value,
                    hiddenFields.value,
                    setHiddenField,
                    { container }
                ).showField(field, field.handle);
            });
        });
    });
});
const hasMultipleVisibleMainTabs = computed(() => visibleMainTabs.value.length > 1);
const shouldShowSidebar = computed(() => (slots.actions || sidebarTab.value) && width.value > 920);
const activeTab = ref(visibleMainTabs.value[0].handle);

onMounted(() => setActiveTabFromHash());

function setActive(tab) {
    if (visibleMainTabs.value.some((t) => t.handle === tab)) {
        activeTab.value = tab;
    } else {
        activeTab.value = visibleMainTabs.value[0].handle;
    }
}

function setActiveTabFromHash() {
    if (!rememberTab.value) return;

    if (window.location.hash.length === 0) return;

    setActive(window.location.hash.substr(1));
}

watch(
    () => activeTab.value,
    (tab) => {
        if (rememberTab.value) window.location.hash = tab
    }
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
            Object.keys(errors.value)
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
        <div>
            <Tabs v-if="width" v-model:modelValue="activeTab">
                <TabList v-if="hasMultipleVisibleMainTabs" class="-mt-2 mb-6">
                    <TabTrigger
                        v-for="tab in visibleMainTabs"
                        :key="tab.handle"
                        :name="tab.handle"
                        :text="__(tab.display)"
                        :class="{ '!text-red-600': tabHasError(tab) }"
                    />
                </TabList>

                <div :class="{ 'grid grid-cols-[1fr_320px] gap-8': shouldShowSidebar }">
                    <component
                        v-for="tab in mainTabs"
                        :key="tab.handle"
                        :name="tab.handle"
                        :is="hasMultipleVisibleMainTabs ? TabContent : 'div'"
                        :force-mount="hasMultipleVisibleMainTabs ? true : null"
                        :class="{ 'hidden': tab.handle !== activeTab }"
                        @revealed="setActive(tab.handle)"
                    >
                        <TabProvider :tab="tab">
                            <slot :tab="tab">
                                <Sections />
                            </slot>

                            <slot v-if="!shouldShowSidebar" name="actions" />
                        </TabProvider>
                    </component>

                    <aside class="space-y-6 starting-style-transition-children" v-if="shouldShowSidebar">
                        <slot name="actions" />
                        <TabProvider v-if="sidebarTab" :tab="sidebarTab">
                            <Sections />
                        </TabProvider>
                    </aside>
                </div>
            </Tabs>
        </div>
    </ElementContainer>
</template>
