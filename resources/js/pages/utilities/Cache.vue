<script setup>
import { router } from '@inertiajs/vue3';
import Head from '@/pages/layout/Head.vue';
import { Header, Button, Panel, PanelHeader, Heading, Card, Description, Badge, DocsCallout, CommandPaletteItem } from '@ui';

const props = defineProps([
    'stache',
    'cache',
    'static',
    'images',
    'clearAllUrl',
    'clearStacheUrl',
    'warmStacheUrl',
    'clearStaticUrl',
    'clearApplicationUrl',
    'clearImageUrl',
]);

function clearAll() {
    router.post(props.clearAllUrl);
}

function clearStache() {
    router.post(props.clearStacheUrl);
}

function warmStache() {
    router.post(props.warmStacheUrl);
}

function clearStatic() {
    router.post(props.clearStaticUrl);
}

function clearApplication() {
    router.post(props.clearApplicationUrl);
}

function clearImage() {
    router.post(props.clearImageUrl);
}
</script>

<template>
    <Head :title="[__('Cache Manager'), __('Utilities')]" />

    <div class="max-w-5xl mx-auto">
        <Header :title="__('Cache Manager')" icon="cache">
            <CommandPaletteItem
                category="Actions"
                :text="__('Clear All')"
                icon="live-preview"
                :action="clearAll"
                prioritize
                v-slot="{ text }"
            >
                <Button
                    :text="text"
                    variant="primary"
                    @click="clearAll"
                />
            </CommandPaletteItem>
        </Header>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Panel class="h-full flex flex-col">
                <PanelHeader class="flex items-center justify-between min-h-10">
                    <Heading>{{ __('Content Stache') }}</Heading>
                    <div class="flex gap-2">
                        <CommandPaletteItem
                            category="Actions"
                            :text="[__('Warm'), __('Content Stache')]"
                            icon="fire-flame-burn-hot"
                            :action="warmStache"
                            v-slot="{ text }"
                        >
                            <Button :text="__('Warm')" size="sm" @click="warmStache" />
                        </CommandPaletteItem>
                        <CommandPaletteItem
                            category="Actions"
                            :text="[__('Clear'), __('Content Stache')]"
                            icon="live-preview"
                            :action="clearStache"
                            v-slot="{ text }"
                        >
                            <Button :text="__('Clear')" size="sm" @click="clearStache" />
                        </CommandPaletteItem>
                    </div>
                </PanelHeader>
                <Card class="flex-1">
                    <Description>{{ __('statamic::messages.cache_utility_stache_description') }}</Description>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <Badge :prepend="__('Records')">{{ stache.records }}</Badge>
                        <Badge v-if="stache.size" :prepend="__('Size')">{{ stache.size }}</Badge>
                        <Badge v-if="stache.time" :prepend="__('Build time')">{{ stache.time }}</Badge>
                        <Badge v-if="stache.rebuilt" :prepend="__('Last rebuild')">{{ stache.rebuilt }}</Badge>
                    </div>
                </Card>
            </Panel>

            <Panel class="h-full flex flex-col">
                <PanelHeader class="flex items-center justify-between min-h-10">
                    <Heading>{{ __('Static Page Cache') }}</Heading>
                    <div v-if="static.enabled" class="flex gap-2">
                        <CommandPaletteItem
                            category="Actions"
                            :text="[__('Clear'), __('Static Page Cache')]"
                            icon="live-preview"
                            :action="clearStatic"
                        >
                            <Button :text="__('Clear')" size="sm" @click="clearStatic" />
                        </CommandPaletteItem>
                    </div>
                </PanelHeader>
                <Card class="flex-1">
                    <Description>{{ __('statamic::messages.cache_utility_static_cache_description') }}</Description>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <Badge :prepend="__('Strategy')">{{ static.strategy }}</Badge>
                        <Badge v-if="static.enabled" :prepend="__('Cached Pages')">{{ static.count }}</Badge>
                    </div>
                </Card>
            </Panel>

            <Panel class="h-full flex flex-col">
                <PanelHeader class="flex items-center justify-between min-h-10">
                    <Heading>{{ __('Application Cache') }}</Heading>
                    <div class="flex gap-2">
                        <CommandPaletteItem
                            category="Actions"
                            :text="[__('Clear'), __('Application Cache')]"
                            icon="live-preview"
                            :action="clearApplication"
                        >
                            <Button :text="__('Clear')" size="sm" @click="clearApplication" />
                        </CommandPaletteItem>
                    </div>
                </PanelHeader>
                <Card class="flex-1">
                    <Description>{{ __('statamic::messages.cache_utility_application_cache_description') }}</Description>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <Badge :prepend="__('Driver')">{{ cache.driver }}</Badge>
                    </div>
                </Card>
            </Panel>

            <Panel class="h-full flex flex-col">
                <PanelHeader class="flex items-center justify-between min-h-10">
                    <Heading>{{ __('Image Cache') }}</Heading>
                    <div class="flex gap-2">
                        <CommandPaletteItem
                            category="Actions"
                            :text="[__('Clear'), __('Image Cache')]"
                            icon="live-preview"
                            :action="clearImage"
                        >
                            <Button :text="__('Clear')" size="sm" @click="clearImage" />
                        </CommandPaletteItem>
                    </div>
                </PanelHeader>
                <Card class="flex-1">
                    <Description>{{ __('statamic::messages.cache_utility_image_cache_description') }}</Description>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <Badge :prepend="__('Cached images')">{{ images.count }}</Badge>
                        <Badge :prepend="__('Size')">{{ images.size }}</Badge>
                    </div>
                </Card>
            </Panel>
        </div>

        <DocsCallout :topic="__('caching')" url="caching" />
    </div>
</template>
