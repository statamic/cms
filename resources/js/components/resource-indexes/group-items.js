export function groupResourceIndexItems(items, resourceIndex) {
    const { groups, fallbackGroup, hasSavedGroups } = resourceIndex;
    const grouped = hasSavedGroups || groups.length > 0;

    if (!grouped) {
        return items.length ? [{ id: '__all', title: null, items }] : [];
    }

    const itemsById = new Map(items.map((item) => [String(item.id), item]));
    const configuredItemIds = new Set(groups.flatMap((group) => group.items.map(String)));

    const configuredGroups = groups.map((group) => {
        const groupItemIds = new Set(group.items.map(String));

        return {
            id: group.id,
            title: group.title,
            items: hasSavedGroups
                ? group.items.map((id) => itemsById.get(String(id))).filter(Boolean)
                : items.filter((item) => groupItemIds.has(String(item.id))),
        };
    });

    const fallbackItems = items.filter((item) => !configuredItemIds.has(String(item.id)));

    return [
        ...configuredGroups,
        { ...fallbackGroup, items: fallbackItems },
    ].filter((group) => group.items.length);
}
