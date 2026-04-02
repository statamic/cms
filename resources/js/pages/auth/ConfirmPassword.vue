<script setup>
import Head from '@/pages/layout/Head.vue';
import { AuthCard, Input, Field, Button, Description, ErrorMessage, Separator } from '@ui';
import { computed } from 'vue';
import { Form, router } from '@inertiajs/vue3';
import { usePasskey } from '@/composables/passkey';

const props = defineProps(['method', 'allowPasskey', 'status', 'submitUrl', 'resendUrl', 'passkeyOptionsUrl']);
const isConfirmingPassword = computed(() => props.method === 'password_confirmation');
const isUsingVerificationCode = computed(() => props.method === 'verification_code');
const isOnlyUsingPasskey = computed(() => props.method === 'passkey');
const passkey = usePasskey();

async function confirmWithPasskey() {
    await passkey.authenticate(
        props.passkeyOptionsUrl,
        props.submitUrl,
        (response) => router.get(response.redirect)
    );
}
</script>

<template>
    <Head :title="__('Confirm Password')" />

    <AuthCard
        icon="key"
        class="max-w-md mx-auto mt-8"
        :title="__('Confirm Your Identity')"
        :description="__('statamic::messages.elevated_session_reauthenticate')"
    >
        <Description v-if="status" variant="success" :text="status" class="mb-6" />

        <Form v-if="!isOnlyUsingPasskey" method="post" :action="submitUrl" class="flex flex-col gap-6" v-slot="{ errors }">
            <Field v-if="isConfirmingPassword" :label="__('Password')" :error="errors.password">
                <Input name="password" type="password" viewable autofocus />
            </Field>

            <Field v-if="isUsingVerificationCode" :label="__('Verification Code')" :error="errors.verification_code">
                <Input name="verification_code" autofocus />
            </Field>

            <div class="flex items-center gap-4">
                <Button type="submit" variant="primary" :text="__('Submit')" class="flex-1" />

                <Button
                    v-if="isUsingVerificationCode"
                    as="href"
                    class="flex-1"
                    :href="resendUrl"
                    :text="__('Resend code')"
                />
            </div>
        </Form>

        <template v-if="allowPasskey">
            <Separator v-if="!isOnlyUsingPasskey" variant="dots" :text="__('or')" class="py-3" />

            <Button
                class="w-full"
                :variant="isOnlyUsingPasskey ? 'primary' : 'default'"
                :text="__('Confirm with Passkey')"
                :icon="passkey.waiting.value ? null : 'key'"
                :disabled="passkey.waiting.value"
                :loading="passkey.waiting.value"
                @click="confirmWithPasskey"
            />
            <ErrorMessage v-if="passkey.error.value" :text="passkey.error.value" />
        </template>
    </AuthCard>
</template>
