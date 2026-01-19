<script setup>
import Head from '@/pages/layout/Head.vue';
import DynamicHtmlRenderer from '@/components/DynamicHtmlRenderer.vue';
import { Icon, EmptyStateMenu, EmptyStateItem, DocsCallout, Alert } from '@ui';
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
    return `${widget.classes} ${tailwindWidthClass(widget.width)}`;
}

function tailwindWidthClass(width) {
    const sizes = {
        sm: 'w-full @2xl:w-1/2 @4xl:w-1/3 @8xl:w-1/4',
        md: 'w-full @2xl:w-1/2 @4xl:w-1/2 @8xl:w-1/3',
        lg: 'w-full @2xl:w-full @4xl:w-2/3 @8xl:w-3/4',
        full: 'w-full',
    };

    // For backward compatibility, map old numeric widths to new sizes
    const legacyMap = {
        25: 'sm',
        33: 'sm',
        50: 'md',
        66: 'md',
        75: 'lg',
        100: 'full'
    };

    const size = typeof width === 'number' ? (legacyMap[width] ?? 'full') : width;

    return sizes[size] ?? sizes.md;
}
</script>

<template>
    <Head :title="__('Dashboard')" />

    <template v-if="widgets.length">
        <ui-header :title="__('Dashboard')" icon="dashboard" />

        <div class="mb-6 space-y-3">
            <Alert variant="default" text="This is a default alert message" />
            <Alert variant="warning" text="This is a warning alert message" />
            <Alert variant="error" text="This is an error alert message" />
            <Alert variant="success" text="This is a success alert message" />
            <Alert variant="warning" icon="git" text="This alert has a custom icon" />
            <Alert variant="success">
                <strong>Success!</strong> This alert uses a slot for custom content.
            </Alert>
            <Alert variant="warning">
                <h3>Please run your migrations</h3>
                <p>The importer uses Laravel's job batching feature to keep track of the import progress, however, it requires a <code>job_batches</code> table in your database. Before you can run the importer, you will need to run <code>php artisan migrate</code>. This alert uses a heading for the title and a paragraph for the message.</p>
            </Alert>
            <Alert variant="default">
                <h3>This is a default alert message</h3>
                <p>The importer uses Laravel's job batching feature to keep track of the import progress, however, it requires a <code>job_batches</code> table in your database. Before you can run the importer, you will need to run <code>php artisan migrate</code>. This alert uses a heading for the title and a paragraph for the message.</p>
            </Alert>
            <Alert variant="success">
                <h3>This is a success alert message</h3>
                <p>The importer uses Laravel's job batching feature to keep track of the import progress, however, it requires a <code>job_batches</code> table in your database. Before you can run the importer, you will need to run <code>php artisan migrate</code>. This alert uses a heading for the title and a paragraph for the message.</p>
            </Alert>
            <Alert variant="error">
                <h3>This is an error alert message</h3>
                <p>The importer uses Laravel's job batching feature to keep track of the import progress, however, it requires a <code>job_batches</code> table in your database. Before you can run the importer, you will need to run <code>php artisan migrate</code>. This alert uses a heading for the title and a paragraph for the message.</p>
            </Alert>
        </div>

        <div class="widgets @container/widgets flex flex-wrap gap-y-6 -mx-2 sm:-mx-3">
            <div
                v-for="widget in widgets"
                class="px-3 starting-style-transition starting-style-transition--siblings"
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
