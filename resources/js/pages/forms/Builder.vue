<script setup>
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';
import LogicFlowMock from './LogicFlowMock.vue';
import TableFieldtype from '@/components/fieldtypes/TableFieldtype.vue';
import { Button, Card, Checkbox, CheckboxGroup, Field, Header, Heading, Icon, Input, Label, Panel, PanelHeader, Radio, RadioGroup, Select, StatusIndicator, Switch, Textarea, Tabs, TabList, TabTrigger, TabContent, ToggleGroup, ToggleItem } from '@ui';
import LayoutPanel from '@/pages/layout/LayoutPanel.vue';
import WidthSelector from '@/components/fields/WidthSelector.vue';
import { computed, ref } from 'vue';
import { mapValues } from 'lodash-es';
import fuzzysort from 'fuzzysort';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

const props = defineProps({
    form: Object,
    fieldtypes: Array,
});

const search = ref('');
const isSearching = computed(() => search.value.length > 0);

const categories = {
    structure: {
        title: __('Structure'),
        color: 'bg-purple-500',
    },
    information: {
        title: __('Information'),
        color: 'bg-pink-500',
    },
    text: {
        title: __('Text'),
        color: 'bg-purple-500',
    },
    choice: {
        title: __('Choice'),
        color: 'bg-orange-500',
    },
    rate: {
        title: __('Rate'),
        color: 'bg-amber-500',
    },
    contact: {
        title: __('Contact Info'),
        color: 'bg-blue-500',
    },
    number: {
        title: __('Number'),
        color: 'bg-teal-500',
    },
    datetime: {
        title: __('Date and Time'),
        color: 'bg-fuchsia-500',
    },
    media: {
        title: __('Media'),
        color: 'bg-cyan-500',
    },
    payment: {
        title: __('Payment'),
        color: 'bg-green-500',
    },
};

const allFieldtypes = computed(() => {
    let options = [...props.fieldtypes];

    options.push({
        handle: 'section',
        title: __('Section'),
        categories: ['structure'],
        keywords: [],
        icon: 'add-section',
        config: [],
    });

    // TODO: Only show this when Forms Pro is installed
    options.push({
        handle: 'page_break',
        title: __('Page Break'),
        categories: ['structure'],
        keywords: [],
        icon: 'page',
        config: [],
    });

    return options;
});

const groupedFieldtypes = computed(() => {
    return mapValues(categories, (category, handle) => {
        category.handle = handle;
        category.fieldtypes = [];

        allFieldtypes.value.forEach((fieldtype) => {
            let categories = fieldtype.categories;
            if (categories.length === 0) categories = ['other'];
            if (categories.includes(handle)) category.fieldtypes.push(fieldtype);
        });

        return category;
    });
});

const searchFieldtypes = computed(() => {
    let options = allFieldtypes.value;

    if (search.value) {
        return fuzzysort
            .go(search.value, options, {
                all: true,
                keys: ['title', (obj) => obj.categories?.join(), (obj) => obj.keywords?.join()],
                scoreFn: (scores) => {
                    const textScore = scores[0]?.score * 1;
                    const categoriesScore = scores[1]?.score * 0.1;
                    const keywordsScore = scores[2]?.score * 0.4;
                    return Math.max(textScore, categoriesScore, keywordsScore);
                },
            })
            .map((result) => result.obj);
    }

    return options;
});

const displayedFieldtypes = computed(() => isSearching.value ? [{ fieldtypes: searchFieldtypes.value }] : groupedFieldtypes.value);



