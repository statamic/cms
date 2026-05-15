<script setup lang="ts">
import { Badge, Icon, PublishContainer, PublishFieldsProvider, PublishFields } from '@ui';
import { injectBuilderContext } from '@/pages/forms/Builder.vue';

const { inspecting: fieldtype } = injectBuilderContext();
</script>

<template>
    <div>
        <!-- Mobile -->
        <div class="@container relative pt-6 pb-12 px-2.5 pe-4.5 min-[1000px]:hidden">
            <div class="space-y-6 pt-8">
                <div class="flex items-center gap-2">
                    <div class="size-4">
                        <Icon :name="fieldtype.icon" class="size-4 text-gray-500 dark:text-gray-300" />
                    </div>
                    <a class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">
                        <span class="truncate">{{ __(fieldtype.title) }}</span>
                    </a>
                </div>

                <p v-if="fieldtype.description" class="text-zinc-600">{{ __(fieldtype.description) }}</p>

                <div v-if="fieldtype.example">
                    <Badge class="mb-2" size="sm" :text="__('Example')" />
                    <div class="bg-white rounded-md p-4 border" inert>
                        <PublishContainer
                            :meta="{[fieldtype.handle]: fieldtype.example.meta}"
                            :blueprint="{tabs: [{ fields: fieldtype.example.config }]}"
                            :model-value="{[fieldtype.handle]: fieldtype.example.value}"
                        >
                            <PublishFieldsProvider :fields="[fieldtype.example.config]">
                                <PublishFields />
                            </PublishFieldsProvider>
                        </PublishContainer>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop -->
        <div class="@container relative pt-6 pb-12 px-2.5 pe-4.5 max-[1000px]:hidden">
            <div class="space-y-6 pt-8">
                <div class="flex items-center gap-2">
                    <div class="size-4">
                        <Icon :name="fieldtype.icon" class="size-4 text-gray-500 dark:text-gray-300" />
                    </div>
                    <a class="inline-flex min-w-0 items-center gap-1.5 text-xl font-medium antialiased">
                        <span class="truncate">{{ __(fieldtype.title) }}</span>
                    </a>
                </div>

                <p v-if="fieldtype.description" class="text-zinc-600">{{ __(fieldtype.description) }}</p>

                <div v-if="fieldtype.example">
                    <Badge class="mb-2" size="sm" :text="__('Example')" />
                    <div class="bg-white rounded-md p-4 border" inert>
                        <PublishContainer
                            :meta="{[fieldtype.handle]: fieldtype.example.meta}"
                            :blueprint="{tabs: [{ fields: fieldtype.example.config }]}"
                            :model-value="{[fieldtype.handle]: fieldtype.example.value}"
                        >
                            <PublishFieldsProvider :fields="[fieldtype.example.config]">
                                <PublishFields />
                            </PublishFieldsProvider>
                        </PublishContainer>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
