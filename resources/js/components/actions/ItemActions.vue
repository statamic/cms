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

const { sortActions, runServerAction, errors } = useActions();

let sortedActions = computed(() => {
    return sortActions(props.actions);
});

const confirmableActions = useTemplateRef('confirmableActions');

function confirmAction(action) {
    let i = sortedActions.value.findIndex(a => a.handle === action.handle);
    confirmableActions.value[i].confirm();
}

function runAction(action, values, done) {
    emit('started');

    runServerAction({ action, values, done, url: props.url, selections: [props.item] })
        .then(data => emit('completed', true, data))
        .catch(data => emit('completed', false, data));
}
</script>

<template>
    <ConfirmableAction
        ref="confirmableActions"
        v-for="action in sortedActions"
        :key="action.handle"
        :action="action"
        :selections="1"
        :errors="errors"
        :is-dirty="isDirty"
        @confirmed="runAction"
    />
    <slot :actions="sortedActions" :select="confirmAction" />
</template>
