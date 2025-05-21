<script setup>
import { ref, computed, watch, inject, useTemplateRef } from 'vue';
import useActions from './Actions.js';
import ConfirmableAction from './ConfirmableAction.vue';
import axios from 'axios';

const props = defineProps({
    url: { type: String, required: true },
    context: { type: Object, default: () => {} },
    showAlways: { type: Boolean, default: false },
});

const emit = defineEmits(['started', 'completed']);

const sharedState = inject('sharedState');

const { sortActions, runServerAction, errors } = useActions();

let actions = ref([]);

let sortedActions = computed(() => {
    return sortActions(actions.value);
});

let selections = computed(() => {
    return sharedState.selections;
});

let hasSelections = computed(() => {
    return selections.value.length > 0;
});

watch(
    selections,
    () => getActions(),
    { deep: true },
);

function getActions() {
    if (selections.value.length === 0) {
        actions.value = [];
        return;
    }

    let params = {
        selections: selections.value,
    };

    if (props.context) {
        params.context = props.context;
    }

    axios
        .post(props.url + '/list', params)
        .then(response => actions.value = response.data);
}

const confirmableActions = useTemplateRef('confirmableActions');

function confirmAction(action) {
    let i = sortedActions.value.findIndex(a => a.handle === action.handle);
    confirmableActions.value[i].select();
}

function runAction(action, values, done) {
    emit('started');

    runServerAction({ action, values, done, url: props.url, selections })
        .then(data => emit('completed', true, data))
        .catch(data => emit('completed', false, data));
}
</script>

<template>
    <ConfirmableAction
        ref="confirmableActions"
        v-if="hasSelections"
        v-for="action in sortedActions"
        :key="action.handle"
        :action="action"
        :selections="selections.length"
        :errors="errors"
        @confirmed="runAction"
    />
    <slot
        v-if="showAlways || hasSelections"
        :actions="sortedActions"
        :select="confirmAction"
    />
</template>
