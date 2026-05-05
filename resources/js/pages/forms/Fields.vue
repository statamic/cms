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

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

const props = defineProps({
    form: Object,
    fieldtypes: Array,
});

const formTitle = computed(() => props.form?.title || __('Untitled Form'));
const formPageTotal = 2;
const activeSettingsTab = ref('settings');
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
const totalFieldCount = computed(() => 13);
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
const isPageInspector = computed(() => inspectorTarget.value === 'page_1' || inspectorTarget.value === 'page_2');
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
        <div style="--graph-paper-y-offset: 4.5rem;" class="bg-graph-paper [&_button]:w-full [&_button>div]:truncate [&_button>div]:block [&_button]:rounded-xl [&_button]:font-normal [&_button]:justify-start [&_button]:h-9 [&_button_svg]:size-3.5">


            <div class="left-panel-popover min-[1000px]:hidden">
                <div id="popover-left-panel" class="left-panel-popover__menu" popover>
                    <button class="left-panel-popover__close-button" title="Close" popovertarget="popover-left-panel">
                        <svg height="100pt" aria-hidden="true" viewBox="0 0 100 100" width="100pt" xmlns="http://www.w3.org/2000/svg"><path d="m91.668 13.676-5.3398-5.3398-36.328 36.324-36.328-36.324-5.3398 5.3398 36.328 36.324-36.328 36.324 5.3398 5.3398 36.328-36.324 36.328 36.324 5.3398-5.3398-36.328-36.324z"/></svg>
                    </button>
                    <ul style="--graph-paper-y-offset: 4.5rem;" class="bg-graph-paper px-0.5 grid gap-8 @container py-10 pb-40">
                        <li>
                            <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                                <span class="h-2 shrink-0 rounded-full bg-pink-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                                Information
                            </h2>
                            <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                                <li>
                                    <Button :text="__('Heading')" :title="__('Heading')" icon="heading" />
                                </li>
                                <li>
                                    <Button :text="__('Paragraph')" :title="__('Paragraph')" icon="text-short" />
                                </li>
                                <li>
                                    <Button :text="__('Banner')" :title="__('Banner')" icon="banner" />
                                </li>
                                <li>
                                    <Button :text="__('Legal')" :title="__('Legal')" icon="legal" />
                                </li>
                            </ul>
                        </li>
                        <li>
                            <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                                <span class="h-2 shrink-0 rounded-full bg-purple-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                                Text
                            </h2>
                            <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                                <li>
                                    <Button :text="__('Short Answer')" :title="__('Short Answer')" icon="text-short" />
                                </li>
                                <li>
                                    <Button :text="__('Long Answer')" :title="__('Long Answer')" icon="text-long" />
                                </li>
                            </ul>
                        </li>
                        <li>
                            <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                                <span class="h-2 shrink-0 rounded-full bg-orange-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                                Choice
                            </h2>
                            <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                                <li>
                                    <Button :text="__('Dropdown')" :title="__('Dropdown')" icon="fieldtype-select" />
                                </li>
                                <li>
                                    <Button :text="__('Yes/No')" :title="__('Yes/No')" icon="like" />
                                </li>
                                <li>
                                    <Button :text="__('Multi Choice')" :title="__('Multi Choice')" icon="fieldtype-radio" />
                                </li>
                                <li>
                                    <Button :text="__('Checkboxes')" :title="__('Checkboxes')" icon="fieldtype-checkboxes" />
                                </li>
                                <li>
                                    <Button :text="__('Toggle')" :title="__('Toggle')" icon="fieldtype-toggle" />
                                </li>
                                <li>
                                    <Button :text="__('Image Choice')" :title="__('Image Choice')" icon="image-select" />
                                </li>
                            </ul>
                        </li>
                        <li>
                            <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                                <span class="h-2 shrink-0 rounded-full bg-amber-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                                Rate
                            </h2>
                            <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                                <li>
                                    <Button :text="__('Star Rating')" :title="__('Star Rating')" icon="star" />
                                </li>
                                <li>
                                    <Button :text="__('Ranking')" :title="__('Ranking')" icon="rank" />
                                </li>
                                <li>
                                    <Button :text="__('Opinion Scale')" :title="__('Opinion Scale')" icon="scale-up" />
                                </li>
                            </ul>
                        </li>
                        <li>
                            <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                                <span class="h-2 shrink-0 rounded-full bg-blue-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                                Contact Info
                            </h2>
                            <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                                <li>
                                    <Button :text="__('Name')" :title="__('Name')" icon="user-avatar-flush" />
                                </li>
                                <li>
                                    <Button :text="__('Email')" :title="__('Email')" icon="mail-sign-at" />
                                </li>
                                <li>
                                    <Button :text="__('Website')" :title="__('Website')" icon="website" />
                                </li>
                                <li>
                                    <Button :text="__('Phone')" :title="__('Phone')" icon="mail-sign-hashtag" />
                                </li>
                                <li>
                                    <Button :text="__('Address')" :title="__('Address')" icon="location-pin" />
                                </li>
                                <li>
                                    <Button :text="__('Signature')" :title="__('Signature')" icon="edit-pen-draw-scribble" />
                                </li>
                            </ul>
                        </li>
                        <li>
                            <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                                <span class="h-2 shrink-0 rounded-full bg-teal-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                                Number
                            </h2>
                            <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                                <li>
                                    <Button :text="__('Number')" :title="__('Number')" icon="number" />
                                </li>
                                <li>
                                    <Button :text="__('Currency')" :title="__('Currency')" icon="currency" />
                                </li>
                            </ul>
                        </li>
                        <li>
                            <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                                <span class="h-2 shrink-0 rounded-full bg-fuchsia-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                                Date and Time
                            </h2>
                            <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                                <li>
                                    <Button :text="__('Date Picker')" :title="__('Date Picker')" icon="calendar" />
                                </li>
                                <li>
                                    <Button :text="__('Time Picker')" :title="__('Time Picker')" icon="time-clock" />
                                </li>
                                <li>
                                    <Button :text="__('Range')" :title="__('Range')" icon="calendar-range" />
                                </li>
                                <li>
                                    <Button :text="__('SavvyCal')" :title="__('SavvyCal')" icon="calendar" />
                                </li>
                            </ul>
                        </li>
                        <li>
                            <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                                <span class="h-2 shrink-0 rounded-full bg-cyan-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                                Media
                            </h2>
                            <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                                <li>
                                    <Button :text="__('Image Choice')" :title="__('Image Choice')" icon="image-select" />
                                </li>
                                <li>
                                    <Button :text="__('Video')" :title="__('Video')" icon="fieldtype-video" />
                                </li>
                                <li>
                                    <Button :text="__('Audio')" :title="__('Audio')" icon="media-music-sound-equalizer" />
                                </li>
                                <li>
                                    <Button :text="__('Upload')" :title="__('Upload')" icon="upload-arrow-up" />
                                </li>
                            </ul>
                        </li>
                        <li>
                            <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                                <span class="h-2 shrink-0 rounded-full bg-green-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                                Payment
                            </h2>
                            <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                                <li>
                                    <Button :text="__('Stripe')" :title="__('Stripe')" icon="credit-card" />
                                </li>
                                <li>
                                    <Button :text="__('PayPal')" :title="__('PayPal')" icon="credit-card" />
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- This is the desktop nav - the content is repeated from the left panel -->
            <ul class="px-0.5 grid gap-8 @container py-10 max-[1000px]:hidden">
