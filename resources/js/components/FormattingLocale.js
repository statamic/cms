let defaultLocale = null;

export function getLocale() {
    return defaultLocale ?? Intl.DateTimeFormat().resolvedOptions().locale;
}

export function getDefaultLocale() {
    return defaultLocale;
}

export function setDefaultLocale(locale) {
    defaultLocale = locale;
}
