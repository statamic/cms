<script setup>
import FormLayout from './Layout.vue';
import { Button } from '@ui';
import LayoutPanel from '@/pages/layout/LayoutPanel.vue';

const props = defineProps({
    form: Object,
});
</script>

<template>
    <FormLayout>
        <template #actions>
            <Button class="ms-2" :text="__('Save')" variant="primary" />
        </template>

        <LayoutPanel side="left">
            <div style="--graph-paper-y-offset: 2.5rem;" class="bg-graph-paper [&_button]:rounded-xl [&_button]:w-full [&_button]:font-normal [&_button]:justify-start [&_button]:h-9 [&_button_svg]:size-3.5">
                <ul class="px-0.5 grid gap-8">
                    <li>
                        <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Information</h2>
                        <ul class="grid gap-2 grid-cols-2">
                            <li>
                                <Button :text="__('Heading')" icon="heading" />
                            </li>
                            <li>
                                <Button :text="__('Paragraph')" icon="text-long" />
                            </li>
                            <li>
                                <Button :text="__('Banner')" icon="banner" />
                            </li>
                        </ul>
                    </li>
                    <li>
                        <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Text</h2>
                        <ul class="grid gap-2 grid-cols-2">
                            <li>
                                <Button :text="__('Short Answer')" icon="text-short" />
                            </li>
                            <li>
                                <Button :text="__('Long Answer')" icon="text-long" />
                            </li>
                        </ul>
                    </li>
                    <li>
                        <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Choice</h2>
                        <ul class="grid gap-2 grid-cols-2">
                            <li>
                                <Button :text="__('Dropdown')" icon="fieldtype-select" />
                            </li>
                            <li>
                                <Button :text="__('Multi Choice')" icon="fieldtype-radio" />
                            </li>
                            <li>
                                <Button :text="__('Checkboxes')" icon="fieldtype-checkboxes" />
                            </li>
                            <li>
                                <Button :text="__('Toggle')" icon="fieldtype-toggle" />
                            </li>
                            <li>
                                <Button :text="__('Star Rating')" icon="star" />
                            </li>
                        </ul>
                    </li>
                    <li>
                        <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Number</h2>
                        <ul class="grid gap-2 grid-cols-2">
                            <li>
                                <Button :text="__('Number')" icon="number" />
                            </li>
                            <li>
                                <Button :text="__('Currency')" icon="currency" />
                            </li>
                        </ul>
                    </li>
                    <li>
                        <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Date and Time</h2>
                        <ul class="grid gap-2 grid-cols-2">
                            <li>
                                <Button :text="__('Date Picker')" icon="calendar" />
                            </li>
                            <li>
                                <Button :text="__('Time Picker')" icon="time-clock" />
                            </li>
                            <li>
                                <Button :text="__('Range')" icon="calendar-range" />
                            </li>
                        </ul>
                    </li>
                    <li>
                        <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Contact Info</h2>
                        <ul class="grid gap-2 grid-cols-2">
                            <li>
                                <Button :text="__('Name')" icon="user-avatar-flush" />
                            </li>
                            <li>
                                <Button :text="__('Phone')" icon="mail-sign-hashtag" />
                            </li>
                            <li>
                                <Button :text="__('Email')" icon="mail-sign-at" />
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </LayoutPanel>

        <div class="py-4">
            <p>TODO: Form Builder</p>
        </div>

        <LayoutPanel side="right">
            <p>Settings</p>
        </LayoutPanel>
    </FormLayout>
</template>
