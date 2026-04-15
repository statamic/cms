<script setup>
import Layout from '@/pages/layout/Layout.vue';
import FormsLayout from './Layout.vue';
import { Button, Icon, Tabs, TabList, TabTrigger, TabContent } from '@ui';
import LayoutPanel from '@/pages/layout/LayoutPanel.vue';
import { ref } from 'vue';

defineOptions({ layout: [Layout, FormsLayout] });

const props = defineProps({
    form: Object,
});

const activeSettingsTab = ref('settings');
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
            min-[1000px]:hidden sticky top-3 mt-3
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

    <div class="pt-20 min-[1000px]:py-10 col-span-full row-start-1">
        <p>TODO: Form Builder</p>
    </div>

    <Button
        class="
        min-[1000px]:hidden sticky top-3 mt-3
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
                    <div class="@container py-6 px-4">
                        <Tabs v-model:modelValue="activeSettingsTab" :unmount-on-hide="false">
                            <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">
                                <TabTrigger name="settings" :text="__('Settings')" />
                                <TabTrigger name="logic" :text="__('Logic')" />
                                <TabTrigger name="validation" :text="__('Validation')" />
                            </TabList>

                            <TabContent name="settings">
                                <p class="text-sm text-gray-700 dark:text-gray-200">{{ __('Settings') }}</p>
                            </TabContent>
                            <TabContent name="logic">
                                <p class="text-sm text-gray-700 dark:text-gray-200">{{ __('Logic') }}</p>
                            </TabContent>
                            <TabContent name="validation">
                                <p class="text-sm text-gray-700 dark:text-gray-200">{{ __('Validation') }}</p>
                            </TabContent>
                        </Tabs>
                    </div>
                </div>
            </div>
            <!-- This is the desktop nav - the content is repeated from the right panel -->
            <div class="@container relative py-6 px-4 max-[1000px]:hidden">
                <Tabs v-model:modelValue="activeSettingsTab" :unmount-on-hide="false">
                    <TabList class="inline-flex flex-wrap [&_button]:w-auto! mb-4 mx-0!">
                        <TabTrigger name="settings" :text="__('Settings')" />
                        <TabTrigger name="logic" :text="__('Logic')" />
                        <TabTrigger name="validation" :text="__('Validation')" />
                    </TabList>

                    <TabContent name="settings">
                        <p class="text-sm text-gray-700 dark:text-gray-200">{{ __('Settings') }}</p>
                    </TabContent>
                    <TabContent name="logic">
                        <p class="text-sm text-gray-700 dark:text-gray-200">{{ __('Logic') }}</p>
                    </TabContent>
                    <TabContent name="validation">
                        <p class="text-sm text-gray-700 dark:text-gray-200">{{ __('Validation') }}</p>
                    </TabContent>
                </Tabs>
            </div>
        </div>
    </LayoutPanel>
</template>
