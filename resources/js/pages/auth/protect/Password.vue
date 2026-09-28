<script setup>
import Head from '@/pages/layout/Head.vue';
import Outside from '@/pages/layout/Outside.vue';
import { AuthCard, Input, Field, Button, ErrorMessage } from '@ui';
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

defineOptions({ layout: Outside });

const props = defineProps(['errors', 'token', 'submitUrl']);

const processing = ref(false);
const password = ref('');
const errors = ref(props.errors?.passwordProtect || {});
watch(() => props.errors, (newErrors) => errors.value = newErrors.passwordProtect);
const shaking = computed(() => Object.keys(errors.value).length ? 'animation-shake' : '');

const submit = () => {
    processing.value = true;
    router.post(props.submitUrl, {
        password: password.value,
        token: props.token
    }, {
        onBefore: () => {
            processing.value = true;
            errors.value = {};
        },
        onSuccess: () => {
            return false;
        },
        onError: () => processing.value = false
    });
}
</script>

<template>
    <Head :title="__('Protected Page')" />
    <AuthCard
        icon="key"
        :title="__('Protected Page')"
        :description="token ? __('statamic::messages.password_protect_enter_password') : __('statamic::messages.password_protect_token_missing')"
        :class="[shaking]"
    >
        <form
            @submit.prevent="submit"
            class="flex flex-col gap-6"
        >
            <Field :error="errors?.password">
                <Input
                    type="password"
                    v-model="password"
                    autofocus
                    :placeholder="__('statamic::messages.password_protect_enter_password')"
                />
            </Field>

            <ErrorMessage v-if="errors?.token" :text="errors?.token" />

            <Button type="submit" variant="primary" class="w-full" :text="__('Submit')" />
        </form>
    </AuthCard>
</template>
