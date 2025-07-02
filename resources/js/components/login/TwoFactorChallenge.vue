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
    <div
        class="rounded-2xl border border-gray-200 bg-white p-2 shadow-[0_8px_5px_-6px_rgba(0,0,0,0.12),_0_3px_8px_0_rgba(0,0,0,0.02),_0_30px_22px_-22px_rgba(39,39,42,0.35)] backdrop-blur-[2px]"
    >
        <div
            class="relative space-y-3 rounded-xl border border-gray-300 bg-white p-4 shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)]"
        >
            <header class="mb-8 flex flex-col items-center justify-center py-3">
                <ui-card class="mb-4 flex items-center justify-center p-2!">
                    <ui-icon name="phone-lock" class="size-5" />
                </ui-card>
                <ui-heading :level="1" size="xl">
                    {{ __('Two-Factor Authentication') }}
                </ui-heading>
                <ui-description
                    v-if="mode === 'code'"
                    :text="__('statamic::messages.two_factor_challenge_code_instructions')"
                    class="text-center"
                />
                <ui-description
                    v-else
                    :text="__('statamic::messages.two_factor_recovery_code_instructions')"
                    class="text-center"
                />
            </header>
            <form
                ref="formEl"
                method="POST"
                :action="formAction"
                class="email-login space-y-6 select-none"
                @submit="busy = true"
            >
                <input type="hidden" name="_token" :value="csrfToken" />
                <input v-if="redirect" type="hidden" name="redirect" :value="redirect" />

                <ui-field
                    v-if="mode === 'code'"
                    :label="__('Code')"
                    :error="hasError('code') ? errorFor('code') : null"
                >
                    <ui-input
                        type="text"
                        name="code"
                        pattern="[0-9]*"
                        maxlength="6"
                        inputmode="numeric"
                        autofocus
                        autocomplete="one-time-code"
                    />
                </ui-field>

                <ui-field
                    v-if="mode === 'recovery_code'"
                    :label="__('Recovery Code')"
                    :error="hasError('recovery_code') ? errorFor('recovery_code') : null"
                >
                    <ui-input type="text" name="recovery_code" maxlength="21" autofocus autocomplete="off" />
                </ui-field>

                <ui-button type="submit" variant="primary" :disabled="busy" :loading="busy" class="w-full">{{
                    __('Continue')
                }}</ui-button>

                <button
                    v-if="mode === 'code'"
                    class="cursor-pointer text-xs text-gray-500 hover:text-gray-800"
                    type="button"
                    @click="mode = 'recovery_code'"
                >
                    {{ __('Use recovery code') }}
                </button>

                <button
                    v-if="mode === 'recovery_code'"
                    class="cursor-pointer text-xs text-gray-500 hover:text-gray-800"
                    type="button"
                    @click="mode = 'code'"
                >
                    {{ __('Use one-time code') }}
                </button>
            </form>
        </div>
    </div>
</template>
