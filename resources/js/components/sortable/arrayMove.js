export default function arrayMove(items, oldIndex, newIndex) {
    const itemRemovedArray = [...items.slice(0, oldIndex), ...items.slice(oldIndex + 1, items.length)];

    return [
        ...itemRemovedArray.slice(0, newIndex),
        items[oldIndex],
        ...itemRemovedArray.slice(newIndex, itemRemovedArray.length),
    ];
}
