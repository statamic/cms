<script setup>
import Head from '@/pages/layout/Head.vue';
import { Header, Dropdown, DropdownMenu, DropdownLabel, DropdownItem, Button, Subheading, Panel, DocsCallout, Icon, StatusIndicator } from '@ui';

defineProps(['collections', 'taxonomies', 'navs', 'assetContainers', 'globals', 'forms', 'userBlueprint', 'groupBlueprint', 'additional']);
</script>

<template>
    <Head :title="__('Blueprints')" />

    <Header :title="__('Blueprints')" icon="blueprints">
        <Dropdown align="end">
            <template #trigger>
                <Button
                    :text="__('Create Blueprint')"
                    icon-append="chevron-down"
                    variant="primary"
                />
            </template>

            <DropdownMenu>
                <template v-if="collections.length">
                    <DropdownLabel :text="__('Collections')" />
                    <DropdownItem
                        v-for="collection in collections"
                        :key="collection.handle"
                        :href="collection.create_url"
                        icon="collections"
                        :text="__(collection.title)"
                    />
                </template>

                <template v-if="taxonomies.length">
                    <DropdownLabel :text="__('Taxonomies')" />
                    <DropdownItem
                        v-for="taxonomy in taxonomies"
                        :key="taxonomy.handle"
                        :href="taxonomy.create_url"
                        icon="taxonomies"
                        :text="__(taxonomy.title)"
                    />
                </template>
            </DropdownMenu>
        </Dropdown>
    </Header>

    <section class="space-y-6 starting-style-transition-children">
        <template v-if="collections.length">
            <Subheading size="lg" class="mb-2" :text="__('Collections')" />
            <Panel>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Blueprint') }}</th>
                            <th class="text-end!" scope="col">{{ __('Collection') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="collection in collections" :key="collection.handle">
                            <tr v-for="blueprint in collection.blueprints" :key="blueprint.handle">
                                <td>
                                    <div class="flex items-center gap-2">
                                        <Icon name="collections" class="text-gray-500 me-1" />
                                        <StatusIndicator :status="blueprint.hidden ? 'hidden' : 'published'" v-tooltip="__(blueprint.hidden ? 'Hidden' : 'Visible')" />
                                        <a :href="blueprint.edit_url" v-text="__(blueprint.title)" />
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="pe-2 font-mono text-xs text-gray-500 dark:text-gray-400" v-text="__(collection.title)" />
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </Panel>
        </template>

        <template v-if="taxonomies.length">
            <Subheading size="lg" class="mb-2" :text="__('Taxonomies')" />
            <Panel>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Blueprint') }}</th>
                            <th class="text-end!">{{ __('Taxonomy') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="taxonomy in taxonomies" :key="taxonomy.handle">
                            <tr v-for="blueprint in taxonomy.blueprints" :key="blueprint.handle">
                                <td>
                                    <div class="flex items-center gap-2">
                                        <Icon name="taxonomies" class="text-gray-500 me-1" />
                                        <StatusIndicator :status="blueprint.hidden ? 'hidden' : 'published'" v-tooltip="__(blueprint.hidden ? 'Hidden' : 'Visible')" />
                                        <a :href="blueprint.edit_url" v-text="__(blueprint.title)" />
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="pe-2 font-mono text-xs text-gray-500 dark:text-gray-400" v-text="__(taxonomy.title)" />
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </Panel>
        </template>

        <template v-if="navs.length">
            <Subheading size="lg" class="mb-2" :text="__('Navigation')" />
            <Panel>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-start!">{{ __('Blueprint') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="nav in navs" :key="nav.handle">
                            <td>
                                <div class="flex items-center gap-2">
                                    <Icon name="navigation" class="text-gray-500 me-1" />
                                    <a :href="nav.edit_url" v-text="__(nav.title)" />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </Panel>
        </template>

        <template v-if="assetContainers.length">
            <Subheading size="lg" class="mb-2" :text="__('Asset Containers')" />
            <Panel>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-start!">{{ __('Blueprint') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="container in assetContainers" :key="container.handle">
                            <td>
                                <div class="flex items-center gap-2">
                                    <Icon name="assets" class="text-gray-500 me-1" />
                                    <a :href="container.edit_url" v-text="__(container.title)" />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </Panel>
        </template>

        <template v-if="globals.length">
            <Subheading size="lg" class="mb-2" :text="__('Globals')" />
            <Panel>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-start!">{{ __('Blueprint') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="global in globals" :key="global.handle">
                            <td>
                                <div class="flex items-center gap-2">
                                    <Icon name="globals" class="text-gray-500 me-1" />
                                    <a :href="global.edit_url" v-text="__(global.title)" />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </Panel>
        </template>

        <template v-if="forms.length">
            <Subheading size="lg" class="mb-2" :text="__('Forms')" />
            <Panel>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-start!">{{ __('Blueprint') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="form in forms" :key="form.handle">
                            <td>
                                <div class="flex items-center gap-2">
                                    <Icon name="forms" class="text-gray-500 me-1" />
                                    <a :href="form.edit_url" v-text="__(form.title)" />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </Panel>
        </template>

        <Subheading size="lg" class="mb-2" :text="__('Users')" />
        <Panel>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-start!">{{ __('Blueprint') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <Icon name="users" class="text-gray-500 me-1" />
                                <a :href="userBlueprint.edit_url">{{ __('User') }}</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <Icon name="groups" class="text-gray-500 me-1" />
                                <a :href="groupBlueprint.edit_url">{{ __('Group') }}</a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </Panel>

        <template v-for="namespace in additional" :key="namespace.namespace">
            <Subheading size="lg" class="mb-2" :text="namespace.title" />
            <Panel>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-start!">{{ __('Blueprint') }}</th>
                            <th scope="col" class="actions-column"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="blueprint in namespace.blueprints" :key="blueprint.handle">
                            <td>
                                <div class="flex items-center gap-2">
                                    <Icon name="blueprints" class="text-gray-500 me-1" />
                                    <a :href="cp_url(`blueprints/additional/${blueprint.namespace}/${blueprint.handle}/edit`)" v-text="__(blueprint.title)" />
                                </div>
                            </td>
                            <td class="actions-column">
                                <template v-if="blueprint.is_resettable">
                                    <Dropdown class="mr-3">
                                        <DropdownMenu>
                                            <DropdownItem
                                                :text="__('Reset')"
                                                variant="destructive"
                                                @click="$refs[`resetter_${blueprint.namespace}_${blueprint.handle}`].confirm()"
                                            />
                                        </DropdownMenu>
                                    </Dropdown>
                                    <blueprint-resetter
                                        :ref="`resetter_${blueprint.namespace}_${blueprint.handle}`"
                                        :resource="blueprint"
                                        reload
                                    />
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </Panel>
        </template>
    </section>

    <DocsCallout :topic="__('Blueprints')" url="blueprints" />
</template>
