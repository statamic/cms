<script setup>
import { computed, ref } from 'vue';
import { Button } from '@statamic/ui';

const emit = defineEmits(['update:values', 'removed']);

const props = defineProps({
    display: { type: String, required: true },
    fields: { type: Array, required: true },
    meta: { type: Object, required: true },
    values: { type: Object, required: true },
});

const containerValues = ref(props.values);

function valuesUpdated(newValues) {
    emit('update:values', newValues);
}
</script>

<template>
    <div class="flex items-center justify-between">
        <ui-publish-container
            :model-value="containerValues"
            @update:model-value="valuesUpdated"
            :meta="meta"
            :track-dirty-state="false"
        >
            <ui-publish-fields-provider :fields="fields">
                <div class="flex items-center justify-between">
                    <div>
                        {{ display }}
                    </div>
                    <ui-publish-field
                        v-for="field in fields"
                        :key="field.handle"
                        :config="field"
                    />
                    <Button @click="$emit('removed')" icon="x" />
                </div>
            </ui-publish-fields-provider>
        </ui-publish-container>
    </div>
</template>
