<script setup>
import Head from '@/pages/layout/Head.vue';
import Outside from '@/pages/layout/Outside.vue';
import { AuthCard, Input, Field, Button, Separator, Checkbox, ErrorMessage } from '@ui';
import { Link, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { usePasskey } from '@/composables/passkey';

defineOptions({ layout: Outside });

const props = defineProps([
    'errors',
    'emailLoginEnabled',
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
        onSuccess: (page) => {
			if (page.component === 'auth/two-factor/Challenge') {
				return;
			}

	        window.location.href = props.referer;
        },
        onError: () => processing.value = false
    });
}

const passkey = usePasskey();

const showPasskeyLogin = computed(() => {
    return props.emailLoginEnabled && passkey.supported;
})

const emailAutocomplete = computed(() => {
    let tokens = 'username';
    if (showPasskeyLogin.value) tokens += ' webauthn';
    return tokens;
});

const passwordAutocomplete = computed(() => {
    let tokens = 'current-password';
    if (showPasskeyLogin.value) tokens += ' webauthn';
    return tokens;
});

async function loginWithPasskey(useBrowserAutofill = false) {
    await passkey.authenticate(
        props.passkeyOptionsUrl,
        props.passkeyVerifyUrl,
        (data) => {
            if (data.redirect) {
                window.location = data.redirect;
            }
        },
        useBrowserAutofill
    );
}

onMounted(() => {
    if (showPasskeyLogin.value) loginWithPasskey(true);
});

onUnmounted(() => passkey.cancel());
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
                    <Input v-model="email" name="email" autofocus tabindex="1" :autocomplete="emailAutocomplete" />
                </Field>

                <Field :label="__('Password')" :error="errors?.password">
                    <Input v-model="password" name="password" type="password" :autocomplete="passwordAutocomplete" tabindex="2" />
                    <template #actions>
                        <Link
                            :href="forgotPasswordUrl"
                            class="text-ui-accent-text text-sm hover:text-ui-accent-text/80"
                            tabindex="6"
                            v-text="__('Forgot password?')"
                        />
                    </template>
                </Field>

                <Checkbox v-model="remember" name="remember" :label="__('Remember me')" tabindex="4" />

                <Button type="submit" variant="primary" :disabled="processing" :text="__('Continue')" tabindex="5" />
            </form>

            <template v-if="showOAuth || showPasskeyLogin">
                <Separator v-if="emailLoginEnabled" variant="dots" :text="__('Or sign in with')" class="py-3" />
                <div class="flex flex-col gap-y-4">
                    <template v-if="showPasskeyLogin">
                        <Button
                            :text="__('Passkey')"
                            class="w-full"
                            :icon="passkey.waiting.value ? null : 'key'"
                            :disabled="passkey.waiting.value"
                            :loading="passkey.waiting.value"
                            @click="loginWithPasskey()"
                        />
                        <ErrorMessage v-if="passkey.error.value" :text="passkey.error.value" />
                    </template>
                    <div v-if="showOAuth" class="flex gap-4 justify-center items-center">
                        <Button
                            v-for="provider in providers"
                            :key="provider.name"
                            as="a"
                            class="flex-1 [&_svg]:opacity-100!"
                            :href="provider.url"
                            :icon="provider.icon"
                            :icon-only="!!provider.icon"
                            v-tooltip="__('Sign in with :provider', { provider: provider.label })"
                        >
                            <span class="sr-only">{{ __('Sign in with :provider', { provider: provider.label }) }}</span>
                            <span v-if="!provider.icon">{{ provider.label }}</span>
                        </Button>
                    </div>
                </div>
            </template>
        </div>
    </AuthCard>
</template>
