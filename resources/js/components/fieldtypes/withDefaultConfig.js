export default function withDefaultConfig(type, config) {
    const { common, types } = Statamic.$config.get('fieldtypeConfigs');

    return { ...common, ...types[type], ...config }
}
