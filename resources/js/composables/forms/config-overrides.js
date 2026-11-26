export function addConfigOverrides(field, keys) {
    if (field.type !== 'reference' || keys.length === 0) return;

    field.config_overrides = [...new Set([...(field.config_overrides ?? []), ...keys])];
}

export function removeConfigOverrides(field, keys) {
    if (field.type !== 'reference' || keys.length === 0) return;

    field.config_overrides = (field.config_overrides ?? []).filter((key) => !keys.includes(key));
}