<!--                    <ul>-->
<!--                        <li v-for="fieldtype in fieldtypes" :key="fieldtype.handle">-->
<!--                            {{ fieldtype.title }}-->
<!--                        </li>-->
<!--                    </ul>-->
                <li>
                    <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                        <span class="h-2 shrink-0 rounded-full bg-purple-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                        Structure
                    </h2>
                    <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                        <li>
                            <Button :text="__('Section')" :title="__('Section')" icon="add-section" />
                        </li>
                        <li>
                            <Button :text="__('Page Break')" :title="__('Page Break')" icon="page" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                        <span class="h-2 shrink-0 rounded-full bg-pink-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                        Information
                    </h2>
                    <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                        <li>
                            <Button :text="__('Heading')" :title="__('Heading')" icon="heading" />
                        </li>
                        <li>
                            <Button :text="__('Paragraph')" :title="__('Paragraph')" icon="text-short" />
                        </li>
                        <li>
                            <Button :text="__('Banner')" :title="__('Banner')" icon="banner" />
                        </li>
                        <li>
                            <Button :text="__('Legal')" :title="__('Legal')" icon="legal" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                        <span class="h-2 shrink-0 rounded-full bg-purple-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                        Text
                    </h2>
                    <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                        <li>
                            <Button :text="__('Short Answer')" :title="__('Short Answer')" icon="text-short" />
                        </li>
                        <li>
                            <Button :text="__('Long Answer')" :title="__('Long Answer')" icon="text-long" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                        <span class="h-2 shrink-0 rounded-full bg-orange-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                        Choice
                    </h2>
                    <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                        <li>
                            <Button :text="__('Dropdown')" :title="__('Dropdown')" icon="fieldtype-select" />
                        </li>
                        <li>
                            <Button :text="__('Yes/No')" :title="__('Yes/No')" icon="like" />
                        </li>
                        <li>
                            <Button :text="__('Multi Choice')" :title="__('Multi Choice')" icon="fieldtype-radio" />
                        </li>
                        <li>
                            <Button :text="__('Checkboxes')" :title="__('Checkboxes')" icon="fieldtype-checkboxes" />
                        </li>
                        <li>
                            <Button :text="__('Toggle')" :title="__('Toggle')" icon="fieldtype-toggle" />
                        </li>
                        <li>
                            <Button :text="__('Image Choice')" :title="__('Image Choice')" icon="image-select" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                        <span class="h-2 shrink-0 rounded-full bg-amber-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                        Rate
                    </h2>
                    <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                        <li>
                            <Button :text="__('Star Rating')" :title="__('Star Rating')" icon="star" />
                        </li>
                        <li>
                            <Button :text="__('Ranking')" :title="__('Ranking')" icon="rank" />
                        </li>
                        <li>
                            <Button :text="__('Opinion Scale')" :title="__('Opinion Scale')" icon="scale-up" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                        <span class="h-2 shrink-0 rounded-full bg-blue-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                        Contact Info
                    </h2>
                    <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                        <li>
                            <Button :text="__('Name')" :title="__('Name')" icon="user-avatar-flush" />
                        </li>
                        <li>
                            <Button :text="__('Email')" :title="__('Email')" icon="mail-sign-at" />
                        </li>
                        <li>
                            <Button :text="__('Website')" :title="__('Website')" icon="website" />
                        </li>
                        <li>
                            <Button :text="__('Phone')" :title="__('Phone')" icon="mail-sign-hashtag" />
                        </li>
                        <li>
                            <Button :text="__('Address')" :title="__('Address')" icon="location-pin" />
                        </li>
                        <li>
                            <Button :text="__('Signature')" :title="__('Signature')" icon="edit-pen-draw-scribble" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                        <span class="h-2 shrink-0 rounded-full bg-teal-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                        Number
                    </h2>
                    <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                        <li>
                            <Button :text="__('Number')" :title="__('Number')" icon="number" />
                        </li>
                        <li>
                            <Button :text="__('Currency')" :title="__('Currency')" icon="currency" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                        <span class="h-2 shrink-0 rounded-full bg-fuchsia-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                        Date and Time
                    </h2>
                    <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                        <li>
                            <Button :text="__('Date Picker')" :title="__('Date Picker')" icon="calendar" />
                        </li>
                        <li>
                            <Button :text="__('Time Picker')" :title="__('Time Picker')" icon="time-clock" />
                        </li>
                        <li>
                            <Button :text="__('Range')" :title="__('Range')" icon="calendar-range" />
                        </li>
                        <li>
                            <Button :text="__('SavvyCal')" :title="__('SavvyCal')" icon="calendar" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                        <span class="h-2 shrink-0 rounded-full bg-cyan-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                        Media
                    </h2>
                    <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                        <li>
                            <Button :text="__('Image Choice')" :title="__('Image Choice')" icon="image-select" />
                        </li>
                        <li>
                            <Button :text="__('Video')" :title="__('Video')" icon="fieldtype-video" />
                        </li>
                        <li>
                            <Button :text="__('Audio')" :title="__('Audio')" icon="media-music-sound-equalizer" />
                        </li>
                        <li>
                            <Button :text="__('Upload')" :title="__('Upload')" icon="upload-arrow-up" />
                        </li>
                    </ul>
                </li>
                <li>
                    <h2 class="inline-flex items-center px-1.5 pb-1 text-sm text-gray-950 dark:text-gray-200 font-medium" :class="fieldView === 'collapsed' ? 'gap-1.5' : 'gap-0'">
                        <span class="h-2 shrink-0 rounded-full bg-green-500" :class="fieldView === 'collapsed' ? 'w-2 opacity-100' : 'w-0 opacity-0'" aria-hidden="true"></span>
                        Payment
                    </h2>
                    <ul class="grid gap-2 gap-y-1.75 @min-[250px]:grid-cols-2">
                        <li>
                            <Button :text="__('Stripe')" :title="__('Stripe')" icon="credit-card" />
                        </li>
                        <li>
                            <Button :text="__('PayPal')" :title="__('PayPal')" icon="credit-card" />
                        </li>
                    </ul>
                </li>
            </ul>
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
            class="mx-auto max-w-5xl max-[600px]:px-5 px-5.75 sm:px-6.25 mb-4 -mt-2"
            role="button"
            tabindex="0"
            :aria-label="__('Page :current of :total', { current: 1, total: formPageTotal })"
            data-form-page-label
            data-form-page="1"
            @click="inspectorTarget = 'page_1'; activeSettingsTab = 'settings'"
            @keydown.enter.prevent="inspectorTarget = 'page_1'; activeSettingsTab = 'settings'"
            @keydown.space.prevent="inspectorTarget = 'page_1'; activeSettingsTab = 'settings'"
        >
            <div class="flex items-center gap-4 cursor-pointer">
                <div class="h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true" />
                <div
                    class="flex shrink-0 items-center gap-2 rounded-xl border border-dashed border-gray-300 px-3.5 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200"
                    :class="inspectorTarget === 'page_1' ? 'bg-blue-50 border-blue-400!' : ''"
                >
                    <Icon name="page" class="size-4 shrink-0 text-gray-500 dark:text-gray-400" aria-hidden="true" />
                    {{ __('Page :current of :total', { current: 1, total: formPageTotal }) }}
                </div>
                <div class="h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true" />
            </div>
        </div>

        <Panel
            class="mx-auto max-w-5xl mb-6"
            :class="{ 'pb-0': introSectionCollapsed }"
            :data-panel-collapsed="introSectionCollapsed ? 'true' : 'false'"
        >
            <PanelHeader class="relative flex items-center justify-between">
                <Heading :text="__('Getting started')" />
                <Button
                    @click="introSectionCollapsed = !introSectionCollapsed"
                    class="static! [&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                    :icon="introSectionCollapsed ? 'expand' : 'collapse'"
                    size="sm"
                    variant="ghost"
                    :aria-label="__('Toggle section visibility')"
                />
            </PanelHeader>

            <div
                style="--tw-ease: ease;"
                class="h-auto visible transition-[height,visibility] duration-[250ms,2s]"
                :class="{ 'h-0! invisible! overflow-clip': introSectionCollapsed }"
            >
                <Card>
                    <div class="space-y-7" :data-fields-collapsed="fieldView === 'collapsed' ? 'true' : null">
                        <Field id="intro-name-field" :label="__('What should we call you?')" required>
                            <template #label>
                                <Label for="intro-name-field">
                                    <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <Icon name="user-avatar-flush" data-collapsed-field-icon class="size-3.5 me-1 text-blue-600 dark:text-blue-400" aria-hidden="true" />
                                        {{ __('What should we call you?') }}
                                        <span class="relative -top-px -ms-0.5 text-red-600" :aria-label="__('Required')">*</span>
                                    </span>
                                </Label>
                            </template>
                            <Input id="intro-name-field" v-model="introFanName" :placeholder="__('Your name')" />
                        </Field>

                        <Field id="intro-city-field" :label="__('Where are you joining us from?')" :instructions="__('City or region is enough (we use this for tour and timezone hints)')">
                            <template #label>
                                <Label for="intro-city-field">
                                    <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <Icon name="text-short" data-collapsed-field-icon class="size-3.5 me-1 text-purple-500 dark:text-purple-400" aria-hidden="true" />
                                        {{ __('Where are you joining us from?') }}
                                    </span>
                                </Label>
                            </template>
                            <Input id="intro-city-field" v-model="introCity" :placeholder="__('e.g. Nashville')" />
                        </Field>

                        <Field id="intro-seen-live-field" :label="__('Have you seen us live before?')">
                            <template #label>
                                <Label for="intro-seen-live-field">
                                    <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <Icon name="fieldtype-select" data-collapsed-field-icon class="size-3.5 me-1 text-orange-600 dark:text-orange-400" aria-hidden="true" />
                                        {{ __('Have you seen us live before?') }}
                                    </span>
                                </Label>
                            </template>
                            <Select
                                id="intro-seen-live-field"
                                v-model="introSeenLive"
                                :options="introSeenLiveOptions"
                                option-label="label"
                                option-value="value"
                                :placeholder="__('Choose one')"
                            />
                        </Field>
                    </div>
                </Card>
            </div>
        </Panel>

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
                        <Field id="heard-about-field" :label="__('How did you hear about us?')" required>
                        <template #label>
                            <Label for="heard-about-field">
                                <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <Icon name="fieldtype-select" data-collapsed-field-icon class="size-3.5 me-1 text-orange-600 dark:text-orange-400" aria-hidden="true" />
                                    {{ __('How did you hear about us?') }}
                                    <span class="relative -top-px -ms-0.5 text-red-600" :aria-label="__('Required')">*</span>
                                </span>
                            </Label>
                        </template>
                        <Select
                            id="heard-about-field"
                            v-model="heardAboutValue"
                            :options="heardAboutOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Choose one')"
                        />
                    </Field>

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
                                <!-- TODO: Add logic tree icon for fields with logic -->
                                <Icon data-logic-attached name="logic-tree" class="absolute z-(--z-index-above) top-1 max-sm:-right-2 sm:-left-14 size-3.5! text-gray-400 dark:text-gray-500" aria-hidden="true" />
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
                                <Icon data-logic-attached name="logic-tree" class="absolute z-(--z-index-above) top-1 max-sm:-right-2 sm:-left-14 size-3.5! text-gray-400 dark:text-gray-500" aria-hidden="true" />
                                <Input id="fan-length-field" v-model="fanLength" />
                            </Field>
                        </div>
                    </div>

                    <div
                        id="editing-field"
                        :data-editing-field="isPageInspector ? undefined : ''"
                        @click="inspectorTarget = 'field'"
                    >
                        <div
                            v-if="!isPageInspector"
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

                    <div data-fieldset-group class="space-y-7">
                        <div id="fieldset-start">
                            <span data-fieldset-label class="inline-flex gap-1.75 rounded-md font-mono text-2xs text-indigo-800">
                                <span class="inline-flex" v-tooltip="__('Fieldset')">
                                    <Icon name="link" class="size-3.5" aria-hidden="true" />
                                </span>
                                <span class="sr-only">{{ __('Fieldset') }}</span>
                            </span>
                            <Field :label="__('Which album was your second favorite?')">
                                <template #label>
                                    <Label>
                                        <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <Icon name="fieldtype-radio" data-collapsed-field-icon class="size-3.5 me-1 text-orange-600 dark:text-orange-400" aria-hidden="true" />
                                            {{ __('Which album was your second favorite?') }}
                                        </span>
                                    </Label>
                                </template>
                                <RadioGroup v-model="secondFavoriteAlbum">
                                    <Radio
                                        v-for="album in visibleAlbumOptions"
                                        :key="`second-${album.value}`"
                                        :value="album.value"
                                        :label="album.label"
                                    />
                                </RadioGroup>
                            </Field>
                        </div>

                        <Field :label="__('Sign up for email notifications from The Midnight')">
                            <template #label>
                                <Label>
                                    <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <Icon name="fieldtype-checkboxes" data-collapsed-field-icon class="size-3.5 me-1 text-orange-600 dark:text-orange-400" aria-hidden="true" />
                                        {{ __('Sign up for email notifications from The Midnight') }}
                                    </span>
                                </Label>
                            </template>
                            <CheckboxGroup v-model="emailNotifications">
                                <Checkbox
                                    v-for="notification in notificationOptions"
                                    :key="notification.value"
                                    :value="notification.value"
                                    :label="notification.label"
                                />
                            </CheckboxGroup>
                        </Field>

                        <div id="fieldset-end">
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
                        </div>
                    </div>

                        <Field :label="__('I want a free drink voucher')">
                        <template #label>
                            <Label>
                                <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <Icon name="fieldtype-toggle" data-collapsed-field-icon class="size-3.5 me-1 text-orange-600 dark:text-orange-400" aria-hidden="true" />
                                    {{ __('I want a free drink voucher') }}
                                </span>
                            </Label>
                        </template>
                        <Switch v-model="wantsFreeDrinkVoucher" />
                        </Field>
                    </div>
                </Card>
            </div>
        </Panel>

        <div
            class="mx-auto max-w-5xl max-[600px]:px-5 px-5.75 sm:px-6.25 mb-4 mt-12"
            role="button"
            tabindex="0"
            :aria-label="__('Goodbye')"
            data-form-page-label
            data-form-page="2"
            @click="inspectorTarget = 'page_2'; activeSettingsTab = 'settings'"
            @keydown.enter.prevent="inspectorTarget = 'page_2'; activeSettingsTab = 'settings'"
            @keydown.space.prevent="inspectorTarget = 'page_2'; activeSettingsTab = 'settings'"
        >
            <div class="flex items-center gap-4 cursor-pointer">
                <div class="h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true" />
                <div
                    class="flex shrink-0 items-center gap-2 rounded-xl border border-dashed border-gray-300 px-3.5 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200"
                    :class="inspectorTarget === 'page_2' ? 'bg-blue-50 border-blue-400!' : ''"
                >
                    <Icon name="page" class="size-4 shrink-0 text-gray-500 dark:text-gray-400" aria-hidden="true" />
                    {{ __('Goodbye') }}
                </div>
                <div class="h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true" />
            </div>
        </div>

        <Panel
            class="mx-auto max-w-5xl mb-6"
            :class="{ 'pb-0': postPageSectionCollapsed }"
            :data-panel-collapsed="postPageSectionCollapsed ? 'true' : 'false'"
        >
            <PanelHeader class="relative flex items-center justify-between">
                <Heading :text="__('Before you go')" />
                <Button
                    @click="postPageSectionCollapsed = !postPageSectionCollapsed"
                    class="static! [&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                    :icon="postPageSectionCollapsed ? 'expand' : 'collapse'"
                    size="sm"
                    variant="ghost"
                    :aria-label="__('Toggle section visibility')"
                />
            </PanelHeader>

            <div
                style="--tw-ease: ease;"
                class="h-auto visible transition-[height,visibility] duration-[250ms,2s]"
                :class="{ 'h-0! invisible! overflow-clip': postPageSectionCollapsed }"
            >
                <Card>
                    <div class="space-y-7" :data-fields-collapsed="fieldView === 'collapsed' ? 'true' : null">
                        <Field id="post-page-email-field" :label="__('Where should we send your confirmation?')" required>
                            <template #label>
                                <Label for="post-page-email-field">
                                    <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <Icon name="mail-sign-at" data-collapsed-field-icon class="size-3.5 me-1 text-blue-600 dark:text-blue-400" aria-hidden="true" />
                                        {{ __('Where should we send your confirmation?') }}
                                        <span class="relative -top-px -ms-0.5 text-red-600" :aria-label="__('Required')">*</span>
                                    </span>
                                </Label>
                            </template>
                            <Input id="post-page-email-field" v-model="postPageEmail" type="email" :placeholder="__('you@example.com')" />
                        </Field>

                        <Field id="post-page-note-field" :label="__('Anything else we should know?')" :instructions="__('Song requests, accessibility needs, or shout-outs for the crew (optional)')">
                            <template #label>
                                <Label for="post-page-note-field">
                                    <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <Icon name="text-long" data-collapsed-field-icon class="size-3.5 me-1 text-purple-500 dark:text-purple-400" aria-hidden="true" />
                                        {{ __('Anything else we should know?') }}
                                    </span>
                                </Label>
                            </template>
                            <Textarea id="post-page-note-field" v-model="postPageFinalNote" :rows="3" resize="vertical" />
                        </Field>

                        <Button
                            variant="primary"
                            @click.prevent
                            class="hover:cursor-not-allowed border-0! dark:border-0! ring-0! shadow-none!"
                            style="--theme-color-primary: var(--theme-color-gray-950)"
                            :text="__('Submit')"
                        />
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
                            <TabTrigger name="conditions" :text="isPageInspector ? __('Logic') : __('Conditions')" />
                            <TabTrigger v-if="!isPageInspector" name="validation" :text="__('Validation')" />
                        </TabList>

                        <TabContent name="settings">
                            <div v-if="isPageInspector" class="space-y-6 pt-8">
                                <div class="flex items-center gap-2.5">
                                    <div class="size-4">
                                        <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                        {{ selectedPageHeadingLabel }}
                                    </span>
                                </div>
                                <Field :label="__('Label')">
                                    <Input v-model="selectedPageInternalName" />
                                </Field>
                                <Field :label="__('Help Text')" :instructions="__('Additional field instructions like this.')">
                                    <Textarea v-model="settingsHelpText" :rows="2" resize="vertical" />
                                </Field>
                            </div>
                            <div v-else class="space-y-6 pt-8">
                                <div class="flex items-center gap-2.5">
                                    <div class="size-4">
                                        <Icon name="fieldtype-radio" class="size-4 text-gray-500 dark:text-gray-300" />
                                    </div>
                                    <a href="#editing-field" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                        {{ __('Multi Choice') }}
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
                        <TabContent name="conditions">
                            <div v-if="isPageInspector" class="space-y-6 pt-8">
                                <div class="flex items-center gap-2.5">
                                    <div class="size-4">
                                        <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                        {{ selectedPageHeadingLabel }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-200">{{ __('Conditions') }}</p>
                                <Button size="sm" variant="subtle" class="-ms-2 bg-transparent!" :text="__('+ Add Condition')" />
                            </div>
                            <div v-else class="space-y-6 pt-8">
                                <div class="flex items-center gap-2.5">
                                    <div class="size-4">
                                        <Icon name="fieldtype-radio" class="size-4 text-gray-500 dark:text-gray-300" />
                                    </div>
                                    <a href="#editing-field" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                        {{ __('Multi Choice') }}
                                        <div class="grid *:[grid-area:1/1]">
                                            <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                            <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                        </div>
                                    </a>
                                </div>

                                <LogicFlowMock />
                                <Button size="sm" variant="subtle" class="-ms-2 bg-transparent!" :text="__('+ Add Condition')" />
                            </div>
                        </TabContent>
                        <TabContent v-if="!isPageInspector" name="validation">
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
                    <TabTrigger name="conditions" :text="isPageInspector ? __('Logic') : __('Conditions')" />
                    <TabTrigger v-if="!isPageInspector" name="validation" :text="__('Validation')" />
                </TabList>

                <TabContent name="settings">
                    <div v-if="isPageInspector" class="space-y-6 pt-8">
                        <div class="flex items-center gap-2.5">
                            <div class="size-4">
                                <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <span class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                {{ selectedPageHeadingLabel }}
                            </span>
                        </div>

                        <Field :label="__('Label')">
                            <Input v-model="selectedPageInternalName" />
                        </Field>
                        <Field :label="__('Help Text')" :instructions="__('Additional field instructions like this.')">
                            <Textarea v-model="settingsHelpText" :rows="2" resize="vertical" />
                        </Field>
                    </div>
                    <div v-else class="space-y-6 pt-8">
                        <div data-field-settings class="flex items-center gap-2.5">
                            <div class="size-4">
                                <Icon name="fieldtype-radio" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a href="#editing-field" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                {{ __('Multi Choice') }}
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
                <TabContent name="conditions">
                    <div v-if="isPageInspector" class="space-y-6 pt-8">
                        <div class="flex items-center gap-2.5">
                            <div class="size-4">
                                <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <span class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                {{ selectedPageHeadingLabel }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-200">{{ __('Conditions') }}</p>
                        <Button size="sm" variant="subtle" class="-ms-2 bg-transparent!" :text="__('+ Add Condition')" />
                    </div>
                    <div v-else class="space-y-6 pt-8">
                        <div data-field-settings class="flex items-center gap-2.5">
                            <div class="size-4">
                                <Icon name="fieldtype-radio" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a href="#editing-field" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                {{ __('Multi Choice') }}
                                <div class="grid *:[grid-area:1/1]">
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                </div>
                            </a>
                        </div>

                        <LogicFlowMock />
                        <Button size="sm" variant="subtle" class="-ms-2 bg-transparent!" :text="__('+ Add Condition')" />
                    </div>
                </TabContent>
                <TabContent v-if="!isPageInspector" name="validation">
                    <p class="text-sm text-gray-700 dark:text-gray-200">{{ __('Validation') }}</p>
                </TabContent>
            </Tabs>
        </div>
    </LayoutPanel>
</template>
