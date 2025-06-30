import path from 'path';

function addAliases(config) {
    if (!config.resolve) config.resolve = {};

    const jsDir = path.resolve('/@fs' + __dirname + '/../');

    const aliases = {
        '@statamic/ui': `${jsDir}/components/ui/index.js`,
        '@statamic': jsDir,
        statamic: `${jsDir}/exports.js`,
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
