<script setup>
import { Button, PublishContainer, PublishField } from '@statamic/ui';
import PublishFieldsProvider from '@statamic/components/ui/Publish/FieldsProvider.vue';

const emit = defineEmits(['update:values', 'removed']);

const props = defineProps({
    display: { type: String, required: true },
    fields: { type: Array, required: true },
    meta: { type: Object, required: true },
    values: { type: Object, required: true },
});
</script>

<template>
    <div class="flex items-center justify-between">
        <PublishContainer
            :model-value="values"
            @update:model-value="$emit('update:values', $event)"
            :meta="meta"
            :track-dirty-state="false"
        >
            <PublishFieldsProvider :fields="fields">
                <div class="flex items-center justify-between">
                    <div>
                        {{ display }}
                    </div>
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
                    <Button @click="$emit('removed')" icon="x" />
                </div>
            </PublishFieldsProvider>
        </PublishContainer>
    </div>
</template>
