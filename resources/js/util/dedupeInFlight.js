const namespaces = new Map();

/**
 * Share identical in-flight async work across callers within a namespace.
 * Once the promise settles the entry is removed — this is not a settled-result cache.
 *
 * The factory is invoked synchronously so callers that assert immediately after
 * kicking off the request still see the underlying work start.
 *
 * @param {string} namespace
 * @param {string} key
 * @param {() => Promise<any>} factory
 * @returns {Promise<any>}
 */
export function dedupeInFlight(namespace, key, factory) {
    let map = namespaces.get(namespace);

    if (!map) {
        map = new Map();
        namespaces.set(namespace, map);
    }

    let entry = map.get(key);

    if (entry) return entry;

    entry = Promise.resolve(factory()).finally(() => {
        if (map.get(key) === entry) {
            map.delete(key);
        }
    });

    map.set(key, entry);

    return entry;
}
