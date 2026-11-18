export function addConfigOverrides(field, keys) {
    if (field.type !== 'reference' || keys.length === 0) return;

    field.config_overrides = [...new Set([...(field.config_overrides ?? []), ...keys])];
}
