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

let commandPaletteActions = computed(() => {
    return preparedActions.value.map(action => Statamic.$commandPalette.add({
        text: action.title,
        icon: action.icon,
        action: action.run,
        prioritize: true,
    }));
});

watch(commandPaletteActions, function (_, oldActions) {
    oldActions.forEach(action => action.remove());
})

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

let errors = ref({});

function runAction(action, values, onSuccess, onError) {
    errors.value = {};
    emit('started');

    runServerAction({ action, values, onSuccess, onError, url: props.url, selections: props.selections })
        .then(data => emit('completed', true, data))
        .catch(data => {
            errors.value = data.errors;
            emit('completed', false, data);
        });
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
        :errors="errors"
        @confirmed="runAction"
    />
    <slot
        v-if="showAlways || hasSelections"
        :actions="preparedActions"
    />
</template>
