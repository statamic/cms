<script setup>
import Head from '@/pages/layout/Head.vue';
import Outside from '@/pages/layout/Outside.vue';
import { AuthCard, Heading, Description, Icon, Card, Input, Field, Button } from '@ui';
import { Link, Form, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

defineOptions({ layout: Outside });

const props = defineProps(['errors', 'action', 'token', 'email', 'redirect', 'title', 'loginUrl']);

const errors = ref(props.errors);
watch(() => props.errors, (newErrors) => errors.value = newErrors);

const email = ref(props.email);
const password = ref('');
const passwordConfirmation = ref('');
const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.post(props.action, {
        email: email.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
        token: props.token,
        redirect: props.redirect,
    }, {
        onBefore: () => {
            processing.value = true;
            errors.value = {};
        },
        onSuccess: (e) => {
            return window.location.href = e.url;
        },
        onError: () => processing.value = false
    });
}
</script>

<template>
    <Head :title="title" />
    <AuthCard
        icon="key"
        :title="title"
        :description="__('statamic::messages.set_new_password_instructions')"
    >
        <form
            @submit.prevent="submit"
            class="flex flex-col gap-6"
        >
            <Field :label="__('Email Address')" :error="errors?.email">
                <Input v-model="email" autofocus type="email" />
            </Field>

            <Field :label="__('Password')" :error="errors?.password">
                <Input v-model="password" type="password" />
            </Field>

            <Field :label="__('Confirm Password')" :error="errors?.password_confirmation">
                <Input v-model="passwordConfirmation" type="password" />
            </Field>

            <Button type="submit" variant="primary" :text="title" />
        </form>
    </AuthCard>

    <div class="mt-4 w-full text-center dark:mt-6">
        <Link
            :href="loginUrl"
            class="text-ui-accent text-sm hover:text-ui-accent/80 dark:text-dark-ui-accent dark:hover:text-dark-ui-accent/80"
            v-text="__('Back to login')"
        />
    </div>
</template>
