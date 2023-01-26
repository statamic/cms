import Values from '../components/publish/Values.js';

test('it rejects values at top level', () => {
    let values = {
        first_name: 'Han',
        last_name: 'Solo',
        ship: 'Falcon',
        bff: 'Chewy',
    };

    let rejected = new Values(values).except([
        'last_name',
        'bff',
    ]);

    let expected = {
        first_name: 'Han',
        ship: 'Falcon',
    };

    expect(rejected).toEqual(expected);
});

test('it rejects nested values', () => {
    let values = {
        first_name: 'Han',
        last_name: 'Solo',
        ship: {
            name: 'Falcon',
            completed_kessel_run: 'less than 12 parsecs',
            junk: true,
        },
        bffs: [
            {
                name: 'Chewy',
                type: 'Wookie',
            },
            {
                name: 'Leia',
                type: 'Woman',
                crush: [
                    {
                        name: 'Lando',
                        type: 'Man',
                    }
                ],
            },
        ],
        foo123: [
            {
                hello: 'alfa',
                world: 'bravo'
            },
            {
                hello: 'charlie',
                world: 'delta'
            }
        ],
        foo123bar: [
            {
                hello: 'alfa',
                world: 'bravo'
            },
            {
                hello: 'charlie',
                world: 'delta'
            }
        ],
    };

    let rejected = new Values(values).except([
        'last_name',
        'ship.completed_kessel_run',
        'bffs.0.type',
        'bffs.1.crush.0.name',
        'foo123.0.hello',
        'foo123bar.0.hello',
    ]);

    let expected = {
        first_name: 'Han',
        ship: {
            name: 'Falcon',
            junk: true,
        },
        bffs: [
            {
                name: 'Chewy',
            },
            {
                name: 'Leia',
                type: 'Woman',
                crush: [
                    {
                        type: 'Man',
                    }
                ],
            },
        ],
        foo123: [
            {
                world: 'bravo'
            },
            {
                hello: 'charlie',
                world: 'delta'
            }
        ],
        foo123bar: [
            {
                world: 'bravo'
            },
            {
                hello: 'charlie',
                world: 'delta'
            }
        ],
    };

    expect(rejected).toEqual(expected);
});

test('it rejects nested json field values', () => {
    let values = {
        first_name: 'Han',
        last_name: 'Solo',
        ship: {
            name: 'Falcon',
            completed_kessel_run: 'less than 12 parsecs',
            junk: true,
        },
        bffs: JSON.stringify([
            {
                name: 'Chewy',
                type: 'Wookie',
            },
            {
                name: 'Leia',
                type: 'Woman',
                crush: JSON.stringify([
                    {
                        name: 'Lando',
                        type: 'Man',
                    },
                ]),
            },
        ]),
    };

    let jsonFields = [
        'bffs.1.crush', // Intentionally passing deeper JSON value first to ensure these are properly sorted before decoding
        'bffs',
    ];

    let rejected = new Values(values, jsonFields).except([
        'last_name',
        'ship.completed_kessel_run',
        'bffs.0.type',
        'bffs.1.crush.0.name',
    ]);

    let expected = {
        first_name: 'Han',
        ship: {
            name: 'Falcon',
            junk: true,
        },
        bffs: JSON.stringify([
            {
                name: 'Chewy',
            },
            {
                name: 'Leia',
                type: 'Woman',
                crush: JSON.stringify([
                    {
                        type: 'Man',
                    },
                ]),
            },
        ]),
    };

    expect(rejected).toEqual(expected);
});

test('it rejects null values', () => {
    let values = {
        first_name: 'Han',
        last_name: 'Solo',
        ship: 'Falcon',
        bff: null, // this is null, but should still get removed
    };

    let rejected = new Values(values).except([
        'last_name',
        'bff',
    ]);

    let expected = {
        first_name: 'Han',
        ship: 'Falcon',
    };

    expect(rejected).toEqual(expected);
});

test('it filters values at top level', () => {
    let values = {
        first_name: 'Han',
        last_name: 'Solo',
        ship: 'Falcon',
        bff: 'Chewy',
    };

    let filtered = new Values(values).only([
        'last_name',
        'bff',
    ]);

    let expected = {
        last_name: 'Solo',
        bff: 'Chewy',
    };

    expect(filtered).toEqual(expected);
});

test('it filters nested values', () => {
    let values = {
        first_name: 'Han',
        last_name: 'Solo',
        ship: {
            name: 'Falcon',
            completed_kessel_run: 'less than 12 parsecs',
            junk: true,
        },
        bffs: [
            {
                name: 'Chewy',
                type: 'Wookie',
            },
            {
                name: 'Leia',
                type: 'Woman',
                crush: [
                    {
                        name: 'Lando',
                        type: 'Man',
                    }
                ],
            },
        ],
    };

    let filtered = new Values(values).only([
        'last_name',
        'ship.completed_kessel_run',
        'bffs.0.name',
        'bffs.1.crush.0.name',
    ]);

    let expected = {
        last_name: 'Solo',
        ship: {
            completed_kessel_run: 'less than 12 parsecs',
        },
        bffs: [
            {
                name: 'Chewy',
            },
            {
                crush: [
                    {
                        name: 'Lando',
                    }
                ],
            },
        ],
    };

    expect(filtered).toEqual(expected);
});

