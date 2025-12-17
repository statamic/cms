<script setup>
import Head from '@/pages/layout/Head.vue';
import Outside from '@/pages/layout/Outside.vue';
import { AuthCard, Field, Input, Button } from '@ui';
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

defineOptions({ layout: Outside });

const props = defineProps(['errors', 'mode', 'action', 'redirect']);

const errors = ref(props.errors);
watch(() => props.errors, (newErrors) => errors.value = newErrors);

const mode = ref(props.mode);
const code = ref('');
const recoveryCode = ref('');
const processing = ref(false);
const shaking = computed(() => Object.keys(errors.value).length ? 'animation-shake' : '');

const submit = () => {
    processing.value = true;
    router.post(props.action, {
        code: code.value,
        recovery_code: recoveryCode.value,
        redirect: props.redirect,
    }, {
        onBefore: () => {
            processing.value = true;
            errors.value = {};
        },
        onSuccess: (response) => window.location.href = response.url,
        onError: () => processing.value = false
    });
}
</script>

<template>
    <Head :title="__('Two-Factor Authentication')" />
    <AuthCard
        icon="phone-lock"
        :title="__('Two-Factor Authentication')"
        :description="mode === 'code'
            ? __('statamic::messages.two_factor_challenge_code_instructions')
            : __('statamic::messages.two_factor_recovery_code_instructions')"
        :class="[shaking]"
    >
        <form
            @submit.prevent="submit"
            class="flex flex-col gap-6"
        >
            <Field v-if="mode === 'code'" :label="__('Code')" :error="errors.code">
                <Input
                    type="text"
                    v-model="code"
                    pattern="[0-9]*"
                    maxlength="6"
                    inputmode="numeric"
                    autofocus
                    autocomplete="one-time-code"
                />
            </Field>

            <Field v-if="mode === 'recovery_code'" :label="__('Recovery Code')" :error="errors.recovery_code">
                <Input
                    type="text"
                    v-model="recoveryCode"
                    maxlength="21"
                    autofocus
                    autocomplete="off"
                />
            </Field>

            <Button type="submit" variant="primary" :disabled="processing" :loading="processing" class="w-full">{{ __('Continue') }}</Button>

            <button v-if="mode === 'code'" class="cursor-pointer text-xs text-gray-500 hover:text-gray-900 dark:hover:text-gray-300" type="button" @click="mode = 'recovery_code'">
                {{ __('Use recovery code') }}
            </button>

            <button v-if="mode === 'recovery_code'" class="cursor-pointer text-xs text-gray-500 hover:text-gray-900 dark:hover:text-gray-300" type="button" @click="mode = 'code'">
                {{ __('Use one-time code') }}
            </button>
        </form>
    </AuthCard>
</template>
