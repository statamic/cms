<script setup>
import { requireElevatedSession } from '@statamic/components/elevated-sessions';
import { ref, watch } from 'vue';
import axios from 'axios';

const emit = defineEmits(['reset-complete']);

const props = defineProps({
    url: String,
    isEnforced: Boolean,
});

const loading = ref(false);
const confirming = ref(false);

watch(loading, (loading) => {
    Statamic.$progress.loading(loading);
});

function confirm() {
    requireElevatedSession()
        .then(() => (confirming.value = true))
        .catch(() => {});
}

function disable() {
    loading.value = true;

    axios
        .delete(props.url)
        .then((response) => {
            Statamic.$toast.success(__('Disabled two factor authentication'));
            emit('reset-complete');
            if (response.data.redirect) window.location = response.data.redirect;
        })
        .catch((error) => Statamic.$toast.error(error.message))
        .finally(() => {
            loading.value = false;
            confirming.value = false;
        });
}
</script>

<template>
    <div>
        <slot :confirm="confirm" />

        <confirmation-modal
            v-if="confirming"
            :title="__('Are you sure?')"
            :danger="true"
            @confirm="disable"
            @cancel="confirming = false"
        >
            <p class="mb-2" v-html="__('statamic::messages.disable_two_factor_authentication')"></p>

            <p
                v-html="
                    isEnforced
                        ? __('statamic::messages.disable_two_factor_authentication_current_user_enforced')
                        : __('statamic::messages.disable_two_factor_authentication_current_user_optional')
                "
            ></p>
        </confirmation-modal>
    </div>
</template>
