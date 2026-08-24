export const UNGROUPED_SITE_GROUP_KEY = 'other';

export function siteGroupKey(item) {
    if (!item?.group && !item?.group_handle) {
        return UNGROUPED_SITE_GROUP_KEY;
    }

    return item.group_handle || item.group;
}

/**
 * Group sites/localizations by named site group. Ungrouped items come last under "Other".
 */
export function groupItemsBySiteGroup(items) {
    const groups = [];
    const indexByKey = new Map();

    for (const item of items) {
        const isUngrouped = siteGroupKey(item) === UNGROUPED_SITE_GROUP_KEY;
        const key = siteGroupKey(item);
        const label = isUngrouped ? __('Other') : item.group;

        if (!indexByKey.has(key)) {
            indexByKey.set(key, groups.length);
            groups.push({ key, label, items: [] });
        }

        groups[indexByKey.get(key)].items.push(item);
    }

    const named = groups.filter((group) => group.key !== UNGROUPED_SITE_GROUP_KEY);
    const other = groups.filter((group) => group.key === UNGROUPED_SITE_GROUP_KEY);

    return [...named, ...other];
}

export function hasNamedSiteGroups(groupsOrItems) {
    const groups = Array.isArray(groupsOrItems?.[0]?.items)
        ? groupsOrItems
        : groupItemsBySiteGroup(groupsOrItems ?? []);

    return groups.some((group) => group.key !== UNGROUPED_SITE_GROUP_KEY);
}

/**
 * Flatten grouped items into Combobox/Select options with header metadata.
 */
export function flatOptionsFromSiteGroups(groups, { filterItems = (items) => items } = {}) {
    let isFirstVisibleOption = true;

    return groups.flatMap((group) => {
        const items = filterItems(group.items);

        if (!items.length) {
            return [];
        }

        return items.map((item, index) => {
            const option = {
                ...item,
                _groupLabel: index === 0 ? group.label : null,
                _showGroupSeparator: index === 0 && !isFirstVisibleOption,
            };

            isFirstVisibleOption = false;

            return option;
        });
    });
}

export function selectedSiteGroupLabel(item, namedGroupsExist) {
    if (item?.group) {
        return item.group;
    }

    return namedGroupsExist ? __('Other') : null;
}

/**
 * Prefer an existing site in the target's group as the origin.
 * If the group has none yet, fall back to originBehavior.
 */
export function preferredOriginHandle(localizations, target, originBehavior = 'root') {
    const existing = (localizations ?? []).filter((localization) => localization.exists);

    if (!existing.length) {
        return null;
    }

    const targetKey = target ? siteGroupKey(target) : null;
    const sameGroup = targetKey
        ? existingInSiteOrder(existing).filter((localization) => siteGroupKey(localization) === targetKey)
        : [];

    if (sameGroup.length) {
        return groupOriginHandle(sameGroup, existing);
    }

    if (originBehavior !== 'root') {
        const active = existing.find((localization) => localization.active);
        if (active) {
            return active.handle;
        }
    }

    const root = existing.find((localization) => localization.root);
    if (root) {
        return root.handle;
    }

    return existing[0]?.handle ?? null;
}

/**
 * Prefer the first localization in the group (the one that entered from outside,
 * or the in-group root). If that site later leaves the group, follow it outside
 * instead of promoting another in-group site to Origin.
 */
function groupOriginHandle(sameGroup, allExisting) {
    const inGroup = new Set(sameGroup.map((localization) => localization.handle));
    const byHandle = new Map(allExisting.map((localization) => [localization.handle, localization]));

    const chainHead = sameGroup.find((localization) => (
        sameGroup.some((other) => other.origin_handle === localization.handle)
    ));

    if (chainHead) {
        return chainHead.handle;
    }

    const entryPoint = sameGroup.find(({ origin_handle: origin }) => {
        if (origin === undefined) return false;

        return !origin || !inGroup.has(origin);
    }) ?? sameGroup[0];

    if (!entryPoint) {
        return null;
    }

    const parentHandle = entryPoint.origin_handle;

    if (!parentHandle) {
        return entryPoint.handle;
    }

    if (inGroup.has(parentHandle)) {
        return entryPoint.handle;
    }

    const parent = byHandle.get(parentHandle);

    if (!parent) {
        return entryPoint.handle;
    }

    // Parent is outside the group. If anything exists that isn't that parent or
    // descended from it, the parent is a former in-group origin that moved out —
    // keep pointing at it. Otherwise this is the first localization in the group
    // (created from the global root/default) and it stays the group origin.
    const hasUnrelatedLocalizations = allExisting.some((localization) => (
        localization.handle !== parentHandle
        && !descendsFrom(localization, parentHandle, byHandle)
    ));

    return hasUnrelatedLocalizations ? parentHandle : entryPoint.handle;
}

function descendsFrom(localization, ancestorHandle, byHandle) {
    let current = localization;
    const seen = new Set();

    while (current?.origin_handle) {
        if (seen.has(current.handle)) {
            return false;
        }

        seen.add(current.handle);

        if (current.origin_handle === ancestorHandle) {
            return true;
        }

        current = byHandle.get(current.origin_handle);
    }

    return false;
}

function existingInSiteOrder(existing) {
    const byHandle = new Map(existing.map((localization) => [localization.handle, localization]));
    const ordered = siteOrderHandles()
        .map((handle) => byHandle.get(handle))
        .filter(Boolean);

    if (!ordered.length) {
        return existing;
    }

    const seen = new Set(ordered.map((localization) => localization.handle));
    const rest = existing.filter((localization) => !seen.has(localization.handle));

    return [...ordered, ...rest];
}

function siteOrderHandles() {
    const sites = typeof Statamic !== 'undefined' ? Statamic.$config?.get?.('sites') : null;

    if (!Array.isArray(sites) || !sites.length) {
        return [];
    }

    return sites.map((site) => site.handle);
}