// TODO: Refactor everything below this line
const formTitle = computed(() => props.form?.title || __('Untitled Form'));
const formPageTotal = 1;
const activeFieldSettingsTab = ref('settings');
const activePageSettingsTab = ref('settings');
const inspectorTarget = ref('field');
const pageOneInternalName = ref('');
const pageTwoInternalName = ref('Goodbye');
const age = ref(null);
const fanLength = ref('');
const heardAboutValue = ref(null);
const favoriteThing = ref('');
const favoriteAlbum = ref(null);
const secondFavoriteAlbum = ref(null);
const emailNotifications = ref([]);
const wantsFreeDrinkVoucher = ref(false);
const editingFieldWidth = ref(100);
const settingsLabel = ref(__('Which album was your favorite?'));
const settingsHelpText = ref('');
const settingsPlaceholder = ref('');
const settingsCharacterLimit = ref(null);
const fieldView = ref('expanded');
const panelCollapsed = ref(false);
const introSectionCollapsed = ref(false);
const introFanName = ref('');
const introCity = ref('');
const introSeenLive = ref(null);
const postPageSectionCollapsed = ref(false);
const postPageEmail = ref('');
const postPageFinalNote = ref('');
const postPageContactMethod = ref(null);
const postPageBestTime = ref(null);
const postPageSmsUpdates = ref(false);
const nextPageButtonLabel = ref(__('Next Page'));
const previousPageButtonLabel = ref(__('Previous Page'));
const submitButtonLabel = ref(__('Submit'));
const totalFieldCount = computed(() => 4);
const introSeenLiveOptions = [
    { label: __('Yes'), value: 'yes' },
    { label: __('Not yet'), value: 'no' },
    { label: __('Planning my first'), value: 'planning' },
];
const heardAboutOptions = [
    { label: __('Instagram'), value: 'instagram' },
    { label: __('Friend referral'), value: 'referral' },
    { label: __('Google search'), value: 'google' },
];
const albumOptions = [
    { label: __('Days of Thunder'), value: 'days_of_thunder' },
    { label: __('Endless Summer'), value: 'endless_summer' },
    { label: __('Nocturnal'), value: 'nocturnal' },
    { label: __('Kids'), value: 'kids' },
    { label: __('Monsters'), value: 'monsters' },
    { label: __('Heroes'), value: 'heroes' },
    { label: __('Red, White, and Bruised: The Midnight Live'), value: 'red_white_and_bruised' },
];
const optionRows = ref(albumOptions.map((option) => ({
    option_value: option.value,
    cells: [option.label],
    hidden: false,
})));
const visibleAlbumOptions = computed(() => optionRows.value
    .filter((option) => !option.hidden)
    .map((option, index) => ({
        label: option.cells?.[0] || '',
        value: option.option_value || `option_${index + 1}`,
    })));
const optionRowsConfig = {
    max_columns: 1,
    max_rows: 20,
    show_header: false,
    show_add_column: false,
    add_row_text: __('Add Option'),
    show_hide_toggle: true,
};
const notificationOptions = [
    { label: __('New Singles and Albums'), value: 'singles_and_albums' },
    { label: __('Merchandise'), value: 'merchandise' },
    { label: __('Friends of The Midnight'), value: 'friends_of_the_midnight' },
];
const postPageContactMethodOptions = [
    { label: __('Email'), value: 'email' },
    { label: __('SMS'), value: 'sms' },
    { label: __('Either is fine'), value: 'either' },
];
const postPageBestTimeOptions = [
    { label: __('Morning'), value: 'morning' },
    { label: __('Afternoon'), value: 'afternoon' },
    { label: __('Evening'), value: 'evening' },
];
const isPageInspector = computed(() => inspectorTarget.value === 'page_1' || inspectorTarget.value === 'page_2');
const isActionInspector = computed(() => inspectorTarget.value === 'action_next' || inspectorTarget.value === 'action_submit');
const activeSettingsTab = computed({
    get() {
        return isPageInspector.value ? activePageSettingsTab.value : activeFieldSettingsTab.value;
    },
    set(value) {
        if (isPageInspector.value) {
            activePageSettingsTab.value = value;
            return;
        }

        activeFieldSettingsTab.value = value;
    },
});
const selectedActionButtonHeading = computed(() => (
    inspectorTarget.value === 'action_submit'
        ? __('Submit button')
        : __('Next Page button')
));
const selectedActionButtonLabel = computed({
    get() {
        return inspectorTarget.value === 'action_submit' ? submitButtonLabel.value : nextPageButtonLabel.value;
    },
    set(value) {
        if (inspectorTarget.value === 'action_submit') {
            submitButtonLabel.value = value;
            return;
        }

        nextPageButtonLabel.value = value;
    },
});
const selectedActionPrimaryLabel = computed(() => (
    inspectorTarget.value === 'action_submit'
        ? __('Submit Button Label')
        : __('Next Button Label')
));
const selectedActionButtonAnchor = computed(() => (
    inspectorTarget.value === 'action_submit' ? '#action-submit-button' : '#action-next-button'
));

