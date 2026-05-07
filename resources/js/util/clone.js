export default function clone(value) {
    if (Array.isArray(value)) {
        return [...value];
    } else if (value !== null && typeof value === 'object') {
        return { ...value };
    }
    return value;
}

export function deepClone(value) {
    if (Array.isArray(value)) {
        return value.map(item => deepClone(item));
    } else if (value !== null && typeof value === 'object') {
        return Object.fromEntries(
            Object.entries(value).map(([key, val]) => [key, deepClone(val)])
        );
    }
    return value;
}
