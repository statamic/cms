export function fieldCount(fields) {
    if (Array.isArray(fields)) {
        return fields.length;
    }

    return Object.keys(fields || {}).length;
}

/** Sets with more fields than this stay at most two columns so cards don't get too cramped. */
export const MAX_FIELDS_FOR_THREE_COLUMNS = 5;

export function cardGroupColumns(count, fields = 0) {
    if (count <= 1) {
        return 1;
    }

    if (count === 2 || fieldCount(fields) > MAX_FIELDS_FOR_THREE_COLUMNS) {
        return 2;
    }

    return 3;
}

export function buildCardLayouts(value, isCardSet, isSameCardGroup, fieldsForSet = () => 0) {
    return value.map((set, index) => {
        if (!isCardSet(set.type)) {
            return {
                isCard: false,
                groupSize: 1,
                positionInGroup: 0,
                columns: 1,
                className: 'replicator-set-slot--full',
            };
        }

        let groupStart = index;

        while (groupStart > 0 && isSameCardGroup(value[groupStart - 1], set)) {
            groupStart--;
        }

        let groupEnd = index;

        while (groupEnd < value.length - 1 && isSameCardGroup(value[groupEnd + 1], set)) {
            groupEnd++;
        }

        const groupSize = groupEnd - groupStart + 1;
        const columns = cardGroupColumns(groupSize, fieldsForSet(set.type));

        return {
            isCard: true,
            groupSize,
            positionInGroup: index - groupStart,
            columns,
            className: `replicator-set-slot--card-cols-${columns}`,
        };
    });
}

function setId(set) {
    return set._id ?? set.id;
}

export function getCardGroupMemberIds({ index, value, layouts, multiColumn }) {
    const set = value[index];

    if (!set) {
        return [];
    }

    const layout = layouts[index];

    if (!multiColumn || !layout.isCard || layout.groupSize <= 1) {
        return [setId(set)];
    }

    const groupStart = index - layout.positionInGroup;

    return value
        .slice(groupStart, groupStart + layout.groupSize)
        .map((item) => setId(item));
}

export function shouldShowPickerConnector({ index, layouts, showCardEntryConnector }) {
    if (showCardEntryConnector(index)) {
        return true;
    }

    const layout = layouts[index];

    return layout.isCard
        && layout.groupSize > 1
        && layout.positionInGroup !== 0;
}
