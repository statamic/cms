<script setup>
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';
import LogicFlowMock from './LogicFlowMock.vue';
import { Button, Card, Checkbox, CheckboxGroup, Field, Header, Heading, Icon, Input, Panel, PanelHeader, Radio, RadioGroup, Select, StatusIndicator, Switch, Textarea, Tabs, TabList, TabTrigger, TabContent } from '@ui';
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
                            <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Information</h2>
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
                            <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Text</h2>
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
                            <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Choice</h2>
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
                            <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Rate</h2>
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
                            <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Contact Info</h2>
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
                            <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Number</h2>
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
                            <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Date and Time</h2>
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
                            <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Media</h2>
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
                            <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Payment</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Information</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Text</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Choice</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Rate</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Contact Info</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Number</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Date and Time</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Media</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 dark:text-gray-200 font-medium">Payment</h2>
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
        </Header>

        <Panel class="mx-auto max-w-5xl">
            <PanelHeader>
                <Heading :text="__('Section')" />
            </PanelHeader>

            <Card>
                <div class="space-y-7">
                    <Field :label="__('How did you hear about us?')" required>
                        <Select
                            v-model="heardAboutValue"
                            :options="heardAboutOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Choose one')"
                        />
                    </Field>

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

                        <Field :label="__('What do you like most about our band?')" required>
                            <Textarea v-model="favoriteThing" :rows="4" resize="vertical" />
                        </Field>
                    </div>

                    <Field :label="__('How long have you been a fan?')" :instructions="__('If you don\'t remember, just give your best estimate.')">
                        <Input v-model="fanLength" />
                    </Field>

                    <Field :label="__('Which album was your favorite?')">
                        <RadioGroup v-model="favoriteAlbum">
                            <Radio
                                v-for="album in albumOptions"
                                :key="album.value"
                                :value="album.value"
                                :label="album.label"
                            />
                        </RadioGroup>
                    </Field>

                    <Field :label="__('Which album was your second favorite?')">
                        <RadioGroup v-model="secondFavoriteAlbum">
                            <Radio
                                v-for="album in albumOptions"
                                :key="`second-${album.value}`"
                                :value="album.value"
                                :label="album.label"
                            />
                        </RadioGroup>
                    </Field>

                    <Field :label="__('Sign up for email notifications from The Midnight')">
                        <CheckboxGroup v-model="emailNotifications">
                            <Checkbox
                                v-for="notification in notificationOptions"
                                :key="notification.value"
                                :value="notification.value"
                                :label="notification.label"
                            />
                        </CheckboxGroup>
                    </Field>

                    <Field :label="__('How old are you?')">
                        <Input v-model="age" type="number" />
                    </Field>

                    <Field :label="__('I want a free drink voucher')">
                        <Switch v-model="wantsFreeDrinkVoucher" />
                    </Field>
                </div>
            </Card>
        </Panel>
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
                                            <Icon name="arrow-up" class="size-4 shrink-0 text-gray-400 dark:text-gray-500" aria-hidden="true" />
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
                                            <Icon name="arrow-up" class="size-4 shrink-0 text-gray-400 dark:text-gray-500" aria-hidden="true" />
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
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
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
                                    <Icon name="arrow-up" class="size-4 shrink-0 text-gray-400 dark:text-gray-500" aria-hidden="true" />
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
