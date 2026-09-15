const LOGIC_KEYS = ['hidden', 'if', 'unless', 'if_any', 'unless_any', 'always_save'];

export function writeFieldConditions(field, conditions) {
    const config = { ...field.config };

    const merged = {
        hidden: conditions.hidden ?? config.hidden ?? false,
        if: conditions.if || null,
        unless: conditions.unless || null,
        if_any: conditions.if_any || null,
        unless_any: conditions.unless_any || null,
        always_save: conditions.always_save ?? config.always_save ?? false,
    };

    LOGIC_KEYS.forEach((key) => {
        if (merged[key]) {
            config[key] = merged[key];
        } else {
            delete config[key];
        }
    });

    field.config = config;

    if (field.type === 'reference') {
        const overrides = new Set((field.config_overrides ?? []).filter((key) => !LOGIC_KEYS.includes(key)));
        LOGIC_KEYS.forEach((key) => key in config && overrides.add(key));
        field.config_overrides = [...overrides];
    }
}
