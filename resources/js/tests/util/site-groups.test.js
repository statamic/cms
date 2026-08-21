import { beforeEach, describe, expect, test } from 'vitest';
import {
    flatOptionsFromSiteGroups,
    groupItemsBySiteGroup,
    hasNamedSiteGroups,
    preferredOriginHandle,
    selectedSiteGroupLabel,
    siteGroupKey,
    UNGROUPED_SITE_GROUP_KEY,
} from '@/util/site-groups.js';

beforeEach(() => {
    globalThis.__ = (string) => string;
    globalThis.Statamic = {
        $config: {
            get: () => [
                { handle: 'en', name: 'English', group: 'UK', group_handle: 'uk' },
                { handle: 'fr', name: 'French', group: 'UK', group_handle: 'uk' },
                { handle: 'de', name: 'German', group: 'EU', group_handle: 'eu' },
                { handle: 'nl', name: 'Dutch' },
            ],
        },
    };
});

describe('siteGroupKey', () => {
    test('uses group_handle, then group, then other', () => {
        expect(siteGroupKey({ group_handle: 'uk', group: 'UK' })).toBe('uk');
        expect(siteGroupKey({ group: 'UK' })).toBe('UK');
        expect(siteGroupKey({})).toBe(UNGROUPED_SITE_GROUP_KEY);
    });
});

describe('groupItemsBySiteGroup', () => {
    test('keeps named groups in first-seen order and Other last', () => {
        const groups = groupItemsBySiteGroup([
            { handle: 'en', group: 'UK', group_handle: 'uk' },
            { handle: 'nl' },
            { handle: 'fr', group: 'UK', group_handle: 'uk' },
            { handle: 'de', group: 'EU', group_handle: 'eu' },
        ]);

        expect(groups.map((group) => group.key)).toEqual(['uk', 'eu', UNGROUPED_SITE_GROUP_KEY]);
        expect(groups[0].items.map((item) => item.handle)).toEqual(['en', 'fr']);
        expect(groups[2].label).toBe('Other');
    });
});

describe('hasNamedSiteGroups', () => {
    test('detects named groups from items or grouped structure', () => {
        expect(hasNamedSiteGroups([{ handle: 'nl' }])).toBe(false);
        expect(hasNamedSiteGroups([{ handle: 'en', group: 'UK' }])).toBe(true);
        expect(hasNamedSiteGroups(groupItemsBySiteGroup([{ handle: 'en', group: 'UK' }]))).toBe(true);
    });
});

describe('flatOptionsFromSiteGroups', () => {
    test('adds headers and separators for visible groups', () => {
        const options = flatOptionsFromSiteGroups(groupItemsBySiteGroup([
            { handle: 'en', name: 'English', group: 'UK', group_handle: 'uk' },
            { handle: 'de', name: 'German', group: 'EU', group_handle: 'eu' },
        ]));

        expect(options[0]._groupLabel).toBe('UK');
        expect(options[0]._showGroupSeparator).toBe(false);
        expect(options[1]._groupLabel).toBe('EU');
        expect(options[1]._showGroupSeparator).toBe(true);
    });

    test('can filter items while regrouping headers', () => {
        const options = flatOptionsFromSiteGroups(groupItemsBySiteGroup([
            { handle: 'en', name: 'English', group: 'UK', group_handle: 'uk' },
            { handle: 'fr', name: 'French', group: 'UK', group_handle: 'uk' },
            { handle: 'de', name: 'German', group: 'EU', group_handle: 'eu' },
        ]), {
            filterItems: (items) => items.filter((item) => item.handle !== 'en'),
        });

        expect(options.map((option) => option.handle)).toEqual(['fr', 'de']);
        expect(options[0]._groupLabel).toBe('UK');
        expect(options[1]._groupLabel).toBe('EU');
    });
});

describe('selectedSiteGroupLabel', () => {
    test('returns group name, Other, or null', () => {
        expect(selectedSiteGroupLabel({ group: 'UK' }, true)).toBe('UK');
        expect(selectedSiteGroupLabel({}, true)).toBe('Other');
        expect(selectedSiteGroupLabel({}, false)).toBeNull();
    });
});

describe('preferredOriginHandle', () => {
    test('prefers an existing site in the target group', () => {
        const localizations = [
            { handle: 'en', exists: true, root: true, group: 'UK', group_handle: 'uk' },
            { handle: 'fr', exists: true, group: 'UK', group_handle: 'uk', origin_handle: 'en' },
            { handle: 'de', exists: false, group: 'EU', group_handle: 'eu' },
        ];

        expect(preferredOriginHandle(localizations, localizations[2])).toBe('en');
    });

    test('follows a former group origin that moved outside the group', () => {
        const localizations = [
            { handle: 'en', exists: true, root: true, group: 'UK', group_handle: 'uk' },
            { handle: 'fr', exists: true, group: 'EU', group_handle: 'eu', origin_handle: 'en' },
            { handle: 'de', exists: true, group: 'EU', group_handle: 'eu', origin_handle: 'fr' },
        ];

        // Creating another EU site should still prefer fr's origin (en) once fr left... 
        // Actually: fr is IN eu with origin en outside. de is in eu with origin fr.
        // preferred for a new EU target should find sameGroup [fr, de], groupOriginHandle.
        expect(preferredOriginHandle(localizations, {
            handle: 'nl',
            exists: false,
            group: 'EU',
            group_handle: 'eu',
        })).toBe('fr');
    });

    test('falls back to root when the target group has no existing sites', () => {
        const localizations = [
            { handle: 'en', exists: true, root: true, group: 'UK', group_handle: 'uk' },
            { handle: 'fr', exists: true, group: 'UK', group_handle: 'uk', origin_handle: 'en' },
            { handle: 'de', exists: false, group: 'EU', group_handle: 'eu' },
        ];

        expect(preferredOriginHandle(localizations, localizations[2], 'root')).toBe('en');
    });

    test('returns null when nothing exists yet', () => {
        expect(preferredOriginHandle([{ handle: 'en', exists: false }], { handle: 'fr' })).toBeNull();
    });
});
