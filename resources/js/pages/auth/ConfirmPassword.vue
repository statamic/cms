<script setup>
import Head from '@/pages/layout/Head.vue';
import Outside from '@/pages/layout/Outside.vue';
import { AuthCard, Input, Field, Button, Description } from '@ui';
import { computed } from 'vue';
import { Form } from '@inertiajs/vue3';

defineOptions({ layout: Outside });

const props = defineProps(['method', 'status', 'submitUrl', 'resendUrl']);
const isConfirmingPassword = computed(() => props.method === 'password_confirmation');
const isUsingVerificationCode = computed(() => props.method === 'verification_code');
</script>

<template>
    <Head :title="__('Confirm Password')" />

    <AuthCard
        icon="key"
        :title="isConfirmingPassword ? __('Confirm Your Password') : __('Verification Code')"
        :description="isConfirmingPassword
            ? __('statamic::messages.elevated_session_enter_password')
            : __('statamic::messages.elevated_session_enter_verification_code')"
    >
        <Description v-if="status" variant="success" :text="status" class="mb-6" />

        <Form method="post" :action="submitUrl" class="flex flex-col gap-6" v-slot="{ errors }">
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
    </AuthCard>
</template>