test('it filters nested json field values', () => {
    let values = {
        first_name: 'Han',
        last_name: 'Solo',
        ship: {
            name: 'Falcon',
            completed_kessel_run: 'less than 12 parsecs',
            junk: true,
        },
        bffs: JSON.stringify([
            {
                name: 'Chewy',
                type: 'Wookie',
            },
            {
                name: 'Leia',
                type: 'Woman',
                crush: JSON.stringify([
                    {
                        name: 'Lando',
                        type: 'Man',
                    },
                ]),
            },
        ]),
    };

    let jsonFields = [
        'bffs.1.crush', // Intentionally passing deeper JSON value first to ensure these are properly sorted before decoding
        'bffs',
    ];

    let filtered = new Values(values, jsonFields).only([
        'last_name',
        'ship.completed_kessel_run',
        'bffs.0.type',
        'bffs.1.crush.0.name',
    ]);

    let expected = {
        last_name: 'Solo',
        ship: {
            completed_kessel_run: 'less than 12 parsecs',
        },
        bffs: JSON.stringify([
            {
                type: 'Wookie',
            },
            {
                crush: JSON.stringify([
                    {
                        name: 'Lando',
                    },
                ]),
            },
        ]),
    };

    expect(filtered).toEqual(expected);
});

test('it filters null values', () => {
    let values = {
        first_name: 'Han',
        last_name: 'Solo',
        ship: 'Falcon',
        bff: null, // this is null, but should still get removed
    };

    let filtered = new Values(values).only([
        'last_name',
        'bff',
    ]);

    let expected = {
        last_name: 'Solo',
        bff: null, // this is null, but should still be present in filtered data
    };

    expect(filtered).toEqual(filtered);
});

test('it merges values at top level', () => {
    let values = {
        first_name: 'Han',
        last_name: 'Solo',
        ship: 'Falcon',
    };

    let merged = new Values(values).merge({
        ship: 'X-Wing',
        bff: 'Leia',
    });

    let expected = {
        first_name: 'Han',
        last_name: 'Solo',
        ship: 'X-Wing',
        bff: 'Leia',
    };

    expect(merged).toEqual(expected);
});

test('it merges nested values', () => {
    let values = {
        first_name: 'Han',
        last_name: 'Solo',
        favourites: {
            food: 'Tauntaun',
            weapon: 'Blaster',
        },
        replicator: [
            {
                name: 'Chewy',
                reveal_type: false,
                type: 'Wookie',
            },
            {
                name: 'Leia',
                reveal_type: false,
                type: 'Woman',
            },
        ],
    };

    let merged = new Values(values).merge({
        nickname: 'Scoundrel',
        favourites: {
            weapon: 'Lightsaber',
            girl: 'Leia',
        },
        replicator: [
            {
                name: 'Chewy',
                reveal_type: true,
                type: 'Wookie',
            },
            {
                name: 'Leia',
                reveal_type: false,
                type: 'Woman',
            },
        ],
    });

    let expected = {
        first_name: 'Han',
        last_name: 'Solo',
        nickname: 'Scoundrel',
        favourites: {
            food: 'Tauntaun',
            weapon: 'Lightsaber',
            girl: 'Leia',
        },
        replicator: [
            {
                name: 'Chewy',
                reveal_type: true,
                type: 'Wookie',
            },
            {
                name: 'Leia',
                reveal_type: false,
                type: 'Woman',
            },
        ],
    };

    expect(merged).toEqual(expected);
});

