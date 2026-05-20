<script setup lang="ts">
import LogicFlowMock from '@/pages/forms/LogicFlowMock.vue';
import { Button, Field, Icon, Input, Textarea, Tabs, TabList, TabTrigger, TabContent } from '@ui';
import { computed, ref } from 'vue';
import { injectBuilderContext } from '@/pages/forms/Builder.vue';

const { inspecting: page, pages } = injectBuilderContext();

enum PageInspectorTabs {
    Settings = 'settings',
    Conditions = 'conditions',
}

const activeTab = ref<PageInspectorTabs>(PageInspectorTabs.Settings);

const placeholderTitle = computed(() => {
    let pageIndex = pages.value.findIndex((p) => p._id === page.value._id);

    return __('Page :current of :total', { current: pageIndex + 1, total: pages.value.length });
});

// todo: refactor everything under this line
const inspectorTarget = ref('page_1');

const selectedPageLogicMockPreset = computed(() => {
    if (inspectorTarget.value === 'page_2') {
        return {
            logicBranchingConditionField: 'age',
            logicOperator: 'contains',
            logicValue: '21',
            logicBranchingAction: 'divide',
            logicBranchingCalculationSource: 'variable_score',
            logicBranchingCalculationVariable: 'engagement_weight',
            logicContainsOperator: 'contains',
            logicContainsAnswer: 'google',
        };
    }

    return {};
});

const goodbyeSecondRuleMockPreset = {
    logicBranchingConditionField: 'email_notifications',
    logicOperator: 'equals',
    logicValue: 'referral',
    logicJoin: 'or',
    logicConditionField: 'fan_length',
    logicContainsOperator: 'contains',
    logicContainsAnswer: 'friend',
    logicBranchingAction: 'go_to',
    logicDestination: 'second_favorite',
};

const selectedPageDestinationStepLabel = computed(() => (
    inspectorTarget.value === 'page_2'
        ? __('Then go to Page 1')
        : __('Then go to Goodbye')
));
</script>

