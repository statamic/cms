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
