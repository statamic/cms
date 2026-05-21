<script setup lang="ts">
import { Field, Icon, Input, Combobox, RadioGroup, Radio } from '@ui';
import { computed, watch } from 'vue';
import { injectBuilderContext } from '@/pages/forms/Builder.vue';
import { __ } from '@/bootstrap/globals';
import { usePage } from '@inertiajs/vue3';

const { inspecting: fieldsetImport, dirty } = injectBuilderContext();

const fieldsets = Object.values(usePage().props.fieldsets);

const fieldsetSuggestions = computed(() =>
    fieldsets.map((fieldset) => ({
        value: fieldset.handle,
        label: __(fieldset.title),
    })));

const selectedFieldset = computed(() => fieldsets.find((fieldset) => fieldset.handle === fieldsetImport.value.fieldset));
const selectedFieldsetHasSections = computed(() => selectedFieldset.value?.has_sections === true);

watch(() => fieldsetImport.value.fieldset, () => {
    dirty();

    if (!selectedFieldsetHasSections.value) {
        delete fieldsetImport.value.section_behavior;
    }
});

watch(() => fieldsetImport.value.prefix, dirty);
watch(() => fieldsetImport.value.section_behavior, dirty);
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
                    <div class="space-y-6 pt-8">
                        <div class="flex items-center gap-2.5">
                            <div class="size-4">
                                <Icon name="link" class="size-4 text-gray-500 dark:text-gray-300" />
                            </div>
                            <a :href="`#import-${fieldsetImport._id}`" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                                {{ __('Linked Fieldset') }}
                                <div class="grid *:[grid-area:1/1]">
                                    <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                                    <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                                </div>
                            </a>
                        </div>

                        <Field :label="__('Fieldset')">
                            <Combobox
                                :placeholder="__('Fieldsets')"
                                :options="fieldsetSuggestions"
                                searchable
                                :model-value="fieldsetImport.fieldset"
                                @update:modelValue="fieldsetImport.fieldset = $event"
                            />
                        </Field>

                        <Field
                            :label="__('Prefix')"
                            :instructions="__('messages.fieldset_import_prefix_instructions')"
                        >
                            <Input v-model="fieldsetImport.prefix" :placeholder="__('e.g. hero_')" />
                        </Field>

                        <Field
                            v-if="selectedFieldsetHasSections"
                            :label="__('Section Behavior')"
                            :instructions="__('messages.fieldset_import_section_behavior_instructions')"
                        >
                            <RadioGroup v-model="fieldsetImport.section_behavior">
                                <Radio :label="__('Preserve')" :description="__('Keep imported sections as-is.')" value="preserve" />
                                <Radio :label="__('Flatten')" :description="__('Merge all fields into this section.')" value="flatten" />
                            </RadioGroup>
                        </Field>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop -->
        <div class="@container relative pt-6 pb-12 px-2.5 pe-4.5 max-[1000px]:hidden">
            <div class="space-y-6 pt-8">
                <div class="flex items-center gap-2.5">
                    <div class="size-4">
                        <Icon name="link" class="size-4 text-gray-500 dark:text-gray-300" />
                    </div>
                    <a :href="`#import-${fieldsetImport._id}`" class="inline-flex items-center gap-1.5 text-xl font-medium antialiased">
                        {{ __('Linked Fieldset') }}
                        <div class="grid *:[grid-area:1/1]">
                            <Icon name="arrow-up" data-field-direction-up aria-hidden="true" />
                            <Icon name="arrow-down" data-field-direction-down aria-hidden="true" />
                        </div>
                    </a>
                </div>

                <Field :label="__('Fieldset')">
                    <Combobox
                        :placeholder="__('Fieldsets')"
                        :options="fieldsetSuggestions"
                        searchable
                        :model-value="fieldsetImport.fieldset"
                        @update:modelValue="fieldsetImport.fieldset = $event"
                    />
                </Field>

                <Field
                    :label="__('Prefix')"
                    :instructions="__('messages.fieldset_import_prefix_instructions')"
                >
                    <Input v-model="fieldsetImport.prefix" :placeholder="__('e.g. hero_')" />
                </Field>

                <Field
                    v-if="selectedFieldsetHasSections"
                    :label="__('Section Behavior')"
                    :instructions="__('messages.fieldset_import_section_behavior_instructions')"
                >
                    <RadioGroup v-model="fieldsetImport.section_behavior">
                        <Radio :label="__('Preserve')" :description="__('Keep imported sections as-is.')" value="preserve" />
                        <Radio :label="__('Flatten')" :description="__('Merge all fields into this section.')" value="flatten" />
                    </RadioGroup>
                </Field>
            </div>
        </div>
    </div>
</template>
