import path from 'path';
import { fileURLToPath } from 'url';

function addAliases(config) {
    if (!config.resolve) config.resolve = {};

    const dir = path.dirname(fileURLToPath(import.meta.url));
    const aliases = {
        '@statamic/cms': path.resolve(dir, '../../dist-package'),
    };

    config.resolve.alias = { ...aliases, ...config.resolve.alias };
}

export default function () {
    return {
        name: 'statamic',
        config(config) {
            addAliases(config);
        },
    };
}
