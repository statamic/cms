export default function (value) {
    if (Array.isArray(value)) {
        return [...value];
    } else if (value !== null && typeof value === 'object') {
        return { ...value };
    }
    return value;
}
