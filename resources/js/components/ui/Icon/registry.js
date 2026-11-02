const registry = new Map();

export function getIconSet(name) {
    return registry.get(name);
}

export function registerIconSet(name, globbed) {
    registry.set(name, { type: 'glob', data: rekeyGlobs(globbed) });
}

function rekeyGlobs(globbed) {
    const cleaned = {};
    for (const path in globbed) {
        const parts = path.split('/');
        const fileName = parts[parts.length - 1];
        const iconName = fileName.replace('.svg', '');
        cleaned[iconName] = importOnce(globbed[path]);
    }
    return cleaned;
}

function importOnce(loader) {
    let promise = null;

    return () => {
        promise ??= loader().catch((error) => {
            promise = null;
            throw error;
        });

        return promise;
    };
}

export function registerIconSetFromStrings(name, icons) {
    registry.set(name, { type: 'strings', data: icons });
}

registerIconSet('default', import.meta.glob('../../../../svg/icons/*.svg', { query: '?raw', import: 'default' }));
