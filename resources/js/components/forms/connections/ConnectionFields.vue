<script setup lang="ts">
// Renders one PublishContainer per connection, with the connection as the container root.
// Fieldtypes that read sibling values through injectPublishContext() rely on that, so don't
// render two of these for the same connection. Use the default slot and pick() instead.
import { computed, inject } from 'vue';
import { PublishContainer, PublishFields, PublishFieldsProvider } from '@ui';
import { connectionContextKey } from './context';

type BlueprintField = { handle: string; [key: string]: unknown };

const props = withDefaults(defineProps<{
    /** Handles of the fields to render, in order. Defaults to every field in the blueprint's first tab. */
    fields?: string[];
    /** Handles of fields to leave out. */
    except?: string[];
    /** Extra values made available to field conditions. */
    extraValues?: Record<string, unknown>;
    /** Wraps the fields in a bordered box. */
    bordered?: boolean;
    /** The connection being edited. Defaults to the one provided by ConnectionList. */
    connection?: Record<string, unknown>;
    /** The blueprint's publish array. Defaults to the one passed to ConnectionList. */
    blueprint?: Record<string, any>;
    /** Fieldtype meta for this connection. Defaults to the one managed by ConnectionList. */
    meta?: Record<string, unknown>;
    /** Validation errors for this connection, keyed by field path. Defaults to the ones from ConnectionList. */
    errors?: Record<string, string[]>;
    /** Prefix for the container name. Defaults to ConnectionList's name. */
    name?: string;
}>(), {
    bordered: true,
});

const context = inject(connectionContextKey, null);

const connection = computed(() => props.connection ?? context?.value.connection ?? {});
const blueprint = computed(() => props.blueprint ?? context?.value.blueprint);
const meta = computed(() => props.meta ?? context?.value.meta ?? {});
const errors = computed(() => props.errors ?? context?.value.errors ?? {});
const containerName = computed(() => `${props.name ?? context?.value.name ?? 'connection'}-${connection.value.id}`);

const allFields = computed<BlueprintField[]>(() =>
    (blueprint.value?.tabs?.[0]?.sections ?? []).flatMap((section) => section.fields ?? []),
);

const pick = (...handles: string[]): BlueprintField[] =>
    handles
        .map((handle) => allFields.value.find((field) => field.handle === handle))
        .filter((field): field is BlueprintField => field !== undefined);

const visibleFields = computed<BlueprintField[]>(() => {
    const fields = props.fields ? pick(...props.fields) : allFields.value;

    return props.except ? fields.filter((field) => !props.except.includes(field.handle)) : fields;
});
</script>

<template>
    <div :class="{ 'rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-700 dark:bg-gray-900': bordered }">
        <PublishContainer
            :blueprint
            :errors
            :model-value="connection"
            :meta
            :extra-values="extraValues"
            :name="containerName"
            :track-dirty-state="false"
        >
            <slot name="before" :connection />
            <slot :connection :fields="visibleFields" :pick>
                <PublishFieldsProvider :fields="visibleFields">
                    <PublishFields />
                </PublishFieldsProvider>
            </slot>
            <slot name="after" :connection />
        </PublishContainer>
    </div>
</template>
