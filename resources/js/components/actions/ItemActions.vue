<script setup>
import { ref, computed, useTemplateRef } from 'vue';
import useActions from './Actions.js';
import ConfirmableAction from './ConfirmableAction.vue';
import axios from 'axios';

const props = defineProps({
    url: { type: String, required: true },
    actions: { type: Array },
    context: { type: Object, default: () => ({}) },
    item: { required: true },
    isDirty: { type: Boolean, default: false },
});

const emit = defineEmits(['started', 'completed']);

const { prepareActions, runServerAction } = useActions();

const confirmableActions = useTemplateRef('confirmableActions');
const actions = ref(props.actions);
const actionsLoaded = ref(props.actions !== undefined);

let preparedActions = computed(() => {
    return prepareActions(actions.value, confirmableActions.value);
});

let errors = ref({});

function runAction(action, values, done) {
    errors.value = {};
    emit('started');

    runServerAction({ action, values, done, url: props.url, selections: [props.item] })
        .then((data) => emit('completed', true, data))
        .catch((data) => {
            errors.value = data.errors;
            emit('completed', false, data);
        });
}

function loadActions() {
    if (actionsLoaded.value) {
        return;
    }

    let params = {
        selections: [props.item],
    };

    if (props.context) {
        params.context = props.context;
    }

    axios.post(props.url + '/list', params).then((response) => (actions.value = response.data));

    actionsLoaded.value = true;
}
</script>

<template>
    <ConfirmableAction
        ref="confirmableActions"
        v-for="action in actions"
        :key="action.handle"
        :action="action"
        :selections="1"
        :errors="errors"
        :is-dirty="isDirty"
        @confirmed="runAction"
    />
    <slot :actions="preparedActions" :load-actions="loadActions" />
</template>
