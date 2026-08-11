import { createSeededRandom, seededId, seededText } from './seeded.js';

export const REPLICATOR_PROFILES = {
    small: { sets: 3, fieldsPerSet: 2, nested: false },
    medium: { sets: 15, fieldsPerSet: 4, nested: false },
    pathological: { sets: 40, fieldsPerSet: 6, nested: true },
};

function makeSetFields(fieldsPerSet) {
    return Array.from({ length: fieldsPerSet }, (_, index) => ({
        handle: `field_${index}`,
        type: 'text',
        display: `Field ${index}`,
        replicator_preview: index === 0,
    }));
}

/**
 * Build a Replicator value array plus matching config/meta.
 */
export function makeReplicatorValue(options = {}) {
    const {
        sets = 3,
        fieldsPerSet = 2,
        nested = false,
        seed = 1,
    } = options;

    const random = createSeededRandom(seed);
    const fields = makeSetFields(fieldsPerSet);

    const value = Array.from({ length: sets }, (_, index) => {
        const set = {
            _id: seededId('rep', index),
            type: 'block',
            enabled: true,
        };

        for (const field of fields) {
            set[field.handle] = seededText(random, 8);
        }

        if (nested) {
            set.nested = Array.from({ length: 2 }, (_, nestedIndex) => ({
                _id: seededId(`rep-${index}-nested`, nestedIndex),
                type: 'block',
                enabled: true,
                field_0: seededText(random, 6),
            }));
        }

        return set;
    });

    const nestedField = nested
        ? [
              {
                  handle: 'nested',
                  type: 'replicator',
                  display: 'Nested',
                  sets: [
                      {
                          handle: 'main',
                          sets: [
                              {
                                  handle: 'block',
                                  display: 'Block',
                                  fields: [{ handle: 'field_0', type: 'text', display: 'Field 0' }],
                              },
                          ],
                      },
                  ],
              },
          ]
        : [];

    const config = {
        display: 'Blocks',
        type: 'replicator',
        collapse: false,
        previews: true,
        sets: [
            {
                handle: 'main',
                display: 'Main',
                sets: [
                    {
                        handle: 'block',
                        display: 'Block',
                        fields: [...fields, ...nestedField],
                    },
                ],
            },
        ],
    };

    const existing = {};

    for (const set of value) {
        existing[set._id] = Object.fromEntries(fields.map((field) => [field.handle, {}]));

        if (nested && set.nested) {
            existing[set._id].nested = {
                existing: Object.fromEntries(set.nested.map((child) => [child._id, { field_0: {} }])),
                defaults: { block: { field_0: null } },
                new: { block: { field_0: {} } },
                collapsed: [],
            };
        }
    }

    const meta = {
        existing,
        defaults: {
            block: Object.fromEntries([
                ...fields.map((field) => [field.handle, null]),
                ...(nested ? [['nested', []]] : []),
            ]),
        },
        new: {
            block: Object.fromEntries([
                ...fields.map((field) => [field.handle, {}]),
                ...(nested
                    ? [[
                          'nested',
                          {
                              existing: {},
                              defaults: { block: { field_0: null } },
                              new: { block: { field_0: {} } },
                              collapsed: [],
                          },
                      ]]
                    : []),
            ]),
        },
        collapsed: [],
    };

    return { value, config, meta, fields };
}

export function makeReplicatorValueFromProfile(profile = 'small', overrides = {}) {
    const preset = REPLICATOR_PROFILES[profile] || REPLICATOR_PROFILES.small;

    return makeReplicatorValue({ ...preset, ...overrides });
}
