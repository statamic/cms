import Slug from '../components/slugs/Slug';

let config = {
    asciiReplaceExtraSymbols: false,
    selectedSite: 'en',
    sites: [{ handle: 'en', lang: 'en' }],
    charmap: {
        // More values would be in this charmap in reality but this enough for the test.
        en: {}, // en *would* be empty by default though.
        currency_short: { $: '$' },
    },
};

window.Statamic = {
    $config: {
        get: (key) => config[key],
    },
};

test('it slugifies', () => {
    expect(new Slug().create('One')).toBe('one');
    expect(new Slug().create('One Two Three')).toBe('one-two-three');
    expect(new Slug().create(`Apple's`)).toBe('apples');
    expect(new Slug().create(`Statamic’s latest feature: “Duplicator”`)).toBe('statamics-latest-feature-duplicator');
    expect(new Slug().separatedBy('_').create('JSON-LD Document')).toBe('json_ld_document');
    expect(new Slug().create('Block - Hero')).toBe('block-hero');
    expect(new Slug().create('10% off over $100 & more')).toBe('10-off-over-100-more');
});

test('it slugifies with extra symbols', () => {
    // When setting ascii_replace_extra_symbols to true, these would get set by php.
    // There would be more in reality but these are enough for the test.
    config.asciiReplaceExtraSymbols = true;
    config.charmap.en['%'] = ' percent ';
    config.charmap.en['&'] = ' and ';
    config.charmap.en['&'] = ' and ';
    config.charmap.currency = { $: ' Dollar ' };

    expect(new Slug().create('10% off over $100 & more')).toBe('10-percent-off-over-dollar-100-and-more');
});