test('it merges nested json field values', () => {
    let values = {
        first_name: 'Han',
        last_name: 'Solo',
        favourites: {
            food: 'Tauntaun',
            weapon: 'Blaster',
        },
        bffs: JSON.stringify([
            {
                name: 'Chewy',
                reveal_type: false,
                type: 'Wookie',
            },
            {
                name: 'Leia',
                reveal_type: false,
                type: 'Woman',
                crush: JSON.stringify([
                    {
                        name: 'Lando',
                        reveal_type: false,
                        type: 'Man',
                    },
                ]),
            },
        ]),
    };

    let jsonFields = [
        'bffs.1.crush', // Intentionally passing deeper JSON value first to ensure these are properly sorted before decoding
        'bffs',
    ];

    let merged = new Values(values, jsonFields).merge({
        nickname: 'Scoundrel',
        favourites: {
            weapon: 'Lightsaber',
            girl: 'Leia',
        },
        bffs: JSON.stringify([
            {
                name: 'Chewy',
                reveal_type: true,
                type: 'Wookie',
            },
            {
                name: 'Leia',
                reveal_type: false,
                type: 'Woman',
                crush: JSON.stringify([
                    {
                        name: 'Lando',
                        reveal_type: true,
                        type: 'Scoundrel',
                    },
                ]),
            },
        ]),
    });

    let expected = {
        first_name: 'Han',
        last_name: 'Solo',
        nickname: 'Scoundrel',
        favourites: {
            food: 'Tauntaun',
            weapon: 'Lightsaber',
            girl: 'Leia',
        },
        bffs: JSON.stringify([
            {
                name: 'Chewy',
                reveal_type: true,
                type: 'Wookie',
            },
            {
                name: 'Leia',
                reveal_type: false,
                type: 'Woman',
                crush: JSON.stringify([
                    {
                        name: 'Lando',
                        reveal_type: true,
                        type: 'Scoundrel',
                    },
                ]),
            },
        ]),
    };

    expect(merged).toEqual(expected);
});

test('it gracefully handles errors', () => {
    let values = {
        first_name: 'Han',
        last_name: 'Solo',
        bffs: JSON.stringify([
            {
                name: 'Chewy',
                type: 'Wookie',
            },
        ]),
    };

    let jsonFields = [
        'bffs',
        'bffs',         // duplicate field
        'middle_name',  // non-existent field
        'bffs.0.crush', // non-existent field
    ];

    let rejected = new Values(values, jsonFields).except([
        'last_name',
        'middle_name',  // non-existent field
        'bffs.0.name',
        'bffs.0.name',  // duplicate field
        'bffs.1.name',  // non-existent field
        'bffs.0.crush', // non-existent field
        'bffs.0.crush.0.name', // non-existent field
    ]);

    let expected = {
        first_name: 'Han',
        bffs: JSON.stringify([
            {
                type: 'Wookie',
            },
        ]),
    };

    expect(rejected).toEqual(expected);
});

test('it properly rejects keys that javascript considers having numeric separators', () => {
    let values = {
        title: 'Millenium Falcon',
        '404_text': JSON.stringify('This ship is the fastest hunk of junk in the galaxy!'),
        page: {
            title: 'X-Wing',
            '404_text': JSON.stringify('Permission to jump in an X-Wing and blow something up?'),
        },
        '123_key': {
            '456_key': {
                'title': 'Y-Wing',
                '404_text': JSON.stringify('Y-Wings are the BOMB!'),
            },
        },
    };

    let jsonFields = [
        '404_text', // Field handles like this were causing numeric separator error.
        'page.404_text',
        '123_key.456_key.404_text',
    ];

    let rejected = new Values(values, jsonFields).except([
        '404_text',
        '123_key.456_key.404_text',
    ]);

    let expected = {
        title: 'Millenium Falcon',
        page: {
            title: 'X-Wing',
            '404_text': JSON.stringify('Permission to jump in an X-Wing and blow something up?'),
        },
        '123_key': {
            '456_key': {
                'title': 'Y-Wing',
            },
        },
    };

    expect(rejected).toEqual(expected);
});

test('it properly merges keys that javascript considers having numeric separators', () => {
    let values = {
        title: 'Millenium Falcon',
        '404_text': JSON.stringify('This ship is the fastest hunk of junk in the galaxy!'),
        page: {
            title: 'X-Wing',
            '404_text': JSON.stringify('Permission to jump in an X-Wing and blow something up?'),
        },
        '123_key': {
            '456_key': {
                'title': 'Y-Wing',
                '404_text': JSON.stringify('Y-Wings are the BOMB!'),
            },
        },
    };

    let jsonFields = [
        '404_text', // Field handles like this were causing numeric separator error.
        'page.404_text',
        '123_key.456_key.404_text',
    ];

    let merged = new Values(values, jsonFields).merge({
        '404_text': JSON.stringify('This ship is the slowest hunk of junk in the galaxy!'),
        page: {
            '404_text': JSON.stringify('Permission to jump in a Tie Fighter and blow something up?'),
        },
        '123_key': {
            '456_key': {
                'title': 'Y-Wing',
                '404_text': JSON.stringify('X-Wings are the BOMB!'),
                '403_text': 'Unauthorized!',
            },
        },
    });

    let expected = {
        title: 'Millenium Falcon',
        '404_text': JSON.stringify('This ship is the slowest hunk of junk in the galaxy!'),
        page: {
            title: 'X-Wing',
            '404_text': JSON.stringify('Permission to jump in a Tie Fighter and blow something up?'),
        },
        '123_key': {
            '456_key': {
                'title': 'Y-Wing',
                '404_text': JSON.stringify('X-Wings are the BOMB!'),
                '403_text': 'Unauthorized!',
            },
        },
    };

    expect(merged).toEqual(expected);
});
