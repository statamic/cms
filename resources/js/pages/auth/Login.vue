<script setup>
import Head from '@/pages/layout/Head.vue';
import Outside from '@/pages/layout/Outside.vue';
import { AuthCard, Input, Field, Button, Separator, Checkbox } from '@ui';
import { Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { startAuthentication, browserSupportsWebAuthn } from '@simplewebauthn/browser';
import { ErrorMessage } from '@statamic/ui';

defineOptions({ layout: Outside });

const props = defineProps([
    'errors',
    'emailLoginEnabled',
    'passkeysEnabled',
    'passkeyOptionsUrl',
    'passkeyVerifyUrl',
    'oauthEnabled',
    'providers',
    'referer',
    'submitUrl',
    'forgotPasswordUrl',
])

const errors = ref(props.errors);
watch(() => props.errors, (newErrors) => errors.value = newErrors);

const email = ref('');
const password = ref('');
const remember = ref(false);
const processing = ref(false);
const shaking = computed(() => Object.keys(errors.value).length ? 'animation-shake' : '');
const showOAuth = computed(() => props.oauthEnabled && props.providers.length > 0);

const submit = () => {
    processing.value = true;
    router.post(props.submitUrl, {
        email: email.value,
        password: password.value,
        remember: remember.value
    }, {
        onBefore: () => {
            processing.value = true;
            errors.value = {};
        },
        onSuccess: () => window.location.href = props.referer,
        onError: () => processing.value = false
    });
}

const passkeyError = ref(null);
const passkeyWaiting = ref(false);

const showPasskeyLogin = computed(() => {
    return props.passkeysEnabled && browserSupportsWebAuthn();
})

async function loginWithPasskey() {
    passkeyWaiting.value = true;
    const authOptionsResponse = await fetch(props.passkeyOptionsUrl);
    const authOptionsJson = await authOptionsResponse.json();

    let startAuthResponse;
    try {
        startAuthResponse = await startAuthentication(authOptionsJson);
    } catch (e) {
        console.error(e);
        passkeyError.value = __('Authentication failed.');
        passkeyWaiting.value = false;
        return;
    }

    axios.post(props.passkeyVerifyUrl, startAuthResponse)
        .then(response => {
            if (response && response.data.redirect) {
                router.get(response.data.redirect);
                return;
            }

            passkeyError.value = response.data.message;
        }).catch(e => handleAxiosError(e))
        .finally(() => passkeyWaiting.value = false);
}


function handleAxiosError(e) {
    if (e.response) {
        const { message, errors } = e.response.data;
        passkeyError.value = message;
        return;
    }

    passkeyError.value = __('Something went wrong');
}
</script>

<template>
    <Head :title="__('Log in')" />
    <AuthCard
        icon="sign-in"
        :title="emailLoginEnabled ? __('Sign in with email') : __('Sign in with OAuth')"
        :description="__('Sign into your Statamic Control Panel')"
        :class="[shaking]"
    >
        <div>
            <form
                v-if="emailLoginEnabled"
                @submit.prevent="submit"
                class="flex flex-col gap-6"
            >
                <Field :label="__('Email')" :error="errors?.email">
                    <Input v-model="email" name="email" autofocus tabindex="1" />
                </Field>

                <Field :label="__('Password')" :error="errors?.password">
                    <Input v-model="password" name="password" type="password" tabindex="2" />
                    <template #actions>
                        <Link
                            :href="forgotPasswordUrl"
                            class="text-blue-400 text-sm hover:text-blue-600"
                            tabindex="6"
                            v-text="__('Forgot password?')"
                        />
                    </template>
                </Field>

                <Checkbox v-model="remember" name="remember" :label="__('Remember me')" tabindex="4" />

                <Button type="submit" variant="primary" :disabled="processing" :text="__('Continue')" tabindex="5" />
            </form>

            <template v-if="showAuth || showPasskeyLogin">
                <Separator v-if="emailLoginEnabled" variant="dots" :text="__('Or sign in with')" class="py-3" />
                <div class="flex flex-col gap-y-4">
                    <template v-if="showPasskeyLogin">
                        <Button
                            :text="__('Passkey')"
                            class="w-full"
                            :icon="passkeyWaiting ? null : 'key'"
                            :disabled="passkeyWaiting"
                            :loading="passkeyWaiting"
                            @click="loginWithPasskey"
                        />
                        <ErrorMessage v-if="passkeyError" :text="passkeyError" />
                    </template>
                    <div v-if="showOAuth" class="flex gap-4 justify-center items-center">
                        <Button
                            v-for="provider in providers"
                            :key="provider.name"
                            as="href"
                            class="flex-1"
                            :href="provider.url"
                            :icon="provider.icon"
                        />
                    </div>
                </div>
            </template>
        </div>
    </AuthCard>
</template>
