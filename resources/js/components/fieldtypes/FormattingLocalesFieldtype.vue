<template>
    <Combobox
        clearable
        taggable
        :options="options"
        :search-keys="['label', 'value']"
        :read-only="isReadOnly"
        :placeholder="null"
        :model-value="value"
        @update:modelValue="comboboxUpdated"
    >
        <template #selected-option="{ option: { value, label } }">
            <template v-if="value === 'language'">{{ label }}</template>
            <div v-else class="w-full flex justify-between">
                <div class="text-start flex-1">
                    {{ getLabel(value) }}
                    <span class="ms-4 text-gray-500 dark:text-gray-400" v-text="value" />
                </div>
                <span class="text-gray-500 dark:text-gray-400" v-text="getSample(value)" />
            </div>
        </template>
        <template #option="{ value, label }">
            <template v-if="value === 'language'">{{ label }}</template>
            <div v-else class="w-full flex justify-between">
                <div class="text-start flex-1">
                    {{ label }}
                    <span class="ms-4 text-gray-500 dark:text-gray-400" v-text="value" />
                </div>
                <span class="text-gray-500 dark:text-gray-400" v-text="getSample(value)" />
            </div>
        </template>
    </Combobox>
</template>

<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { dateFormatter, toast } from '@api';
import { Combobox } from '@/components/ui';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { isReadOnly, update } = Fieldtype.use(emit, props);

const candidateLocales = [
    'ar', 'az', 'cs', 'da', 'de', 'de-AT', 'de-CH', 'en', 'en-GB', 'es', 'es-419', 'et', 'fa', 'fr',
    'fr-CA', 'fr-CH', 'hu', 'id', 'it', 'it-CH', 'ja', 'ms', 'nb', 'nl', 'pl', 'pt', 'pt-BR', 'ru',
    'sl', 'sv', 'tr', 'uk', 'vi', 'zh-CN', 'zh-TW',
];

const uiLocale = document.documentElement.lang || 'en';

const displayNames = typeof Intl.DisplayNames !== 'undefined'
    ? new Intl.DisplayNames([uiLocale], { type: 'language', languageDisplay: 'standard' })
    : null;

const options = computed(() => {
    const locales = Intl.DateTimeFormat.supportedLocalesOf([...candidateLocales, ...browserLocales()]);

    const collator = new Intl.Collator(uiLocale);

    const formatted = locales
        .map((locale) => ({ value: locale, label: getLabel(locale) }))
        .sort((a, b) => collator.compare(a.label, b.label));

    return [
        { value: 'language', label: __('Same as language') },
        ...formatted,
    ];
});

function browserLocales() {
    try {
        return Intl.getCanonicalLocales(navigator.languages ?? []);
    } catch {
        return [];
    }
}

function getLabel(locale) {
    try {
        return displayNames?.of(locale) ?? locale;
    } catch {
        return locale;
    }
}

function getSample(locale) {
    return dateFormatter.withLocale(locale, (formatter) => formatter.format(new Date, 'datetime'));
}

function comboboxUpdated(value) {
    if (value && !isValidLocale(value)) {
        update(null);
        toast.error(__('statamic::messages.preference_formatting_locale_invalid'));
        return;
    }

    update(value || null);
}

function isValidLocale(value) {
    if (value === 'language') {
        return true;
    }

    try {
        return Intl.DateTimeFormat.supportedLocalesOf([value]).length > 0;
    } catch {
        return false;
    }
}
</script>
