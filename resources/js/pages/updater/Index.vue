<script setup>
import { Link } from '@inertiajs/vue3';
import Head from '@/pages/layout/Head.vue';
import { Header, Card, Panel, Table, TableRow, TableCell, Badge, Heading, Button, DocsCallout, CommandPaletteItem } from '@ui';
import { computed } from 'vue';

const props = defineProps(['requestError', 'statamic', 'addons']);

const securityUpdateAvailable = computed(() => props.statamic.security || props.addons.some(addon => addon.security));
</script>

<template>
    <Head :title="__('Updates')" />

    <div class="max-w-page mx-auto">
        <Header :title="__('Updates')" icon="updates">
            <template #actions>
                <Badge v-if="securityUpdateAvailable" :text="__('Security update available')" color="red" size="lg" icon="alert-warning-exclamation-mark" />
            </template>
        </Header>

        <Card v-if="requestError" class="w-full space-y-4 flex items-center justify-between">
            <Heading size="lg" class="mb-0!" :text="__('statamic::messages.outpost_issue_try_later')" icon="warning-diamond" />
            <Button :href="cp_url('updater')" variant="primary">
                {{ __('Try Again') }}
            </Button>
        </Card>

        <section v-else class="space-y-6">
            <Panel :heading="__('Core')">
                <Card class="py-0!">
                    <Table class="w-full">
                        <TableRow>
                            <TableCell class="w-64 font-bold">
                                <CommandPaletteItem
                                    category="Actions"
                                    :text="[__('Updates'), __('Core'), __('Statamic')]"
                                    icon="updates"
                                    :url="cp_url('updater/statamic')"
                                    prioritize
                                    v-slot="{ url }"
                                >
                                    <Link :href="url" v-text="__('Statamic')" />
                                </CommandPaletteItem>
                            </TableCell>
                            <TableCell>{{ statamic.currentVersion }}</TableCell>
                            <TableCell v-if="statamic.availableUpdatesCount" class="text-right">
                                <Badge
                                    size="sm"
                                    :text="__n('1 update|:count updates', statamic.availableUpdatesCount)"
                                    :color="statamic.security ? 'red' : 'amber'"
                                />
                            </TableCell>
                            <TableCell v-else class="text-right">{{ __('Up to date') }}</TableCell>
                        </TableRow>
                    </Table>
                </Card>
            </Panel>

            <Panel v-if="addons.length" :heading="__('Addons')">
                <Card class="py-0!">
                    <Table class="w-full">
                        <TableRow v-for="addon in addons" :key="addon.slug">
                            <TableCell class="w-64 font-bold">
                                <CommandPaletteItem
                                    category="Actions"
                                    :text="[__('Updates'), __('Addons'), addon.name]"
                                    icon="updates"
                                    :url="cp_url(`updater/${addon.slug}`)"
                                    v-slot="{ url }"
                                >
                                    <Link :href="url" v-text="addon.name" />
                                </CommandPaletteItem>
                            </TableCell>
                            <TableCell>{{ addon.version }}</TableCell>
                            <TableCell v-if="addon.availableUpdatesCount" class="text-right">
                                <Badge
                                    size="sm"
                                    :text="__n('1 update|:count updates', addon.availableUpdatesCount)"
                                    :color="addon.security ? 'red' : 'amber'"
                                />
                            </TableCell>
                            <TableCell v-else class="text-right">{{ __('Up to date') }}</TableCell>
                        </TableRow>
                    </Table>
                </Card>
            </Panel>
        </section>

        <DocsCallout v-if="!requestError" :topic="__('Updates')" url="updating" />
    </div>
</template>
