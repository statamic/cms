<script setup>
import Head from '@/pages/layout/Head.vue';
import DynamicHtmlRenderer from '@/components/DynamicHtmlRenderer.vue';
import { Icon, EmptyStateMenu, EmptyStateItem, DocsCallout } from '@ui';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';

const props = defineProps({
    widgets: Array,
    pro: Boolean,
    blueprintsUrl: String,
    collectionsCreateUrl: String,
    navigationCreateUrl: String,
});

if (props.widgets.length === 0) useArchitecturalBackground();

function classes(widget) {
    return `${widget.classes} widget-w-${widget.width}`;
}
</script>

<template>
    <Head :title="__('Dashboard')" />

    <template v-if="widgets.length">
        <ui-header :title="__('Dashboard')" icon="dashboard" />

        <div class="widgets @container/widgets">
            <div
                v-for="widget in widgets"
                class="starting-style-transition"
                :class="classes(widget)"
            >
                <component v-if="widget.component" :is="widget.component.name" v-bind="widget.component.props" />
                <DynamicHtmlRenderer v-else :html="widget.html" />
            </div>
        </div>
    </template>

    <template v-else>
        <header class="py-8 pt-16 text-center">
            <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
                <Icon name="dashboard" class="size-5 text-gray-500" />
                {{ __('Dashboard') }}
            </h1>
        </header>

        <EmptyStateMenu
            :heading="__('statamic::messages.getting_started_widget_header')"
            :subheading="__('statamic::messages.getting_started_widget_intro')"
        >
            <EmptyStateItem
                href="https://statamic.dev"
                icon="docs"
                :heading="__('Read the Documentation')"
                :description="__('statamic::messages.getting_started_widget_docs')"
            />
            <EmptyStateItem
                v-if="!pro"
                href="https://statamic.dev/licensing"
                icon="pro-ribbon"
                :heading="__('Enable Pro Mode')"
                :description="__('statamic::messages.getting_started_widget_pro')"
            />
            <EmptyStateItem
                :href="blueprintsUrl"
                icon="blueprints"
                :heading="__('Create a Blueprint')"
                :description="__('statamic::messages.blueprints_intro')"
            />
            <EmptyStateItem
                :href="collectionsCreateUrl"
                icon="collections"
                :heading="__('Create a Collection')"
                :description="__('statamic::messages.getting_started_widget_collections')"
            />
            <EmptyStateItem
                :href="navigationCreateUrl"
                icon="navigation"
                :heading="__('Create a Navigation')"
                :description="__('statamic::messages.getting_started_widget_navigation')"
            />
        </EmptyStateMenu>
    </template>

    <DocsCallout :topic="__('Widgets')" url="widgets" />
</template>
