<script setup>
import { ref, computed, watch, useTemplateRef, toRaw } from 'vue';
import useActions from './Actions.js';
import ConfirmableAction from './ConfirmableAction.vue';
import axios from 'axios';

const props = defineProps({
    url: { type: String, required: true },
    selections: { type: Array, required: true },
    context: { type: Object, default: () => {} },
    showAlways: { type: Boolean, default: false },
});

const emit = defineEmits(['started', 'completed']);

const { prepareActions, runServerAction } = useActions();

let actions = ref([]);

const confirmableActions = useTemplateRef('confirmableActions');

let preparedActions = computed(() => {
    return prepareActions(actions.value, confirmableActions.value);
});

let hasSelections = computed(() => {
    return props.selections.length > 0;
});

watch(props.selections, getActions, { deep: true });

function getActions() {
    if (!hasSelections.value) {
        actions.value = [];
        return;
    }

    let params = {
        selections: toRaw(props.selections),
    };

    if (props.context) {
        params.context = props.context;
    }

    axios
        .post(props.url + '/list', params)
        .then(response => actions.value = response.data);
}

function runAction(action, values, done) {
    emit('started');

    runServerAction({ action, values, done, url: props.url, selections: props.selections })
        .then(data => emit('completed', true, data))
        .catch(data => emit('completed', false, data));

    // TODO: Handle validation `errors` from server, which is passed into ConfirmableAction's errors prop below
}
</script>

<template>
    <ConfirmableAction
        ref="confirmableActions"
        v-if="hasSelections"
        v-for="action in actions"
        :key="action.handle"
        :action="action"
        :selections="selections.length"
        :errors="{}"
        @confirmed="runAction"
    />
    <slot
        v-if="showAlways || hasSelections"
        :actions="preparedActions"
    />
</template>
