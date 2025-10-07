<script setup>
import { useForm } from '@inertiajs/vue3';
import Head from '@/pages/layout/Head.vue';
import { Header, CardPanel, Input, Button, Table, TableRows, TableRow, TableCell } from '@ui';

const props = defineProps(['sendUrl', 'defaultEmail', 'config', 'errors']);

const form = useForm({
    email: props.defaultEmail,
});

function send() {
    form.post(props.sendUrl);
}
</script>

<template>
    <Head :title="__('Email')" />

    <Header :title="__('Email')" icon="mail-settings" />

    <CardPanel :heading="__('Send Test Email')">
        <form @submit.prevent="send">
            <div class="flex items-center gap-2">
                <Input
                    v-model="form.email"
                    name="email"
                />
                <Button
                    variant="primary"
                    type="submit"
                    :loading="form.processing"
                >
                    {{ __('Send') }}
                </Button>
            </div>
            <p v-if="errors.email" class="mt-4 text-red-700 text-sm">{{ errors.email }}</p>
        </form>
    </CardPanel>

    <CardPanel
        class="mt-6"
        :heading="__('Configuration')"
        :subheading="__('statamic::messages.email_utility_configuration_description', { path: config.path })"
    >
        <Table>
            <TableRows>
                <TableRow class="[&_td:first-child]:font-medium">
                    <TableCell>{{ __('Default Mailer') }}</TableCell>
                    <TableCell>{{ config.default }}</TableCell>
                </TableRow>
                <template v-if="config.smtp">
                    <TableRow>
                        <TableCell>{{ __('Host') }}</TableCell>
                        <TableCell>{{ config.smtp.host }}</TableCell>
                    </TableRow>
                    <TableRow>
                        <TableCell>{{ __('Port') }}</TableCell>
                        <TableCell>{{ config.smtp.port }}</TableCell>
                    </TableRow>
                    <TableRow>
                        <TableCell>{{ __('Encryption') }}</TableCell>
                        <TableCell>{{ config.smtp.encryption }}</TableCell>
                    </TableRow>
                    <TableRow>
                        <TableCell>{{ __('Username') }}</TableCell>
                        <TableCell>{{ config.smtp.username }}</TableCell>
                    </TableRow>
                    <TableRow>
                        <TableCell>{{ __('Password') }}</TableCell>
                        <TableCell>{{ config.smtp.password }}</TableCell>
                    </TableRow>
                </template>
                <TableRow v-if="config.sendmail">
                    <TableCell>{{ __('Sendmail') }}</TableCell>
                    <TableCell>{{ config.sendmail.path }}</TableCell>
                </TableRow>
                <TableRow>
                    <TableCell>{{ __('Default From Address') }}</TableCell>
                    <TableCell>{{ config.from.address }}</TableCell>
                </TableRow>
                <TableRow>
                    <TableCell>{{ __('Default From Name') }}</TableCell>
                    <TableCell>{{ config.from.name }}</TableCell>
                </TableRow>
                <TableRow>
                    <TableCell>{{ __('Markdown theme') }}</TableCell>
                    <TableCell>{{ config.markdown.theme }}</TableCell>
                </TableRow>
                <TableRow>
                    <TableCell>{{ __('Markdown paths') }}</TableCell>
                    <TableCell>
                        <template v-for="(path, index) in config.markdown.paths" :key="index">
                            {{ path }}<br v-if="index < config.markdown.paths.length - 1" />
                        </template>
                    </TableCell>
                </TableRow>
            </TableRows>
        </Table>
    </CardPanel>
</template>