<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    initialMode: {
        type: String,
        default: 'code',
    },
    errors: {
        type: Object,
    },
    formAction: {
        type: String,
    },
    csrfToken: {
        type: String,
    },
    redirect: {
        type: String,
    },
});

const formEl = ref(null);
const busy = ref(false);
const mode = ref(props.initialMode);

onMounted(() => {
    if (hasErrors()) {
        formEl.value.parentElement.parentElement.classList.add('animation-shake');
    }
});

function hasErrors() {
    return Object.keys(props.errors).length > 0;
}

function hasError(field) {
    return !!props.errors[field];
}

function errorFor(field) {
    return props.errors[field][0];
}
</script>

<template>
    <form ref="formEl" method="POST" :action="formAction" class="email-login select-none" @submit="busy = true">
        <input type="hidden" name="_token" :value="csrfToken" />
        <input v-if="redirect" type="hidden" name="redirect" :value="redirect" />

        <h1 class="mb-2 text-lg text-gray-800 dark:text-dark-175">
            {{ __('Two Factor Authentication') }}
        </h1>
        <p v-if="mode === 'code'" class="mb-4 text-sm text-gray dark:text-dark-175">
            {{ __('statamic::messages.two_factor_challenge_code_instructions') }}
        </p>
        <p v-if="mode === 'recovery_code'" class="mb-4 text-sm text-gray dark:text-dark-175">
            {{ __('statamic::messages.two_factor_recovery_code_instructions') }}
        </p>

        <div v-if="mode === 'code'" class="mb-8">
            <label class="mb-2" for="input-code">{{ __('Code') }}</label>
            <input
                type="text"
                class="input-text"
                name="code"
                pattern="[0-9]*"
                maxlength="6"
                inputmode="numeric"
                autofocus
                autocomplete="one-time-code"
                id="input-code"
            />
            <div class="mt-2 text-xs text-red-500" v-if="hasError('code')" v-text="errorFor('code')" />
        </div>

        <div v-if="mode === 'recovery_code'" class="mb-8">
            <label class="mb-2" for="input-recovery-code">{{ __('Recovery Code') }}</label>
            <input
                type="text"
                class="input-text"
                name="recovery_code"
                maxlength="21"
                autofocus
                autocomplete="off"
                id="input-recovery-code"
            />
            <div
                class="mt-2 text-xs text-red-500"
                v-if="hasError('recovery_code')"
                v-text="errorFor('recovery_code')"
            />
        </div>

        <div class="flex items-center justify-between">
            <button v-if="mode === 'code'" class="text-btn text-xs" type="button" @click="mode = 'recovery_code'">
                {{ __('Use recovery code') }}
            </button>

            <button v-if="mode === 'recovery_code'" class="text-btn text-xs" type="button" @click="mode = 'code'">
                {{ __('Use one-time code') }}
            </button>

            <button type="submit" class="btn-primary" :disabled="busy">{{ __('Continue') }}</button>
        </div>
    </form>
</template>
