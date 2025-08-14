<template>
    <div>
        <div class="flex items-center gap-2" v-if="isToggleMode">
            <Switch :model-value="isRevealed" @update:model-value="update" :read-only="isReadOnly" :id="id" />
            <Heading v-if="config.input_label" v-html="$markdown(__(config.input_label), { openLinksInNewTabs: true })" />
        </div>
        <Button
            v-else
            icon="eye-closed"
            @click="buttonReveal"
            :read-only="isReadOnly"
            :disabled="config.disabled"
            :text="config.input_label || __('Show Fields')"
            :v-tooltip="__(config.instructions)"
        />
    </div>
</template>

<script setup>
import { Fieldtype } from '@statamic/cms';
import { Switch, Heading, Button } from '@/components/ui';
import { onMounted, onBeforeUnmount, watch, nextTick, computed } from 'vue';
import { injectContainerContext } from '@statamic/components/ui/Publish/Container.vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { update, expose, isReadOnly } = Fieldtype.use(emit, props);
defineExpose(expose);

const { setRevealerField, unsetRevealerField, setHiddenField } = injectContainerContext();
const isRevealed = computed(() => props.value);
const isToggleMode = computed(() => data_get(props.config, 'mode') === 'toggle');
const fieldPath = computed(() => props.fieldPathPrefix ? `${props.fieldPathPrefix}.${props.handle}` : props.handle);

onMounted(() => setRevealerField(fieldPath.value));
onBeforeUnmount(() => unsetRevealerField(fieldPath.value));

watch(fieldPath, (fieldPath, oldFieldPath) => {
    unsetRevealerField(oldFieldPath);
    nextTick(() => setRevealerField(fieldPath));
});

function buttonReveal() {
    if (isReadOnly.value) return;

    setHiddenField({
        dottedKey: fieldPath.value,
        hidden: 'force',
        omitValue: true,
    });

    update(true);
}
</script>
