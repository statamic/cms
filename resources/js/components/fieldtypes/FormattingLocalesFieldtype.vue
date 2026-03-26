<template>
    <Combobox
        clearable
        taggable
        :options="options"
        :read-only="isReadOnly"
        :placeholder="null"
        :model-value="value"
        @update:modelValue="comboboxUpdated"
    />
</template>

<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { dateFormatter } from '@api';
import { Combobox } from '@/components/ui';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { isReadOnly, update } = Fieldtype.use(emit, props);

const candidateLocales = [
    'ar', 'az', 'cs', 'da', 'de', 'de-CH', 'en', 'es', 'et', 'fa', 'fr',
    'hu', 'id', 'it', 'ja', 'ms', 'nb', 'nl', 'pl', 'pt', 'pt-BR', 'ru',
    'sl', 'sv', 'tr', 'uk', 'vi', 'zh-CN', 'zh-TW',
];

const displayNames = typeof Intl.DisplayNames !== 'undefined'
    ? new Intl.DisplayNames([document.documentElement.lang || 'en'], { type: 'language' })
    : null;

const options = computed(() => {
    const locales = Intl.DateTimeFormat.supportedLocalesOf(candidateLocales);
    const now = new Date();

    const formatted = locales.map((locale) => {
        const language = locale.split('-')[0];
        const display = displayNames?.of(language);
        const sample = dateFormatter.withLocale(locale, (formatter) => formatter.format(now, 'datetime'));

        return {
            value: locale,
            label: display
                ? `${locale} (${display}) - ${sample}`
                : `${locale} - ${sample}`,
        };
    });

    return [
        { value: 'language', label: __('Same as language') },
        ...formatted,
    ];
});

function comboboxUpdated(value) {
    update(value || null);
}
</script>
