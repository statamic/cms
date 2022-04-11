import Omitter from '../components/field-conditions/Omitter.js';

test('it omits values at top level', () => {
    let values = {
        first_name: 'Han',
        last_name: 'Solo',
        ship: 'Falcon',
        bff: 'Chewy',
    };

    let omitted = new Omitter(values).omit([
        'last_name',
        'bff',
    ]);

    let expected = {
        first_name: 'Han',
        ship: 'Falcon',
    };

    expect(new Omitter(values).omit(['last_name', 'bff'])).toEqual(expected);
});

test('it omits nested values', () => {
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

    let omitted = new Omitter(values).omit([
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
    };

    expect(omitted).toEqual(expected);
});

test('it omits nested json field values', () => {
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
        'bffs.1.crush', // Intentionally passing deeper JSON value first to ensure the Omitter properly sorts these before decoding
        'bffs',
    ];

    let omitted = new Omitter(values, jsonFields).omit([
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

    expect(omitted).toEqual(expected);
});
