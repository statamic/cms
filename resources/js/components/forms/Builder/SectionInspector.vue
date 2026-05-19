<script setup lang="ts">
import { Field, Icon, Input } from '@ui';
import { injectBuilderContext } from '@/pages/forms/Builder.vue';

const { inspecting: section } = injectBuilderContext();
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
                    <div class="group/logic-tab space-y-6 pt-8">
                        <div class="flex items-center gap-2.5">
                            <div class="size-4">
                                <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a :href="`#section-${section._id}`" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                {{ __(section.title) }}
                                <div class="grid *:[grid-area:1/1]">
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                </div>
                            </a>
                        </div>

                        <Field :label="__('Label')">
                            <Input v-model="section.title" />
                        </Field>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop -->
        <div class="@container relative pt-6 pb-12 px-2.5 pe-4.5 max-[1000px]:hidden">
            <div class="group/logic-tab space-y-6 pt-8">
                <div class="flex items-center gap-2.5">
                    <div class="size-4">
                        <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                    </div>
                    <a :href="`#section-${section._id}`" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                        {{ __(section.title) }}
                        <div class="grid *:[grid-area:1/1]">
                            <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                            <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                        </div>
                    </a>
                </div>

                <Field :label="__('Label')">
                    <Input v-model="section.title" />
                </Field>
            </div>
        </div>
    </div>
</template>
