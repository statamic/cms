<script setup>
import { ref, computed, useTemplateRef, watch } from 'vue';
import useActions from './Actions.js';
import ConfirmableAction from './ConfirmableAction.vue';
import useSkeletonDelay from '@/composables/skeleton-delay.js';
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
const loading = ref(false);
const shouldShowSkeleton = useSkeletonDelay(loading);
let loadActionsRequest = null;

watch(
    () => props.actions,
    () => {
        actions.value = props.actions;
        actionsLoaded.value = props.actions !== undefined;
    },
    { deep: true }
);

let preparedActions = computed(() => {
    return prepareActions(actions.value, confirmableActions.value);
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
        return Promise.resolve(actions.value);
    }

    if (loading.value) {
        return loadActionsRequest;
    }

    let params = {
        selections: [props.item],
    };

    if (props.context) {
        params.context = props.context;
    }

    loading.value = true;

    loadActionsRequest = axios
        .post(props.url + '/list', params)
        .then((response) => {
            actions.value = response.data;
            actionsLoaded.value = true;

            return response.data;
        })
        .finally(() => {
            loading.value = false;
            loadActionsRequest = null;
        });

    return loadActionsRequest;
}

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
    <slot
        :actions="preparedActions"
        :load-actions="loadActions"
        :loading="loading"
        :should-show-skeleton="shouldShowSkeleton"
    />
</template>
