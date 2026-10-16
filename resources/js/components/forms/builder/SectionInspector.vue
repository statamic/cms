<script setup lang="ts">
import { Field, Icon, Input } from '@ui';
import { watch } from 'vue';
import { injectBuilderContext } from '@/pages/forms/Builder.vue';

const { dirty, inspecting: section } = injectBuilderContext();

watch(() => section.value.display, dirty);
</script>

<template>
    <div class="@container relative pt-6 pb-40 max-[1000px]:pb-12 px-2.5 pe-4.5">
        <div class="group/logic-tab space-y-6 pt-8">
            <div class="flex items-center gap-2.5">
                <div class="size-4">
                    <Icon name="page" class="size-4 text-gray-500 dark:text-gray-300" />
                </div>
                <a :href="`#section-${section._id}`" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                    {{ __(section.display) }}
                    <div class="grid *:[grid-area:1/1]">
                        <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                        <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                    </div>
                </a>
            </div>

            <Field :label="__('Label')">
                <Input v-model="section.display" focus />
            </Field>
        </div>
    </div>
</template>
