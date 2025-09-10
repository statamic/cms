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
        cleaned[iconName] = globbed[path];
    }
    return cleaned;
}

export function registerIconSetFromStrings(name, icons) {
    registry.set(name, { type: 'strings', data: icons });
}

registerIconSet('default', import.meta.glob('../../../../resources/svg/icons/*.svg', { query: '?raw', import: 'default' }));
