<script setup>
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';
import LogicFlowMock from './LogicFlowMock.vue';
import { Button, Card, Checkbox, CheckboxGroup, Field, Header, Heading, Icon, Input, Label, Panel, PanelHeader, Radio, RadioGroup, Select, StatusIndicator, Switch, Textarea, Tabs, TabList, TabTrigger, TabContent, ToggleGroup, ToggleItem } from '@ui';
import LayoutPanel from '@/pages/layout/LayoutPanel.vue';
import WidthSelector from '@/components/fields/WidthSelector.vue';
import { computed, ref } from 'vue';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

const props = defineProps({
    form: Object,
});

const formTitle = computed(() => props.form?.title || __('Untitled Form'));
const activeSettingsTab = ref('settings');
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
const totalFieldCount = computed(() => 8);
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
const notificationOptions = [
    { label: __('New Singles and Albums'), value: 'singles_and_albums' },
    { label: __('Merchandise'), value: 'merchandise' },
    { label: __('Friends of The Midnight'), value: 'friends_of_the_midnight' },
];
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
                    <ul style="--graph-paper-y-offset: 4.5rem;" class="bg-graph-paper px-0.5 grid gap-8 @container py-10">
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

        <Panel class="mx-auto max-w-5xl">
            <PanelHeader>
                <Heading :text="__('Section')" />
            </PanelHeader>

            <Card>
                <div class="space-y-7" :data-fields-collapsed="fieldView === 'collapsed' ? 'true' : null">
                    <Field id="heard-about-field" :label="__('How did you hear about us?')" required>
                        <template #label>
                            <Label for="heard-about-field">
                                <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <Icon name="fieldtype-select" data-collapsed-field-icon class="size-3.5 rounded-sm bg-orange-50 text-orange-500 dark:bg-orange-950/40 dark:text-orange-300" aria-hidden="true" />
                                    {{ __('How did you hear about us?') }}
                                    <span class="relative -top-px ms-0.5 text-red-600" :aria-label="__('Required')">*</span>
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
                            <div id="editing-field" data-editing-field>
                                <div class="!absolute z-(--z-index-above) -top-0.5 end-0.5 flex items-center">
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
                                        icon="trash"
                                        variant="subtle"
                                        :aria-label="__('Remove field')"
                                        :title="__('Remove field')"
                                        class="[&_svg]:opacity-45"
                                    />
                                </div>
                                <Field :label="__('What do you like most about our band?')">
                                    <template #label>
                                        <Label for="favorite-thing-field">
                                            <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                                <Icon name="text-long" data-collapsed-field-icon class="size-3.5 rounded-sm bg-purple-50 text-purple-500 dark:bg-purple-950/40 dark:text-purple-300" aria-hidden="true" />
                                                {{ __('What do you like most about our band?') }}
                                                <span class="relative -top-px ms-0.5 text-red-600" :aria-label="__('Required')">*</span>
                                            </span>
                                        </Label>
                                    </template>
                                    <!-- TODO: Add logic tree icon for fields with logic -->
                                    <Icon name="logic-tree" class="absolute z-(--z-index-above) top-1 -left-14 size-3.5! text-gray-400 dark:text-gray-500" aria-hidden="true" />
                                    <Textarea id="favorite-thing-field" v-model="favoriteThing" :rows="4" resize="vertical" required />
                                </Field>
                            </div>
                        </div>

                        <div id="fieldset-end">
                            <Field :label="__('How long have you been a fan?')" :instructions="__('If you don\'t remember, just give your best estimate.')">
                                <template #label>
                                    <Label for="fan-length-field">
                                        <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <Icon name="text-short" data-collapsed-field-icon class="size-3.5 rounded-sm bg-purple-50 text-purple-500 dark:bg-purple-950/40 dark:text-purple-300" aria-hidden="true" />
                                            {{ __('How long have you been a fan?') }}
                                        </span>
                                    </Label>
                                </template>
                                <Icon name="logic-tree" class="absolute z-(--z-index-above) top-1 -left-14 size-3.5! text-gray-400 dark:text-gray-500" aria-hidden="true" />
                                <Input id="fan-length-field" v-model="fanLength" />
                            </Field>
                        </div>
                    </div>

                    <Field :label="__('Which album was your favorite?')">
                        <template #label>
                            <Label>
                                <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <Icon name="fieldtype-radio" data-collapsed-field-icon class="size-3.5 rounded-sm bg-orange-50 text-orange-500 dark:bg-orange-950/40 dark:text-orange-300" aria-hidden="true" />
                                    {{ __('Which album was your favorite?') }}
                                </span>
                            </Label>
                        </template>
                        <RadioGroup v-model="favoriteAlbum">
                            <Radio
                                v-for="album in albumOptions"
                                :key="album.value"
                                :value="album.value"
                                :label="album.label"
                            />
                        </RadioGroup>
                    </Field>

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
                                            <Icon name="fieldtype-radio" data-collapsed-field-icon class="size-3.5 rounded-sm bg-orange-50 text-orange-500 dark:bg-orange-950/40 dark:text-orange-300" aria-hidden="true" />
                                            {{ __('Which album was your second favorite?') }}
                                        </span>
                                    </Label>
                                </template>
                                <RadioGroup v-model="secondFavoriteAlbum">
                                    <Radio
                                        v-for="album in albumOptions"
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
                                        <Icon name="fieldtype-checkboxes" data-collapsed-field-icon class="size-3.5 rounded-sm bg-orange-50 text-orange-500 dark:bg-orange-950/40 dark:text-orange-300" aria-hidden="true" />
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
                            <Field id="age-field" :label="__('How old are you?')">
                                <template #label>
                                    <Label for="age-field">
                                        <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <Icon name="number" data-collapsed-field-icon class="size-3.5 rounded-sm bg-teal-50 text-teal-500 dark:bg-teal-950/40 dark:text-teal-300" aria-hidden="true" />
                                            {{ __('How old are you?') }}
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
                                    <Icon name="fieldtype-toggle" data-collapsed-field-icon class="size-3.5 rounded-sm bg-orange-50 text-orange-500 dark:bg-orange-950/40 dark:text-orange-300" aria-hidden="true" />
                                    {{ __('I want a free drink voucher') }}
                                </span>
                            </Label>
                        </template>
                        <Switch v-model="wantsFreeDrinkVoucher" />
                    </Field>
                </div>
            </Card>
        </Panel>

        <p class="mx-auto max-w-5xl max-[600px]:p-5 px-5.75 sm:px-6.25 mb-5 text-sm text-gray-600 dark:text-gray-300">
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
        <div class="[&_button]:w-full [&_button>div]:truncate [&_button>div]:block [&_button]:rounded-xl [&_button]:font-normal [&_button]:justify-start [&_button]:h-9 [&_button_svg]:size-3.5">


            <div class="right-panel-popover min-[1000px]:hidden">
                <div id="popover-right-panel" class="right-panel-popover__menu" popover>
                    <button class="right-panel-popover__close-button" title="Close" popovertarget="popover-right-panel">
                        <svg height="100pt" aria-hidden="true" viewBox="0 0 100 100" width="100pt" xmlns="http://www.w3.org/2000/svg"><path d="m91.668 13.676-5.3398-5.3398-36.328 36.324-36.328-36.324-5.3398 5.3398 36.328 36.324-36.328 36.324 5.3398 5.3398 36.328-36.324 36.328 36.324 5.3398-5.3398-36.328-36.324z"/></svg>
                    </button>
                    <div class="@container py-6 px-2.5">
                        <Tabs v-model:modelValue="activeSettingsTab" :unmount-on-hide="false">
                            <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">
                                <TabTrigger name="settings" :text="__('Settings')" />
                                <TabTrigger name="logic" :text="__('Logic')" />
                                <TabTrigger name="validation" :text="__('Validation')" />
                            </TabList>

                            <TabContent name="settings">
                                <div class="space-y-6 pt-8">
                                    <div class="flex items-center gap-2.5">
                                        <Icon name="fieldtype-radio" class="size-4 text-gray-500 dark:text-gray-300" />
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
                                </div>
                            </TabContent>
                            <TabContent name="logic">
                                <div class="space-y-6 pt-8">
                                    <div class="flex items-center gap-2.5">
                                        <Icon name="fieldtype-radio" class="size-4 text-gray-500 dark:text-gray-300" />
                                        <a href="#editing-field" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                            {{ __('Multi Choice') }}
                                            <div class="grid *:[grid-area:1/1]">
                                                <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                                <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                            </div>
                                        </a>
                                    </div>

                                    <LogicFlowMock />
                                </div>
                            </TabContent>
                            <TabContent name="validation">
                                <p class="text-sm text-gray-700 dark:text-gray-200">{{ __('Validation') }}</p>
                            </TabContent>
                        </Tabs>
                    </div>
                </div>
            </div>

            <!-- This is the desktop nav - the content is repeated from the right panel -->
            <div class="@container relative py-6 px-2.5 pe-4.5 max-[1000px]:hidden">
                <Tabs v-model:modelValue="activeSettingsTab" :unmount-on-hide="false">
                    <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">
                        <TabTrigger name="settings" :text="__('Settings')" />
                        <TabTrigger name="logic" :text="__('Logic')" />
                        <TabTrigger name="validation" :text="__('Validation')" />
                    </TabList>

                    <TabContent name="settings">
                        <div class="space-y-6 pt-8">
                            <div data-field-settings class="flex items-center gap-2.5">
                                <Icon name="fieldtype-radio" class="size-4 text-gray-500 dark:text-gray-300" />
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
                    <TabContent name="logic">
                        <div class="space-y-6 pt-8">
                            <div data-field-settings class="flex items-center gap-2.5">
                                <Icon name="fieldtype-radio" class="size-4 text-gray-500 dark:text-gray-300" />
                                <a href="#editing-field" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                    {{ __('Multi Choice') }}
                                    <div class="grid *:[grid-area:1/1]">
                                        <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                        <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                    </div>
                                </a>
                            </div>

                            <LogicFlowMock />
                        </div>
                    </TabContent>
                    <TabContent name="validation">
                        <p class="text-sm text-gray-700 dark:text-gray-200">{{ __('Validation') }}</p>
                    </TabContent>
                </Tabs>
            </div>
        </div>
    </LayoutPanel>
</template>
