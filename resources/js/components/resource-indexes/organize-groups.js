export function unassignedResourceIndexItems(items, groups) {
    const assignedIds = new Set(groups.flatMap((group) => group.items.map(String)));

    return items.filter((item) => !assignedIds.has(String(item.id)));
}

export function isResourceIndexFallbackReorder(currentGroupId, overGroupId, fallbackGroupId) {
    return currentGroupId === fallbackGroupId && overGroupId === fallbackGroupId;
}

export function addResourceIndexItems(groups, groupId, itemIds) {
    const group = groups.find((group) => group.id === groupId);
    if (!group) return;

    const existingIds = new Set(group.items.map(String));

    itemIds.map(String).forEach((id) => {
        if (existingIds.has(id)) return;

        existingIds.add(id);
        group.items.push(id);
    });
}

export function removeResourceIndexItem(groups, groupId, itemId) {
    const group = groups.find((group) => group.id === groupId);
    if (!group) return;

    group.items = group.items.filter((id) => String(id) !== String(itemId));
}

export function moveResourceIndexItem(groups, {
    itemId,
    oldGroupId,
    oldIndex,
    newGroupId,
    newIndex,
    fallbackGroupId,
}) {
    itemId = String(itemId);

    if (isResourceIndexFallbackReorder(oldGroupId, newGroupId, fallbackGroupId)) return;

    if (newGroupId === fallbackGroupId) {
        groups.forEach((group) => {
            group.items = group.items.filter((id) => String(id) !== itemId);
        });

        return;
    }

    const newGroup = groups.find((group) => group.id === newGroupId);
    if (!newGroup) return;

    if (oldGroupId === newGroupId) {
        newGroup.items.splice(newIndex, 0, newGroup.items.splice(oldIndex, 1)[0]);

        return;
    }

    if (oldGroupId !== fallbackGroupId) {
        removeResourceIndexItem(groups, oldGroupId, itemId);
    }

    if (!newGroup.items.some((id) => String(id) === itemId)) {
        newGroup.items.splice(newIndex, 0, itemId);
    }
}
