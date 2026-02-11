<script setup>
import { ref, computed, useTemplateRef, watch, useSlots } from 'vue';
import useActions from './Actions.js';
import ConfirmableAction from './ConfirmableAction.vue';
import axios from 'axios';

const props = defineProps({
    url: { type: String },
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

watch(
	() => props.actions,
	() => actions.value = props.actions,
	{ deep: true }
);

let preparedActions = computed(() => {
    return prepareActions(actions.value, confirmableActions.value);
});

let preparedPinnedActions = computed(() => {
	return prepareActions(actions.value?.filter(action => action.pinned), confirmableActions.value);
});

let errors = ref({});

function runAction(action, values, onSuccess, onError) {
    errors.value = {};
    emit('started');

    runServerAction({ action, values, onSuccess, onError, url: props.url, selections: [props.item] })
        .then((data) => {
            if (props.actions === undefined) {
                actionsLoaded.value = false;
            }

            emit('completed', true, data);
        })
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

const slots = useSlots();
const showPinnedActions = computed(() => preparedPinnedActions.value && !!slots.pinned);

defineExpose({
    preparedActions,
});
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
	<slot v-if="showPinnedActions" name="pinned" :actions="preparedPinnedActions" />
</template>
