<script setup>
import LogicFlowMock from '@/pages/forms/LogicFlowMock.vue';
import { Button, Field, Icon, Input, Textarea, Tabs, TabList, TabTrigger, TabContent } from '@ui';
import { computed, ref } from 'vue';

const activeTab = ref('settings');

const formPageTotal = 1;
const inspectorTarget = ref('page_1');
const pageOneInternalName = ref('');
const pageTwoInternalName = ref('Goodbye');
const settingsHelpText = ref('');

const selectedPageAnchor = computed(() => (inspectorTarget.value === 'page_2' ? '#form-page-2' : '#form-page-1'));

const selectedPageHeadingLabel = computed(() => {
    if (inspectorTarget.value === 'page_1') return __('Page :current of :total', { current: 1, total: formPageTotal });
    if (inspectorTarget.value === 'page_2') return __('Goodbye');
    return __('Page');
});

const selectedPageInternalName = computed({
    get() {
        return inspectorTarget.value === 'page_2' ? pageTwoInternalName.value : pageOneInternalName.value;
    },
    set(value) {
        if (inspectorTarget.value === 'page_2') {
            pageTwoInternalName.value = value;
            return;
        }

        pageOneInternalName.value = value;
    },
});

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
        <div class="right-panel-popover min-[1000px]:hidden">
            <div id="popover-right-panel" class="right-panel-popover__menu" popover>
                <button class="right-panel-popover__close-button" title="Close" popovertarget="popover-right-panel">
                    <svg height="100pt" aria-hidden="true" viewBox="0 0 100 100" width="100pt" xmlns="http://www.w3.org/2000/svg"><path d="m91.668 13.676-5.3398-5.3398-36.328 36.324-36.328-36.324-5.3398 5.3398 36.328 36.324-36.328 36.324 5.3398 5.3398 36.328-36.324 36.328 36.324 5.3398-5.3398-36.328-36.324z"/></svg>
                </button>
                <div class="@container pt-6 pb-40 px-2.5">
                    <Tabs v-model:modelValue="activeTab" :unmount-on-hide="false">
                        <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">
                            <TabTrigger name="settings" :text="__('Settings')" />
                            <TabTrigger name="conditions" :text="__('Logic')" />
                        </TabList>

                        <TabContent name="settings">
                            <div class="group/logic-tab space-y-6 pt-8">
                                <div class="flex items-center gap-2.5">
                                    <div class="size-4">
                                        <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                                    </div>
                                    <a :href="selectedPageAnchor" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                        {{ selectedPageHeadingLabel }}
                                        <div class="grid *:[grid-area:1/1]">
                                            <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                            <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                        </div>
                                    </a>
                                </div>

                                <Field :label="__('Label')">
                                    <Input v-model="selectedPageInternalName" />
                                </Field>
                                <Field :label="__('Help Text')" :instructions="__('Additional field instructions like this.')">
                                    <Textarea v-model="settingsHelpText" :rows="2" resize="vertical" />
                                </Field>
                            </div>
                        </TabContent>

                        <TabContent name="conditions">
                            <div class="group/logic-tab space-y-6 pt-8">
                                <div class="flex items-center gap-2.5">
                                    <div class="size-4">
                                        <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                                    </div>
                                    <a :href="selectedPageAnchor" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                        {{ selectedPageHeadingLabel }}
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
                                <div v-if="inspectorTarget === 'page_2'" class="my-8 border-t border-dashed border-gray-400 dark:border-gray-700"></div>
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
                                <div class="mt-6 border-t border-gray-300 dark:border-gray-700">
                                    <Button size="sm" variant="default" class="-ms-2" :text="__('+ Add Rule')" />
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
                    <TabTrigger name="settings" :text="__('Settings')" />
                    <TabTrigger name="conditions" :text="__('Logic')" />
                </TabList>

                <TabContent name="settings">
                    <div class="group/logic-tab space-y-6 pt-8">
                        <div class="flex items-center gap-2.5">
                            <div class="size-4">
                                <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a :href="selectedPageAnchor" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                {{ selectedPageHeadingLabel }}
                                <div class="grid *:[grid-area:1/1]">
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                </div>
                            </a>
                        </div>

                        <Field :label="__('Label')">
                            <Input v-model="selectedPageInternalName" />
                        </Field>
                        <Field :label="__('Help Text')" :instructions="__('Additional field instructions like this.')">
                            <Textarea v-model="settingsHelpText" :rows="2" resize="vertical" />
                        </Field>
                    </div>
                </TabContent>

                <TabContent name="conditions">
                    <div class="group/logic-tab space-y-6 pt-8">
                        <div class="flex items-center gap-2.5">
                            <div class="flex items-center gap-2.5">
                                <div class="size-4">
                                    <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                                </div>
                                <a :href="selectedPageAnchor" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                    {{ selectedPageHeadingLabel }}
                                    <div class="grid *:[grid-area:1/1]">
                                        <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                        <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                    </div>
                                </a>
                            </div>
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
