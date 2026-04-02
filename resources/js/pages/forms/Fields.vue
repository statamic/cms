<script setup>
import Layout from '@/pages/layout/Layout.vue';
import FormsLayout from './Layout.vue';
import { Button } from '@ui';
import LayoutPanel from '@/pages/layout/LayoutPanel.vue';

defineOptions({ layout: [Layout, FormsLayout] });

const props = defineProps({
    form: Object,
});
</script>

<template>
    <Teleport to="#form-layout-actions">
        <Button :text="__('Save')" variant="primary" />
    </Teleport>

    <LayoutPanel side="left">
        <div style="--graph-paper-y-offset: 4.5rem;" class="bg-graph-paper [&_button]:w-full [&_button>div]:truncate [&_button>div]:block [&_button]:rounded-xl [&_button]:font-normal [&_button]:justify-start [&_button]:h-9 [&_button_svg]:size-3.5">


            <div class="c-nav-with-popover-api">
                <button class="c-nav-with-popover-api__mobile-button" id="anchor-nav-mobile" popovertarget="popover-nav-mobile">Menu</button>
                <nav id="popover-nav-mobile" class="c-nav-with-popover-api__mobile u-hide-on-desktop" popover>
                    <button class="c-nav-with-popover-api__close-button" title="Close" popovertarget="popover-nav-mobile">
                        <svg height="100pt" aria-hidden="true" viewBox="0 0 100 100" width="100pt" xmlns="http://www.w3.org/2000/svg"><path d="m91.668 13.676-5.3398-5.3398-36.328 36.324-36.328-36.324-5.3398 5.3398 36.328 36.324-36.328 36.324 5.3398 5.3398 36.328-36.324 36.328 36.324 5.3398-5.3398-36.328-36.324z"/></svg>
                    </button>
                    <ul>
                        <li>
                            <span class="c-nav-with-popover-api-category-heading">Getting Started</span>
                            <ul>
                                <li><a href="/">Requirements</a></li>
                                <li><a href="/">Quickstart Guide</a></li>
                                <li><a href="/">Upgrade Guides</a></li>
                                <li><a href="/">Installing</a></li>
                                <li><a href="/">Updating</a></li>
                                <li><a href="/">Deploying</a></li>
                            </ul>
                        </li>

                        <li>
                            <span class="c-nav-with-popover-api-category-heading">Core Concepts</span>
                            <ul>
                                <li><a href="/">Overview</a></li>
                                <li><a href="/">Assets</a></li>
                                <li><a href="/">Configuration</a></li>
                                <li><a href="/">Collections</a></li>
                                <li><a href="/">Fields</a></li>
                                <li><a href="/">Globals</a></li>
                                <li><a href="/">Search</a></li>
                                <li><a href="/">Navigation</a></li>
                                <li><a href="/">Taxonomies</a></li>
                                <li><a href="/">Users</a></li>
                                <li><a href="/">YAML</a></li>
                            </ul>
                        </li>

                        <li>
                            <span class="c-nav-with-popover-api-category-heading">Diving Deeper</span>
                            <ul>
                                <li><a href="/">Augmentation</a></li>
                                <li><a href="/">CLI</a></li>
                                <li><a href="/">Caching</a></li>
                                <li><a href="/">Computed Values</a></li>
                                <li><a href="/">Data Inheritance</a></li>
                                <li><a href="/">Debugging</a></li>
                                <li><a href="/">Forms</a></li>
                                <li><a href="/">OAuth</a></li>
                                <li><a href="/">Multi-Site</a></li>
                                <li><a href="/">Preferences</a></li>
                                <li><a href="/">Revisions</a></li>
                            </ul>
                        </li>

                        <li>
                            <span class="c-nav-with-popover-api-category-heading">Frontend</span>
                            <ul>
                                <li><a href="/">Overview</a></li>
                                <li><a href="/">Antlers Templates</a></li>
                                <li><a href="/">Blade Templates</a></li>
                                <li><a href="/">Content Queries</a></li>
                                <li><a href="/">Controllers</a></li>
                                <li><a href="/">GraphQL API</a></li>
                                <li><a href="/">Image Manipulation</a></li>
                                <li><a href="/">Front-End Frameworks</a></li>
                                <li><a href="/">Modifiers</a></li>
                                <li><a href="/">Protecting Content</a></li>
                                <li><a href="/">REST API</a></li>
                                <li><a href="/">Tags</a></li>
                                <li><a href="/">Tag Conditions</a></li>
                                <li><a href="/">Variables</a></li>
                                <li><a href="/">Views</a></li>
                                <li><a href="/">View Models</a></li>
                            </ul>
                        </li>

                        <li>
                            <span class="c-nav-with-popover-api-category-heading">Case Studies</span>
                            <ul>
                                <li><a href="/">Dashboard</a></li>
                                <li><a href="/">Navigation</a></li>
                                <li><a href="/">Blueprints</a></li>
                                <li><a href="/">Fieldtypes</a></li>
                                <li><a href="/">Fieldsets</a></li>
                                <li><a href="/">Conditional Fields</a></li>
                                <li><a href="/">Git Automation</a></li>
                                <li><a href="/">Live Preview</a></li>
                                <li><a href="/">Multi-User Collaboration</a></li>
                                <li><a href="/">Translations</a></li>
                                <li><a href="/">White Labeling</a></li>
                                <li><a href="/">Widgets</a></li>
                            </ul>
                        </li>

                        <li>
                            <span class="c-nav-with-popover-api-category-heading">Addons</span>
                            <ul>
                                <li><a href="/">Overview</a></li>
                            </ul>
                        </li>

                        <li>
                            <span class="c-nav-with-popover-api-category-heading">Starter Kits</span>
                            <ul>
                                <li><a href="/">Overview</a></li>
                                <li><a href="/">Installing</a></li>
                                <li><a href="/">Creating</a></li>
                            </ul>
                        </li>

                        <li>
                            <span class="c-nav-with-popover-api-category-heading">Resources</span>
                            <ul>
                                <li><a href="/">Content Manager's Guide</a></li>
                                <li><a href="/">WordPress to Statamic</a></li>
                                <li><a href="/">Code of Conduct</a></li>
                                <li><a href="/">Contributing</a></li>
                                <li><a href="/">Licensing</a></li>
                                <li><a href="/">Release Schedule</a></li>
                                <li><a href="/">Tips & Tricks</a></li>
                                <li><a href="/">Troubleshooting</a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>



























            <ul class="px-0.5 grid gap-8 @container py-10 max-[1000px]:hidden">
                <li>
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Information</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Text</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Choice</h2>
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
                    </ul>
                </li>
                <li>
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Rate</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Contact Info</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Number</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Date and Time</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Media</h2>
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
                    <h2 class="px-1.5 pb-1.5 text-sm text-gray-950 font-medium">Payment</h2>
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

    <div class="py-4">
        <p>TODO: Form Builder</p>
    </div>

    <LayoutPanel side="right">
        <p>Settings</p>
    </LayoutPanel>
</template>
