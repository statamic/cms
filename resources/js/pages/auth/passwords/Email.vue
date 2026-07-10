<script setup>
import Head from '@/pages/layout/Head.vue';
import Outside from '@/pages/layout/Outside.vue';
import { AuthCard, Heading, Description, Icon, Card, Input, Field, Button } from '@ui';
import { Link, Form } from '@inertiajs/vue3';

defineOptions({ layout: Outside });

defineProps(['action', 'loginUrl']);
</script>

<template>
    <Head :title="__('Reset Password')" />
    <AuthCard>
        <Form
            method="post"
            :action="action"
            error-bag="user.forgot_password"
            v-slot="{ errors, wasSuccessful }"
        >
            <header class="flex flex-col justify-center items-center mb-8 py-3">
                <template v-if="!wasSuccessful">
                    <Card class="p-2! mb-4 flex items-center justify-center">
                        <Icon name="key" class="size-5" />
                    </Card>
                    <Heading :level="1" size="xl" :text="__('Reset Your Password')" />
                    <Description :text="__('statamic::messages.forgot_password_enter_email')" class="text-center" />
                </template>
                <template v-if="wasSuccessful">
                    <Card class="p-2! mb-4 flex items-center justify-center">
                        <Icon name="mail-check" class="size-5" />
                    </Card>
                    <Heading :level="1" size="xl" :text="__('Password Reset Sent')" />
                    <Description :text="__('statamic::messages.forgot_password_sent')" class="text-center" />
                </template>
            </header>
            <div class="flex flex-col gap-6" v-if="!wasSuccessful">
                <Field :label="__('Email Address')" :error="errors.email">
                    <Input name="email" autofocus type="email" />
                </Field>
                <Button type="submit" variant="primary" :text="__('Submit')" />
            </div>
        </Form>

    </AuthCard>

    <div class="mt-4 w-full text-center dark:mt-6">
        <Link
            :href="loginUrl"
            class="text-ui-accent-text text-sm hover:text-ui-accent-text/80"
            v-text="__('I remember my password')"
        />
    </div>
</template>
