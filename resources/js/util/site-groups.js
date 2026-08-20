export const UNGROUPED_SITE_GROUP_KEY = 'other';

/**
 * Group sites/localizations by named site group. Ungrouped items come last under "Other".
 */
export function groupItemsBySiteGroup(items) {
    const groups = [];
    const indexByKey = new Map();

    for (const item of items) {
        const isUngrouped = !item.group && !item.group_handle;
        const key = isUngrouped
            ? UNGROUPED_SITE_GROUP_KEY
            : (item.group_handle || item.group);
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
        return __(item.group);
    }

    return namedGroupsExist ? __('Other') : null;
}
