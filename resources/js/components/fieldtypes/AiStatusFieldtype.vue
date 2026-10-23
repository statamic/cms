<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { Button, Icon, injectPublishContext } from '@ui';
import { computed, ref, watch } from 'vue';

// Prototype: the read-only parts of Settings → AI (see the AiStatus fieldtype). Everything here is made up.
const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);

const { values } = injectPublishContext();

const providers = {
    anthropic: 'Anthropic',
    openai: 'OpenAI',
    google: 'Google',
    mistral: 'Mistral',
    ollama: 'Ollama',
};

// "Test connection": pretends to call the provider, and fails only when there's nothing to connect with
const connection = ref('idle'); // idle, testing, connected, failed
const connectionError = ref('');

function testConnection() {
    connection.value = 'testing';

    setTimeout(() => {
        const ollama = values.value.provider === 'ollama';
        const missing = ollama ? !values.value.ollama_url : !values.value.api_key;

        if (missing) {
            connection.value = 'failed';
            connectionError.value = ollama ? __('Add the Ollama URL first.') : __('Add an API key first.');
            return;
        }

        connection.value = 'connected';
    }, 1200);
}

// Changing what it connects with means the last test no longer counts
watch(() => [values.value.provider, values.value.api_key, values.value.ollama_url, values.value.model], () => (connection.value = 'idle'));

const connectedTo = computed(() => [providers[values.value.provider], values.value.model || __("the provider's recommended model")].filter(Boolean).join(' · '));

// This month's spending, against the limit if there is one
const spent = 12.4;
const limit = computed(() => Number(values.value.monthly_limit) || null);
const usedPercent = computed(() => (limit.value ? Math.min(100, (spent / limit.value) * 100) : 0));
const resets = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 1).toLocaleDateString(undefined, { day: 'numeric', month: 'long' });
const money = (amount) => `$${amount.toFixed(2)}`;
const usageText = computed(() =>
    limit.value
        ? __('of :limit spent this month. Resets on :date.', { limit: money(limit.value), date: resets })
        : __('spent this month. Resets on :date.', { date: resets }),
);
</script>

<template>
    <div v-if="config.show === 'connection'" class="flex flex-wrap items-center gap-3">
        <Button
            size="sm"
            icon="refresh"
            :text="__('Test connection')"
            :class="connection === 'testing' && 'motion-safe:[&_svg]:animate-spin'"
            :aria-disabled="connection === 'testing'"
            @click="connection !== 'testing' && testConnection()"
        />
        <p role="status" class="flex items-center gap-1.5 text-sm">
            <template v-if="connection === 'testing'">
                <span class="text-gray-500 dark:text-gray-400">{{ __('Checking…') }}</span>
            </template>
            <template v-else-if="connection === 'connected'">
                <Icon name="checkmark" class="size-3.5 text-green-600 dark:text-green-400" />
                <span class="text-gray-700 dark:text-gray-300">{{ __('Connected to :provider', { provider: connectedTo }) }}</span>
            </template>
            <template v-else-if="connection === 'failed'">
                <Icon name="warning-diamond" class="size-3.5 text-red-600 dark:text-red-400" />
                <span class="text-red-700 dark:text-red-300">{{ connectionError }}</span>
            </template>
        </p>
    </div>

    <div v-else-if="config.show === 'usage'" class="space-y-2">
        <p class="text-sm text-gray-700 dark:text-gray-300">
            <span class="font-medium text-gray-900 dark:text-white">{{ money(spent) }}</span>{{ ' ' }}{{ usageText }}
        </p>
        <div v-if="limit" class="h-1.5 w-full max-w-80 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10" role="presentation">
            <div class="h-full rounded-full bg-primary" :style="{ width: `${usedPercent}%` }" />
        </div>
    </div>
</template>
