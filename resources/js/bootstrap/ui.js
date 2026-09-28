export default async (app) => {
    // For every export in `@ui`, register it as a Vue component with a `ui-` prefix.
    const components = await import('@ui');

    for (const [name, component] of Object.entries(components)) {
        app.component(
            `ui-${name.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase()}`,
            component
        );
    }
};
