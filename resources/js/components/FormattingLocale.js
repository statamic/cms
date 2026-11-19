let defaultLocale = null;

export function normalizeLocale(locale) {
    return typeof locale === 'string' ? locale.replace('_', '-') : locale;
}

export function getLocale() {
    return defaultLocale ?? Intl.DateTimeFormat().resolvedOptions().locale;
}

export function getDefaultLocale() {
    return defaultLocale;
}

export function setDefaultLocale(locale) {
    defaultLocale = normalizeLocale(locale);
}
