export default async (app) => {
    const components = import.meta.glob('@/components/ui/**/*.vue');

    for (const [path, component] of Object.entries(components)) {
        // Split full path and remove leading segments up to 'components'
        const segments = path
            .split('/')
            .reduce((acc, segment, index, array) => {
                if (segment === 'ui' || acc.length > 0) {
                    acc.push(segment.replace(/\.\w+$/, '')); // Remove extension
                }
                return acc;
            }, [])
            .slice(1); // Remove 'components' segment

        // Handle Index files specially (named Name/Name.vue)
        if (
            segments.length > 1 &&
            segments[segments.length - 1].toLowerCase() === segments[segments.length - 2].toLowerCase()
        ) {
            segments.pop();
        }

        // Convert to kebab case
        const componentName = segments
            .join('-') // Join segments with hyphens
            .replace(/([a-z])([A-Z])/g, '$1-$2') // Add hyphen between camelCase words
            .toLowerCase(); // Convert to kebab-case

        app.component(`ui-${componentName}`, (await component()).default);
    }
};