<template>
    <div>
        <!-- Mobile -->
        <div class="right-panel-popover min-[1000px]:hidden">
            <div id="popover-right-panel" class="right-panel-popover__menu" popover>
                <button class="right-panel-popover__close-button" :title="__('Close')" popovertarget="popover-right-panel">
                    <svg height="100pt" aria-hidden="true" viewBox="0 0 100 100" width="100pt" xmlns="http://www.w3.org/2000/svg"><path d="m91.668 13.676-5.3398-5.3398-36.328 36.324-36.328-36.324-5.3398 5.3398 36.328 36.324-36.328 36.324 5.3398 5.3398 36.328-36.324 36.328 36.324 5.3398-5.3398-36.328-36.324z"/></svg>
                </button>
                <div class="@container pt-6 pb-40 px-2.5">
                    <Tabs v-model:modelValue="activeTab" :unmount-on-hide="false">
                <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">
                    <TabTrigger :name="PageInspectorTabs.Settings" :text="__('Settings')" />
                    <TabTrigger :name="PageInspectorTabs.Conditions" :text="__('Logic')" />
                </TabList>

                <TabContent :name="PageInspectorTabs.Settings">
                    <div class="group/logic-tab space-y-6 pt-8">
                        <div class="flex items-center gap-2.5">
                            <div class="size-4">
                                <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a :href="`#page-${page._id}`" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                {{ page.display ? __(page.display) : placeholderTitle }}
                                <div class="grid *:[grid-area:1/1]">
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                </div>
                            </a>
                        </div>

                        <Field :label="__('Label')">
                            <Input v-model="page.display" :placeholder="placeholderTitle" />
                        </Field>

                        <Field :label="__('Help Text')" :instructions="__('Additional field instructions like this.')">
                            <Textarea v-model="page.instructions" :rows="2" resize="vertical" />
                        </Field>
                    </div>
                </TabContent>

                <TabContent :name="PageInspectorTabs.Conditions">
                    <div class="group/logic-tab space-y-6 pt-8">
                        <div class="flex items-center gap-2.5">
                            <div class="size-4">
                                <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a :href="`#page-${page._id}`" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                {{ page.display ? __(page.display) : __('Page :current of :total', { current: 'FOO', total: pages.length }) }}
                                <div class="grid *:[grid-area:1/1]">
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                </div>
                            </a>
                        </div>

                        <LogicFlowMock
                            :key="`mobile-page-logic-${inspectorTarget}`"
                            :destination-step-label="selectedPageDestinationStepLabel"
                            :show-destination-selector="true"
                            :show-rule-controls="true"
                            :show-add-condition-before-then="true"
                            :use-page-destination-options="true"
                            :mock-preset="selectedPageLogicMockPreset"
                        />

                        <div v-if="inspectorTarget === 'page_2'" class="my-6 border-t border-dashed border-gray-400 dark:border-gray-700"></div>

                        <LogicFlowMock
                            v-if="inspectorTarget === 'page_2'"
                            :key="`mobile-page-logic-secondary-${inspectorTarget}`"
                            :destination-step-label="__('Then go to Page 1')"
                            :show-destination-selector="true"
                            :show-rule-controls="true"
                            :show-add-condition-before-then="true"
                            :use-page-destination-options="true"
                            :mock-preset="goodbyeSecondRuleMockPreset"
                        />

                        <div class="mt-8 mb-6 pt-4 border-t border-dashed border-gray-300 dark:border-gray-700">
                            <Button size="sm" :text="__('+ Add Rule')" />
                        </div>
                    </div>
                </TabContent>
                    </Tabs>
                </div>
            </div>
        </div>

        <!-- Desktop -->
        <div class="@container relative pt-6 pb-12 px-2.5 pe-4.5 max-[1000px]:hidden">
            <Tabs v-model:modelValue="activeTab" :unmount-on-hide="false">
                <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">
                    <TabTrigger :name="PageInspectorTabs.Settings" :text="__('Settings')" />
                    <TabTrigger :name="PageInspectorTabs.Conditions" :text="__('Logic')" />
                </TabList>

                <TabContent :name="PageInspectorTabs.Settings">
                    <div class="group/logic-tab space-y-6 pt-8">
                        <div class="flex items-center gap-2.5">
                            <div class="size-4">
                                <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a :href="`#page-${page._id}`" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                {{ page.display ? __(page.display) : placeholderTitle }}
                                <div class="grid *:[grid-area:1/1]">
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                </div>
                            </a>
                        </div>

                        <Field :label="__('Label')">
                            <Input v-model="page.display" :placeholder="placeholderTitle" />
                        </Field>

                        <Field :label="__('Help Text')" :instructions="__('Additional field instructions like this.')">
                            <Textarea v-model="page.instructions" :rows="2" resize="vertical" />
                        </Field>
                    </div>
                </TabContent>

                <TabContent :name="PageInspectorTabs.Conditions">
                    <div class="group/logic-tab space-y-6 pt-8">
                        <div class="flex items-center gap-2.5">
                            <div class="size-4">
                                <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a :href="`#page-${page._id}`" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                {{ page.display ? __(page.display) : __('Page :current of :total', { current: 'FOO', total: pages.length }) }}
                                <div class="grid *:[grid-area:1/1]">
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                </div>
                            </a>
                        </div>

                        <LogicFlowMock
                            :key="`desktop-page-logic-${inspectorTarget}`"
                            :destination-step-label="selectedPageDestinationStepLabel"
                            :show-destination-selector="true"
                            :show-rule-controls="true"
                            :show-add-condition-before-then="true"
                            :use-page-destination-options="true"
                            :mock-preset="selectedPageLogicMockPreset"
                        />

                        <div v-if="inspectorTarget === 'page_2'" class="my-6 border-t border-dashed border-gray-400 dark:border-gray-700"></div>

                        <LogicFlowMock
                            v-if="inspectorTarget === 'page_2'"
                            :key="`desktop-page-logic-secondary-${inspectorTarget}`"
                            :destination-step-label="__('Then go to Page 1')"
                            :show-destination-selector="true"
                            :show-rule-controls="true"
                            :show-add-condition-before-then="true"
                            :use-page-destination-options="true"
                            :mock-preset="goodbyeSecondRuleMockPreset"
                        />

                        <div class="mt-8 mb-6 pt-4 border-t border-dashed border-gray-300 dark:border-gray-700">
                            <Button size="sm" :text="__('+ Add Rule')" />
                        </div>
                    </div>
                </TabContent>
            </Tabs>
        </div>
    </div>
</template>
