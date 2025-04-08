import { h } from 'vue';

// Cache for icon load promises
const iconCache = new Map();

export function preloadIcon(name) {
    if (!iconCache.has(name)) {
        // Handle direct SVG strings
        if (name.startsWith('<svg')) {
            iconCache.set(name, Promise.resolve({ render: () => h('div', { innerHTML: name }) }));
        } else {
            // Handle file imports
            iconCache.set(name, import(`../../../svg/icons/${name}.svg`));
        }
    }
    return iconCache.get(name);
}

export function getIconFromCache(name) {
    return iconCache.get(name);
}

export function hasIcon(name) {
    return iconCache.has(name);
}
