<script setup>
import { Button, PublishContainer, PublishField } from '@/components/ui';
import PublishFieldsProvider from '@/components/ui/Publish/FieldsProvider.vue';

const emit = defineEmits(['update:values', 'removed']);

const props = defineProps({
    display: { type: String, required: true },
    fields: { type: Array, required: true },
    meta: { type: Object, required: true },
    values: { type: Object, required: true },
});
</script>

<template>
    <div class="flex items-center justify-between mb-3">
        <PublishContainer
            :model-value="values"
            @update:model-value="$emit('update:values', $event)"
            :meta="meta"
            :track-dirty-state="false"
        >
            <PublishFieldsProvider :fields="fields">
                <div class="flex items-center gap-2 w-full">
                    <div class="w-1/4 user-select-none">
                        <ui-input read-only :value="display" class="focus-within:outline-none" />
                    </div>
                    <div class="flex-1 flex items-center gap-2">
                        <PublishField
                            v-for="field in fields"
                            :key="field.handle"
                            :config="field"
                            v-slot="{ fieldtypeComponent, fieldtypeComponentProps, fieldtypeComponentEvents }"
                        >
                            <Component
                                :is="fieldtypeComponent"
                                v-bind="fieldtypeComponentProps"
                                v-on="fieldtypeComponentEvents"
                            />
                        </PublishField>
                    </div>
                    <Button @click="$emit('removed')" icon="x" size="sm" variant="ghost" inset />
                </div>
            </PublishFieldsProvider>
        </PublishContainer>
    </div>
</template>
