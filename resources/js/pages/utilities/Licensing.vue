<script setup>
import Head from '@/pages/layout/Head.vue';
import { Header, Button, Card, CardPanel, Panel, Heading, Table, TableRow, TableCell, Badge, DocsCallout } from '@ui';

const props = defineProps([
    'requestError',
    'site',
    'statamic',
    'addons',
    'unlistedAddons',
    'configCached',
    'addToCartUrl',
    'usingLicenseKeyFile',
    'refreshUrl',
]);
</script>

<template>
    <div class="max-w-5xl mx-auto" data-max-width-wrapper>
        <Head :title="[__('Licensing'), __('Utilities')]" />

        <Header :title="__('Licensing')" icon="license">
            <Button
                :href="site.url"
                target="_blank"
                :text="__('Manage on statamic.com')"
            />
            <Button
                v-if="addToCartUrl"
                :href="addToCartUrl"
                target="_blank"
                :text="__('Buy Licenses')"
            />
            <Button
                :href="refreshUrl"
                variant="primary"
                :text="__('Sync')"
                v-tooltip="__('statamic::messages.licensing_sync_instructions')"
            />
        </Header>

        <Card v-if="requestError" class="w-full space-y-4 flex items-center justify-between">
            <Heading size="lg" class="mb-0!" :text="usingLicenseKeyFile ? __('statamic::messages.outpost_license_key_error') : __('statamic::messages.outpost_issue_try_later')" icon="warning-diamond" />
            <Button :href="refreshUrl" variant="primary">
                {{ __('Try Again') }}
            </Button>
        </Card>

        <section v-else class="space-y-6">
            <CardPanel v-if="configCached" :heading="__('Configuration is cached')">
                <p class="text-gray-700 text-sm" v-html="__('statamic::messages.licensing_config_cached_warning')" />
            </CardPanel>

            <CardPanel v-if="site.usesIncorrectKeyFormat" :heading="__('statamic::messages.licensing_incorrect_key_format_heading')">
                <p class="text-gray-700 text-sm" v-html="__('statamic::messages.licensing_incorrect_key_format_body')" />
            </CardPanel>

            <Panel :heading="__('Site')">
                <Card class="py-0!">
                    <Table class="w-full">
                        <TableRow>
                            <TableCell class="w-64 font-bold">
                                <div class="flex gap-2 sm:gap-3">
                                    <span class="little-dot mt-[0.45rem]" :class="site.valid ? 'bg-green-500' : 'bg-red-500 dark:bg-red-600'" />
                                    {{ site.key ?? __('No license key') }}
                                </div>
                            </TableCell>
                            <TableCell class="relative">
                                {{ site.domain?.url ?? '' }}
                                <span v-if="site.hasMultipleDomains" class="text-xs">
                                ({{ __('and :count more', { count: site.additionalDomainCount }) }})
                            </span>
                            </TableCell>
                            <TableCell class="text-end">
                                <Badge v-if="site.invalidReason" color="red">
                                    {{ site.invalidReason }}
                                </Badge>
                            </TableCell>
                        </TableRow>
                    </Table>
                </Card>
            </Panel>

            <Panel :heading="__('Core')">
                <Card class="py-0!">
                    <Table class="w-full">
                        <TableRow>
                            <TableCell class="w-64 font-bold">
                                <div class="flex gap-2 sm:gap-3">
                                    <span class="little-dot mt-[0.45rem]" :class="statamic.valid ? 'bg-green-500' : 'bg-red-500'" />
                                    <span>
                                    {{ __('Statamic') }}
                                    <span v-if="statamic.pro" class="text-pink">{{ __('Pro') }}</span>
                                    <template v-else>{{ __('Free') }}</template>
                                </span>
                                </div>
                            </TableCell>
                            <TableCell>{{ statamic.version }}</TableCell>
                            <TableCell class="text-end">
                                <Badge v-if="statamic.invalidReason" color="red">
                                    {{ statamic.invalidReason }}
                                </Badge>
                            </TableCell>
                        </TableRow>
                    </Table>
                </Card>
            </Panel>

            <Panel v-if="addons.length" :heading="__('Addons')">
                <Card class="py-0!">
                    <Table class="w-full">
                        <TableRow v-for="addon in addons" :key="addon.name">
                            <TableCell class="w-64">
                                <div class="flex gap-2 sm:gap-3">
                                    <span class="little-dot mt-[0.45rem]" :class="addon.valid ? 'bg-green-' : 'bg-red-500'" />
                                    <span class="font-bold">
                                    <a :href="addon.marketplaceUrl" class="underline">{{ addon.name }}</a>
                                </span>
                                    <div v-if="addon.edition" class="ms-auto">
                                        <Badge>{{ addon.edition }}</Badge>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>{{ addon.version }}</TableCell>
                            <TableCell class="text-red-700 text-end">{{ addon.invalidReason }}</TableCell>
                        </TableRow>
                    </Table>
                </Card>
            </Panel>

            <Panel v-if="unlistedAddons.length" :heading="__('Unlisted Addons')">
                <Card class="py-0!">
                    <Table class="w-full">
                        <TableRow v-for="addon in unlistedAddons" :key="addon.name">
                            <TableCell class="w-64">
                                <div class="flex gap-2 sm:gap-3">
                                    <span class="little-dot mt-[0.45rem] bg-green-500" />
                                    {{ addon.name }}
                                </div>
                            </TableCell>
                            <TableCell>{{ addon.version }}</TableCell>
                        </TableRow>
                    </Table>
                </Card>
            </Panel>
        </section>

        <DocsCallout :topic="__('Licensing')" url="licensing" />
    </div>
</template>