const inspectActionButton = (target) => {
    inspectorTarget.value = target;
    activeFieldSettingsTab.value = 'settings';
    activePageSettingsTab.value = 'settings';
};
const selectedPageAnchor = computed(() => (inspectorTarget.value === 'page_2' ? '#form-page-2' : '#form-page-1'));
const selectedPageHeadingLabel = computed(() => {
    if (inspectorTarget.value === 'page_1') return __('Page :current of :total', { current: 1, total: formPageTotal });
    if (inspectorTarget.value === 'page_2') return __('Goodbye');
    return __('Page');
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
</script>

<template>

    <Teleport to="#form-layout-actions">
        <Button variant="primary" :aria-label="__('Save')">
            <Icon name="save" class="sm:hidden" />
            <span class="hidden sm:inline">{{ __('Save') }}</span>
        </Button>
    </Teleport>

    <Button
        class="
            min-[1000px]:hidden sticky top-3 mt-3 z-(--z-index-above)
            sm:-translate-x-3 md:-translate-x-9 col-start-1 row-start-1
        "
        popovertarget="popover-left-panel"
        :text="__('Form Builder')"
        icon="bar-sidebar-left-panel-open"
    />

    <LayoutPanel side="left">
        <div
            style="--graph-paper-y-offset: 4.5rem;"
            class="bg-graph-paper [&_button]:w-full [&_button>div]:truncate [&_button>div]:block [&_button]:rounded-xl [&_button]:font-normal [&_button]:justify-start [&_button]:h-9 [&_button_svg]:size-3.5"
        >
            <div class="left-panel-popover min-[1000px]:hidden">
                <div id="popover-left-panel" class="left-panel-popover__menu" popover>
                    <button class="left-panel-popover__close-button" title="Close" popovertarget="popover-left-panel">
                        <svg height="100pt" aria-hidden="true" viewBox="0 0 100 100" width="100pt" xmlns="http://www.w3.org/2000/svg"><path d="m91.668 13.676-5.3398-5.3398-36.328 36.324-36.328-36.324-5.3398 5.3398 36.328 36.324-36.328 36.324 5.3398 5.3398 36.328-36.324 36.328 36.324 5.3398-5.3398-36.328-36.324z"/></svg>
                    </button>
                    <ul style="--graph-paper-y-offset: 4.5rem;" class="bg-graph-paper px-0.5 grid gap-8 @container py-10 pb-40">
                        <li
                            v-for="group in displayedFieldtypes"
                            :key="group.handle"
                            v-show="group.fieldtypes.length > 0"
                        >
                            <h2
                                v-if="group.title"
                                class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium"
                                :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'"
                            >
                                <span
                                    class="h-2 shrink-0 rounded-full"
                                    :class="{
                                        [group.color]: true,
                                        'w-2 opacity-100': fieldView === 'collapsed',
                                        'w-0 opacity-0': fieldView === 'expanded',
                                    }"
                                    aria-hidden="true"
                                />
                                {{ group.title }}
                            </h2>
                            <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                                <li v-for="fieldtype in group.fieldtypes" :key="fieldtype.handle">
                                    <Button :text="__(fieldtype.title)" :title="__(fieldtype.title)" :icon="fieldtype.icon" />
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- This is the desktop nav - the content is repeated from the left panel -->
            <div class="px-0.5 pt-6 max-[1000px]:hidden">
                <Input icon="magnifying-glass" :placeholder="__('Search Field Types...')" v-model="search" />

                <ul class="py-10 grid gap-8 @container">
                    <li
                        v-for="group in displayedFieldtypes"
                        :key="group.handle"
                        v-show="group.fieldtypes.length > 0"
                    >
                        <h2
                            v-if="group.title"
                            class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium"
                            :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'"
                        >
                        <span
                            class="h-2 shrink-0 rounded-full"
                            :class="{
                                [group.color]: true,
                                'w-2 opacity-100': fieldView === 'collapsed',
                                'w-0 opacity-0': fieldView === 'expanded',
                            }"
                            aria-hidden="true"
                        />
                            {{ group.title }}
                        </h2>
                        <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                            <li v-for="fieldtype in group.fieldtypes" :key="fieldtype.handle">
                                <Button :text="__(fieldtype.title)" :title="__(fieldtype.title)" :icon="fieldtype.icon" />
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </LayoutPanel>

    <div class="col-span-full row-start-1 max-[1000px]:pt-14">
        <Header class="mx-auto max-w-5xl">
            <template #title>
                <StatusIndicator status="published" />
                {{ formTitle }}
            </template>
            <template #actions>
                <ToggleGroup v-model="fieldView" size="xs">
                    <ToggleItem
                        value="expanded"
                        icon="expand"
                        :aria-label="__('Expanded view')"
                        v-tooltip="__('Expanded view')"
                    />
                    <ToggleItem
                        value="collapsed"
                        icon="collapse"
                        :aria-label="__('Collapsed view')"
                        v-tooltip="__('Collapsed view')"
                    />
                </ToggleGroup>
            </template>
        </Header>

        <div
            id="form-page-1"
            class="mx-auto max-w-5xl max-[600px]:px-5 px-5.75 sm:px-6.25 mb-4 -mt-2"
            role="button"
            tabindex="0"
            :aria-label="__('Page :current of :total', { current: 1, total: formPageTotal })"
            data-form-page-label
            data-form-page="1"
            @click="inspectorTarget = 'page_1'"
            @keydown.enter.prevent="inspectorTarget = 'page_1'"
            @keydown.space.prevent="inspectorTarget = 'page_1'"
        >
            <div class="flex items-center gap-4 cursor-pointer">
                <div class="flex items-center gap-2 flex-1">
                    <div class="h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true" />
                    <span v-tooltip="__('Logic attached')">
                        <Icon data-logic-attached name="logic-tree" class="size-3.5! shrink-0 text-gray-400 dark:text-gray-600" aria-hidden="true" />
                    </span>
                </div>
                <div
                    class="flex shrink-0 items-center gap-2 rounded-xl border border-dashed border-gray-300 px-3.5 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200 scroll-mt-[7rem]"
                    :data-editing-item="inspectorTarget === 'page_1' ? '' : undefined"
                    :class="inspectorTarget === 'page_1' ? 'bg-blue-50 border-blue-400! dark:bg-blue-950 dark:border-blue-700!' : ''"
                >
                    <Icon name="page" class="size-4 shrink-0 text-gray-500 dark:text-gray-400" aria-hidden="true" />
                    {{ __('Page :current of :total', { current: 1, total: formPageTotal }) }}
                </div>
                <div class="h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true" />
            </div>
        </div>

        <Panel
            class="mx-auto max-w-5xl"
            :class="{ 'pb-0': panelCollapsed }"
            :data-panel-collapsed="panelCollapsed ? 'true' : 'false'"
        >
            <PanelHeader class="relative flex items-center justify-between">
                <Heading :text="__('Main Section')" />
                <Button
                    @click="panelCollapsed = !panelCollapsed"
                    class="static! [&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                    :icon="panelCollapsed ? 'expand' : 'collapse'"
                    size="sm"
                    variant="ghost"
                    :aria-label="__('Toggle section visibility')"
                />
            </PanelHeader>

            <div
                style="--tw-ease: ease;"
                class="h-auto visible transition-[height,visibility] duration-[250ms,2s]"
                :class="{ 'h-0! invisible! overflow-clip': panelCollapsed }"
            >
                <Card>
                    <div class="space-y-7" :data-fields-collapsed="fieldView === 'collapsed' ? 'true' : null">
                        <div data-fieldset-group class="space-y-7">
                            <div id="fieldset-start">
                                <span data-fieldset-label class="inline-flex gap-1.75 rounded-md font-mono text-2xs text-indigo-800">
                                    <span class="inline-flex" v-tooltip="__('Fieldset')">
                                        <Icon name="link" class="size-3.5" aria-hidden="true" />
                                    </span>
                                    <span class="sr-only">{{ __('Fieldset') }}</span>
                                </span>
                                <Field :label="__('What do you like most about our band?')">
                                    <template #label>
                                        <Label for="favorite-thing-field">
                                            <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                                <Icon name="text-long" data-collapsed-field-icon class="size-3.5 me-1 text-purple-500 dark:text-purple-400" aria-hidden="true" />
                                                {{ __('What do you like most about our band?') }}
                                                <span class="relative -top-px -ms-0.5 text-red-600" :aria-label="__('Required')">*</span>
                                            </span>
                                        </Label>
                                    </template>
                                    <span class="absolute z-(--z-index-above) top-1 max-sm:-right-2 sm:-left-14" v-tooltip="__('Logic attached')">
                                        <Icon data-logic-attached name="logic-tree" class="size-3.5! text-gray-400 dark:text-gray-600" aria-hidden="true" />
                                    </span>
                                    <Textarea id="favorite-thing-field" v-model="favoriteThing" :rows="4" resize="vertical" required />
                                </Field>
                            </div>

                            <div id="fieldset-end">
                                <Field :label="__('How long have you been a fan?')" :instructions="__('If you don\'t remember, just give your best estimate')">
                                    <template #label>
                                        <Label for="fan-length-field">
                                            <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                                <Icon name="text-short" data-collapsed-field-icon class="size-3.5 me-1 text-purple-500 dark:text-purple-400" aria-hidden="true" />
                                                {{ __('How long have you been a fan?') }}
                                            </span>
                                        </Label>
                                    </template>
                                    <span class="absolute z-(--z-index-above) top-1 max-sm:-right-2 sm:-left-14" v-tooltip="__('Logic attached')">
                                        <Icon data-logic-attached name="logic-tree" class="size-3.5! text-gray-400 dark:text-gray-600" aria-hidden="true" />
                                    </span>
                                    <Input id="fan-length-field" v-model="fanLength" />
                                </Field>
                            </div>
                        </div>

                        <div
                            id="editing-field"
                            :data-editing-field="isPageInspector || isActionInspector ? undefined : ''"
                            :data-editing-item="isPageInspector || isActionInspector ? undefined : ''"
                            @click="inspectorTarget = 'field'"
                        >
                            <div
                                v-if="!isPageInspector && !isActionInspector"
                                class="!absolute z-(--z-index-above) -top-0.5 end-0.5 flex items-center"
                            >
                                <WidthSelector
                                    v-model="editingFieldWidth"
                                    size="base"
                                    variant="filled"
                                    class="me-2 bg-blue-50! border-blue-300! dark:bg-blue-950/40! dark:border-blue-600! [&_[data-state]]:!border-blue-200 dark:[&_[data-state]]:!border-blue-700 [&_[data-state='selected']]:bg-blue-100! [&_[data-state='selected'][data-last='false']]:!border-blue-100 [&_[data-last='true']]:!border-blue-300 dark:[&_[data-state='selected']]:bg-blue-900! dark:[&_[data-state='selected'][data-last='false']]:!border-blue-900 dark:[&_[data-last='true']]:!border-blue-600"
                                />
                                <Button
                                    size="sm"
                                    inset
                                    icon="duplicate"
                                    variant="subtle"
                                    :aria-label="__('Duplicate field')"
                                    :title="__('Duplicate field')"
                                    class="[&_svg]:opacity-45"
                                />
                                <Button
                                    size="sm"
                                    inset
                                    icon="eye"
                                    variant="subtle"
                                    :aria-label="__('Hide field')"
                                    :title="__('Hide field')"
                                    class="[&_svg]:opacity-45"
                                />
                                <Button
                                    size="sm"
                                    inset
                                    icon="trash"
                                    variant="subtle"
                                    :aria-label="__('Remove field')"
                                    :title="__('Remove field')"
                                    class="[&_svg]:opacity-45"
                                />
                            </div>
                            <Field :label="__('Which album was your favorite?')">
                                <template #label>
                                    <Label>
                                        <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <Icon name="fieldtype-radio" data-collapsed-field-icon class="size-3.5 me-1 text-orange-600 dark:text-orange-400" aria-hidden="true" />
                                            {{ __('Which album was your favorite?') }}
                                        </span>
                                    </Label>
                                </template>
                                <RadioGroup v-model="favoriteAlbum">
                                    <Radio
                                        v-for="album in visibleAlbumOptions"
                                        :key="album.value"
                                        :value="album.value"
                                        :label="album.label"
                                    />
                                </RadioGroup>
                            </Field>
                        </div>

                        <Field id="age-field" class="opacity-60" :label="__('How old are you?')">
                            <template #label>
                                <Label for="age-field">
                                    <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <Icon name="number" data-collapsed-field-icon class="size-3.5 me-1 text-teal-600 dark:text-teal-400" aria-hidden="true" />
                                        {{ __('How old are you?') }}
                                        <Icon name="eye-closed" class="size-3.5! text-gray-400 dark:text-gray-500" :aria-label="__('Hidden')" v-tooltip="__('Hidden')" />
                                    </span>
                                </Label>
                            </template>
                            <Input id="age-field" v-model="age" type="number" />
                        </Field>

                        <div
                            id="action-next-button"
                            class="mt-8"
                        >
                            <Button
                                variant="primary"
                                @click.prevent="inspectActionButton('action_next')"
                                :data-editing-field="inspectorTarget === 'action_next' ? '' : undefined"
                                :data-editing-item="inspectorTarget === 'action_next' ? '' : undefined"
                                class="border-0! dark:border-0! ring-0! shadow-none!"
                                style="--theme-color-primary: var(--theme-color-gray-950)"
                                :text="nextPageButtonLabel"
                            />
                        </div>
                    </div>
                </Card>
            </div>
        </Panel>

        <p
            v-if="fieldView === 'collapsed'"
            class="mx-auto text-center max-w-5xl max-[600px]:p-5 px-5.75 sm:px-6.25 mb-5 text-sm text-gray-600 dark:text-gray-300"
        >
            <strong>{{ totalFieldCount }}</strong> {{ __n('field on this form|fields on this form', totalFieldCount) }}
        </p>
    </div>

    <Button
        class="
        min-[1000px]:hidden sticky top-3 mt-3 z-(--z-index-above)
        sm:translate-x-3 md:translate-x-9 mb-5 col-start-3 row-start-1"
        popovertarget="popover-right-panel"
        :text="__('Settings')"
        icon="cog"
    />

    <LayoutPanel side="right">
        <div class="right-panel-popover min-[1000px]:hidden">
            <div id="popover-right-panel" class="right-panel-popover__menu" popover>
                <button class="right-panel-popover__close-button" title="Close" popovertarget="popover-right-panel">
                    <svg height="100pt" aria-hidden="true" viewBox="0 0 100 100" width="100pt" xmlns="http://www.w3.org/2000/svg"><path d="m91.668 13.676-5.3398-5.3398-36.328 36.324-36.328-36.324-5.3398 5.3398 36.328 36.324-36.328 36.324 5.3398 5.3398 36.328-36.324 36.328 36.324 5.3398-5.3398-36.328-36.324z"/></svg>
                </button>
                <div class="@container pt-6 pb-40 px-2.5">
                    <Tabs v-model:modelValue="activeSettingsTab" :unmount-on-hide="false">
                        <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">
                            <TabTrigger name="settings" :text="__('Settings')" />
                            <TabTrigger v-if="!isActionInspector" name="conditions" :text="__('Logic')" />
                            <TabTrigger v-if="!isPageInspector && !isActionInspector" name="validation" :text="__('Validation')" />
                        </TabList>

                        <TabContent name="settings">
                            <div v-if="isPageInspector" class="group/logic-tab space-y-6 pt-8">
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
                            <div v-else-if="isActionInspector" class="space-y-6 pt-8">
                                <div class="flex items-center gap-2.5">
                                    <div class="size-4">
                                        <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                                    </div>
                                    <a :href="selectedActionButtonAnchor" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                        {{ selectedActionButtonHeading }}
                                        <div class="grid *:[grid-area:1/1]">
                                            <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                            <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                        </div>
                                    </a>
                                </div>
                                <Field :label="selectedActionPrimaryLabel">
                                    <Input v-model="selectedActionButtonLabel" />
                                </Field>
                                <Field v-if="inspectorTarget === 'action_next'" :label="__('Previous Button Label')">
                                    <Input v-model="previousPageButtonLabel" />
                                </Field>
                            </div>
                            <div v-else class="space-y-6 pt-8">
                                <div class="flex items-center gap-2.5">
                                    <div class="size-4">
                                        <Icon name="fieldtype-radio" class="size-4 text-gray-500 dark:text-gray-300" />
                                    </div>
                                    <a href="#editing-field" class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">
                                        <span class="truncate">{{ settingsLabel }}</span>
                                        <div class="grid *:[grid-area:1/1]">
                                            <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                            <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                        </div>
                                    </a>
                                </div>

                                <Field :label="__('Label')">
                                    <Input v-model="settingsLabel" />
                                </Field>

                                <Field :label="__('Help Text')" :instructions="__('Additional field instructions like this.')">
                                    <Textarea v-model="settingsHelpText" :rows="2" resize="vertical" />
                                </Field>

                                <Field :label="__('Placeholder')">
                                    <Input v-model="settingsPlaceholder" />
                                </Field>

                                <Field :label="__('Character Limit')" :instructions="__('Set the recommended maximum number of enterable characters.')">
                                    <Input v-model="settingsCharacterLimit" type="number" />
                                </Field>

                                <Field :label="__('Options')">
                                    <TableFieldtype
                                        id="field-options-mobile"
                                        handle="options"
                                        v-model:value="optionRows"
                                        :config="optionRowsConfig"
                                    />
                                </Field>
                            </div>
                        </TabContent>
                        <TabContent v-if="!isActionInspector" name="conditions">
                            <div v-if="isPageInspector" class="group/logic-tab space-y-6 pt-8">
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
                                <div v-if="inspectorTarget === 'page_2'" class="my-8 test border-t border-dashed border-gray-400 dark:border-gray-700"></div>
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
                            <div v-else class="space-y-6 pt-8">
                                <div class="flex items-center gap-2.5">
                                    <div class="size-4">
                                        <Icon name="fieldtype-radio" class="size-4 text-gray-500 dark:text-gray-300" />
                                    </div>
                                    <a href="#editing-field" class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">
                                        <span class="truncate">{{ settingsLabel }}</span>
                                        <div class="grid *:[grid-area:1/1]">
                                            <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                            <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                        </div>
                                    </a>
                                </div>

                                <div class="space-y-4">
                                    <LogicFlowMock :use-when-selector="true" />
                                    <Button size="sm" variant="subtle" class="ms-4 bg-transparent!" :text="__('+ Add Condition')" />
                                </div>
                            </div>
                        </TabContent>
                        <TabContent v-if="!isPageInspector && !isActionInspector" name="validation">
                            <p class="text-sm text-gray-700 dark:text-gray-200">{{ __('Validation') }}</p>
                        </TabContent>
                    </Tabs>
                </div>
            </div>
        </div>

        <!-- This is the desktop nav - the content is repeated from the right panel -->
        <div class="@container relative pt-6 pb-12 px-2.5 pe-4.5 max-[1000px]:hidden">
            <Tabs v-model:modelValue="activeSettingsTab" :unmount-on-hide="false">
                <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">
                    <TabTrigger name="settings" :text="__('Settings')" />
                    <TabTrigger v-if="!isActionInspector" name="conditions" :text="__('Logic')" />
                    <TabTrigger v-if="!isPageInspector && !isActionInspector" name="validation" :text="__('Validation')" />
                </TabList>

                <TabContent name="settings">
                    <div v-if="isPageInspector" class="group/logic-tab space-y-6 pt-8">
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
                    <div v-else-if="isActionInspector" class="space-y-6 pt-8">
                        <div class="flex items-center gap-2.5">
                            <div class="size-4">
                                <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a :href="selectedActionButtonAnchor" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                {{ selectedActionButtonHeading }}
                                <div class="grid *:[grid-area:1/1]">
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                </div>
                            </a>
                        </div>
                        <Field :label="selectedActionPrimaryLabel">
                            <Input v-model="selectedActionButtonLabel" />
                        </Field>
                        <Field v-if="inspectorTarget === 'action_next'" :label="__('Previous Button Label')">
                            <Input v-model="previousPageButtonLabel" />
                        </Field>
                    </div>
                    <div v-else class="space-y-6 pt-8">
                        <div data-field-settings class="flex items-center gap-2.5">
                            <div class="size-4">
                                <Icon name="fieldtype-radio" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a href="#editing-field" class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">
                                <span class="truncate">{{ settingsLabel }}</span>
                                <div class="grid *:[grid-area:1/1]">
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                </div>
                            </a>
                        </div>

                        <Field :label="__('Label')">
                            <Input v-model="settingsLabel" />
                        </Field>

                        <Field :label="__('Help Text')" :instructions="__('Additional field instructions like this.')">
                            <Textarea v-model="settingsHelpText" :rows="2" resize="vertical" />
                        </Field>

                        <Field :label="__('Placeholder')">
                            <Input v-model="settingsPlaceholder" />
                        </Field>

                        <Field :label="__('Character Limit')" :instructions="__('Set the recommended maximum number of enterable characters.')">
                            <Input v-model="settingsCharacterLimit" type="number" />
                        </Field>

                        <Field :label="__('Options')">
                            <TableFieldtype
                                id="field-options-desktop"
                                handle="options"
                                v-model:value="optionRows"
                                :config="optionRowsConfig"
                            />
                        </Field>

                        <!-- <Field :label="__('Help Text')" :instructions="__('Additional field instructions like this.')">
                            <Textarea v-model="settingsHelpText" :rows="2" resize="vertical" />
                        </Field>

                        <Field :label="__('Placeholder')">
                            <Input v-model="settingsPlaceholder" />
                        </Field>

                        <Field :label="__('Character Limit')" :instructions="__('Set the recommended maximum number of enterable characters.')">
                            <Input v-model="settingsCharacterLimit" type="number" />
                        </Field>

                        <Field :label="__('Help Text')" :instructions="__('Additional field instructions like this.')">
                            <Textarea v-model="settingsHelpText" :rows="2" resize="vertical" />
                        </Field>

                        <Field :label="__('Placeholder')">
                            <Input v-model="settingsPlaceholder" />
                        </Field>

                        <Field :label="__('Character Limit')" :instructions="__('Set the recommended maximum number of enterable characters.')">
                            <Input v-model="settingsCharacterLimit" type="number" />
                        </Field>

                        <Field :label="__('Help Text')" :instructions="__('Additional field instructions like this.')">
                            <Textarea v-model="settingsHelpText" :rows="2" resize="vertical" />
                        </Field>

                        <Field :label="__('Placeholder')">
                            <Input v-model="settingsPlaceholder" />
                        </Field>

                        <Field :label="__('Character Limit')" :instructions="__('Set the recommended maximum number of enterable characters.')">
                            <Input v-model="settingsCharacterLimit" type="number" />
                        </Field> -->
                    </div>
                </TabContent>
                <TabContent v-if="!isActionInspector" name="conditions">
                    <div v-if="isPageInspector" class="group/logic-tab space-y-6 pt-8">
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
                    <div v-else class="space-y-6 pt-8">
                        <div data-field-settings class="flex items-center gap-2.5">
                            <div class="size-4">
                                <Icon name="fieldtype-radio" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a href="#editing-field" class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">
                                <span class="truncate">{{ settingsLabel }}</span>
                                <div class="grid *:[grid-area:1/1]">
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                </div>
                            </a>
                        </div>

                        <div class="space-y-4">
                            <LogicFlowMock :use-when-selector="true" />
                            <Button size="sm" variant="subtle" class="ms-4 bg-transparent!" :text="__('+ Add Condition')" />
                        </div>
                    </div>
                </TabContent>
                <TabContent v-if="!isPageInspector && !isActionInspector" name="validation">
                    <p class="text-sm text-gray-700 dark:text-gray-200">{{ __('Validation') }}</p>
                </TabContent>
            </Tabs>
        </div>
    </LayoutPanel>
</template>
