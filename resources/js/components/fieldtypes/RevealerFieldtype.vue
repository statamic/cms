<template>
    <div>
        <div class="flex items-center gap-2" v-if="isToggleMode">
            <Switch
                :model-value="isRevealed"
                :disabled="config.disabled || isReadOnly"
                :id="id"
                @update:model-value="onToggleUpdate"
            />
            <Heading v-if="config.input_label" v-html="$markdown(__(config.input_label), { openLinksInNewTabs: true })" />
        </div>
        <Button
            v-else
            icon="eye-closed"
            :disabled="config.disabled || isReadOnly"
            :text="config.input_label || __('Show Fields')"
            :v-tooltip="__(config.instructions)"
            :data-readonly="isReadOnly ? true : undefined"
            :class="isReadOnly ? revealerReadOnlyButtonClass : undefined"
            @click="buttonReveal"
        />
    </div>
</template>

<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { Switch, Heading, Button, injectPublishContext as injectContainerContext } from '@ui';
import { onMounted, onBeforeUnmount, watch, nextTick, computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { update, expose, isReadOnly, defineReplicatorPreview } = Fieldtype.use(emit, props);
defineExpose(expose);

const { setRevealerField, unsetRevealerField, setHiddenField } = injectContainerContext();
const isRevealed = computed(() => props.value);
const isToggleMode = computed(() => data_get(props.config, 'mode') === 'toggle');
const fieldPath = computed(() => props.fieldPathPrefix ? `${props.fieldPathPrefix}.${props.handle}` : props.handle);

/** Dashed read-only aesthetic on the control (matches radio/checkbox field controls). */
const revealerReadOnlyButtonClass =
    'data-readonly:border-dashed! data-readonly:border-gray-300 data-readonly:with-contrast:border-gray-100 data-readonly:dark:border! data-readonly:dark:border-dashed! data-readonly:dark:border-gray-600!';

onMounted(() => setRevealerField(fieldPath.value));
onBeforeUnmount(() => unsetRevealerField(fieldPath.value));

watch(fieldPath, (fieldPath, oldFieldPath) => {
    unsetRevealerField(oldFieldPath);
    nextTick(() => setRevealerField(fieldPath));
});

function onToggleUpdate(value) {
    if (isReadOnly.value) return;
    update(value);
}

function buttonReveal() {
    if (isReadOnly.value) return;

    setHiddenField({
        dottedKey: fieldPath.value,
        hidden: 'force',
        omitValue: true,
    });

    update(true);
}

defineReplicatorPreview(() => null);
</script>
