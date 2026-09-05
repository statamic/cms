import { expect, test } from 'vitest';
import {
    buildCardLayouts,
    cardGroupColumns,
    getCardGroupMemberIds,
    shouldShowPickerConnector,
} from '@/components/fieldtypes/replicator/cardLayouts.js';

test('cardGroupColumns returns expected column counts', () => {
    expect(cardGroupColumns(1)).toBe(1);
    expect(cardGroupColumns(2)).toBe(2);
    expect(cardGroupColumns(3)).toBe(3);
    expect(cardGroupColumns(5)).toBe(3);
});

test('buildCardLayouts groups consecutive card sets', () => {
    const value = [
        { _id: '1', type: 'marketing' },
        { _id: '2', type: 'team' },
        { _id: '3', type: 'team' },
        { _id: '4', type: 'team' },
    ];
    const isCardSet = (type) => type === 'team';
    const isSameCardGroup = (a, b) => isCardSet(a.type) && isCardSet(b.type) && a.type === b.type;

    const layouts = buildCardLayouts(value, isCardSet, isSameCardGroup);

    expect(layouts[0].className).toBe('replicator-set-slot--full');
    expect(layouts[1].groupSize).toBe(3);
    expect(layouts[1].positionInGroup).toBe(0);
    expect(layouts[2].positionInGroup).toBe(1);
    expect(layouts[3].positionInGroup).toBe(2);
    expect(layouts[3].columns).toBe(3);
    expect(layouts[3].className).toBe('replicator-set-slot--card-cols-3');
});

test('getCardGroupMemberIds returns the whole row in multi-column layout', () => {
    const value = [
        { _id: '1', type: 'team' },
        { _id: '2', type: 'team' },
        { _id: '3', type: 'team' },
    ];
    const layouts = buildCardLayouts(
        value,
        () => true,
        (a, b) => a.type === b.type,
    );

    expect(getCardGroupMemberIds({
        index: 1,
        value,
        layouts,
        multiColumn: true,
    })).toEqual(['1', '2', '3']);
});

test('getCardGroupMemberIds only returns the clicked set when stacked', () => {
    const value = [
        { _id: '1', type: 'team' },
        { _id: '2', type: 'team' },
    ];
    const layouts = buildCardLayouts(
        value,
        () => true,
        (a, b) => a.type === b.type,
    );

    expect(getCardGroupMemberIds({
        index: 1,
        value,
        layouts,
        multiColumn: false,
    })).toEqual(['2']);
});

test('shouldShowPickerConnector shows inset connectors for non-row-start cards', () => {
    const value = [
        { _id: '1', type: 'team' },
        { _id: '2', type: 'team' },
    ];
    const layouts = buildCardLayouts(
        value,
        () => true,
        (a, b) => a.type === b.type,
    );

    expect(shouldShowPickerConnector({
        index: 0,
        layouts,
        showCardEntryConnector: () => false,
    })).toBe(false);

    expect(shouldShowPickerConnector({
        index: 1,
        layouts,
        showCardEntryConnector: () => false,
    })).toBe(true);
});
