<script setup>
import { ref, computed, useTemplateRef } from 'vue';
import useActions from './Actions.js';
import ConfirmableAction from './ConfirmableAction.vue';

const props = defineProps({
    url: { type: String, required: true },
    actions: { type: Array, required: true },
    item: { required: true },
    isDirty: { type: Boolean, default: false },
});

const emit = defineEmits(['started', 'completed']);

const { prepareActions, runServerAction } = useActions();

const confirmableActions = useTemplateRef('confirmableActions');

let preparedActions = computed(() => {
    return prepareActions(props.actions, confirmableActions.value);
});

function runAction(action, values, done) {
    emit('started');

    runServerAction({ action, values, done, url: props.url, selections: [props.item] })
        .then(data => emit('completed', true, data))
        .catch(data => emit('completed', false, data));

    // TODO: Handle validation `errors` from server, which is passed into ConfirmableAction's errors prop below
}
</script>

<template>
    <ConfirmableAction
        ref="confirmableActions"
        v-for="action in actions"
        :key="action.handle"
        :action="action"
        :selections="1"
        :errors="{}"
        :is-dirty="isDirty"
        @confirmed="runAction"
    />
    <slot :actions="preparedActions" />
</template>
