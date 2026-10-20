// Prototype: pretend an AI wrote suggestions for a field. Nothing here is real.
// An empty field gets drafts written for that kind of field (a title, a meta description…) from the entry's title.
// A filled one gets rewrites of what's there. A sale gets copy about the sale; anything else gets general copy.

const upperFirst = (text) => (text ? text[0].toUpperCase() + text.slice(1) : text);
const lowerFirst = (text) => (text ? text[0].toLowerCase() + text.slice(1) : text);
const tidy = (text) => text.replace(/\s+/g, ' ').trim();
const sentence = (text) => (/[.!?]$/.test(text) ? text : `${text}.`);

// What kind of field this is, from its handle and display name
function kindOf(config) {
    const name = `${config.handle || ''} ${config.display || ''}`.toLowerCase();

    if (/meta|seo/.test(name)) return 'meta';
    if (/excerpt|summary|intro|teaser|description/.test(name)) return 'excerpt';
    if (/subtitle|tagline|strapline|standfirst/.test(name)) return 'subtitle';
    if (/title|heading|headline/.test(name)) return 'title';
    if (/note/.test(name)) return 'note';

    return ['textarea', 'markdown'].includes(config.type) ? 'excerpt' : 'subtitle';
}

// "Our summer sale" is about the "summer sale"
// Its first letter is lowered to sit mid-sentence, but the rest keeps its case, so names like "Statamic" survive.
const subjectOf = (title) => lowerFirst(tidy(title).replace(/^(our|the|a|an)\s+/i, '')) || 'this page';

function salePack(subject) {
    return {
        title: [`The ${subject} is on`, `${upperFirst(subject)}: up to 40% off`, `Don't miss our ${subject}`],
        subtitle: [
            'Our biggest discounts of the year, for a limited time only',
            'Big savings across the range while stocks last',
            'Fresh deals every week until the sale ends',
        ],
        excerpt: [
            `Our ${subject} is here, with our biggest discounts of the year across the range. Stock is limited, so if something catches your eye, don't wait too long.`,
            `Save on our most popular pieces in the ${subject}. Here's what's included, how long it runs and how to make the most of it.`,
            `The ${subject} is back. See what's on offer and grab a bargain before it ends.`,
        ],
        meta: [
            `Shop the ${subject} and save on our most popular pieces. Limited stock, limited time, so see what's on offer before it ends.`,
            `Our ${subject} is live, with our biggest discounts of the year. Browse the deals and find your favourites today.`,
            `Save big in the ${subject}. Discover this year's best deals before they're gone.`,
        ],
        note: ['Prices correct at the time of publishing', 'Discounts apply automatically at checkout', 'While stocks last. Ends at midnight on the last day of the sale'],
    };
}

function generalPack(subject) {
    return {
        title: [upperFirst(subject), `${upperFirst(subject)}: a practical guide`, `${upperFirst(subject)}, explained`],
        subtitle: ['Everything you need to know, in one place', "What's new, what's changed and what's next", `A quick guide to ${subject}`],
        excerpt: [
            `A quick guide to ${subject}: the essentials, the details that matter and where to go next.`,
            `Everything you need to know about ${subject}, from the basics to the finer details.`,
            `${upperFirst(subject)} doesn't have to be complicated. Here's a clear, practical overview to help you along.`,
        ],
        meta: [
            `A clear, practical guide to ${subject}, covering the essentials and the details that matter.`,
            `Everything you need to know about ${subject}, explained simply.`,
            `${upperFirst(subject)}, made simple. Get the key facts and find out where to go next.`,
        ],
        note: ['Written by our editorial team', 'Last reviewed this month for accuracy', "Got a question? Get in touch and we'll help"],
    };
}

// Takes out filler and wordy phrasing
function tighten(text) {
    return upperFirst(
        tidy(text)
            .replace(/\b(the|our) (\w+est) (\w+) (?:that )?we(?:'ve| have) ever (?:done|had|made|run|offered)\b/gi, 'our $2-ever $3')
            .replace(/\bon all of our\b/gi, 'across our')
            .replace(/\ball of (our|the)\b/gi, '$1')
            .replace(/\b(very|really|just|actually|basically|literally)\s+/gi, '')
            .replace(/\bin order to\b/gi, 'to')
            .replace(/\bwe(?:'re| are) (?:\w+ )?(?:excited|pleased|delighted|happy) to (?:announce|share|let you know) that\s+/gi, '')
            // Capitals after a full stop, and for the first word
            .replace(/([.!?]\s+)([a-z])/g, (_, end, letter) => end + letter.toUpperCase()),
    );
}

// Tighter still: the first sentence, without the extra detail
function punchier(text, long) {
    let result = tighten(text);
    if (long) return result.match(/^[^.!?]+[.!?]?/)[0];

    return result
        .replace(/\bacross our\b/gi, 'on')
        .replace(/\s+(ranges?|collections?|lines?)\b/gi, '')
        .replace(/\s+(this year|right now|at the moment|for now)$/i, '');
}

// A short phrase gets an inviting verb in front. Full sentences get a friendly opener.
function warmer(text, long) {
    const result = tighten(text);
    if (long) return `Good news: ${lowerFirst(result)}`;

    return /^(our|the) /i.test(result) ? `Enjoy ${lowerFirst(result)}` : `Discover ${lowerFirst(result)}`;
}

export function suggestFor(config, value, values) {
    const kind = kindOf(config);
    const long = ['excerpt', 'meta'].includes(kind) || ['textarea', 'markdown'].includes(config.type);
    const subject = subjectOf(values?.title || config.display || '');
    const pack = (/\b(sale|offer|discount|deals?)\b/i.test(subject) ? salePack : generalPack)(subject)[kind];
    const current = typeof value === 'string' ? tidy(value) : '';
    const finish = (text) => (long ? sentence(text) : text);

    if (!current) {
        return { mode: 'write', kind, long, suggestions: pack.map((text) => ({ text })) };
    }

    // A rewrite that comes out the same as what's there (a short title, say) is swapped for a fresh draft instead
    const rewrites = [
        { label: 'Tighter', text: finish(tighten(current)) },
        { label: 'Punchier', text: finish(punchier(current, long)) },
        { label: 'Warmer', text: finish(warmer(current, long)) },
    ];
    const seen = new Set([current.toLowerCase()]);
    let spare = 0;

    return {
        mode: 'improve',
        kind,
        long,
        suggestions: rewrites.map((rewrite) => {
            while (seen.has(rewrite.text.toLowerCase()) && spare < pack.length) rewrite = { label: 'Fresh take', text: pack[spare++] };
            seen.add(rewrite.text.toLowerCase());
            return rewrite;
        }),
    };
}

// Prototype: rework a set of suggestions to follow an instruction from the "Make it…" box
const formal = (text) =>
    upperFirst(
        text
            .replace(/^(Enjoy|Discover) /, '')
            .replace(/^Good news: /, '')
            .replace(/\bdon't\b/gi, 'do not')
            .replace(/\bit's\b/gi, 'it is')
            .replace(/\bwe've\b/gi, 'we have')
            .replace(/\bhere's\b/gi, 'here is')
            .replace(/\bthey're\b/gi, 'they are')
            .replace(/\bwhat's\b/gi, 'what is')
            .replace(/\bgrab a bargain\b/gi, 'take advantage of the offers')
            .replace(/\bbig\b/gi, 'significant')
            .replace(/\bbiggest-ever\b/gi, 'most substantial')
            .replace(/\bdiscounts\b/gi, 'reductions'),
    );

export function steer(result, instruction) {
    const finish = (text) => (result.long ? sentence(text) : text);
    const how = instruction.trim().toLowerCase();

    const rework = (text) => {
        if (/short|concise|brief/.test(how)) return finish(punchier(text, result.long));
        if (/formal|professional/.test(how)) return finish(formal(text));
        if (/friend|warm|casual/.test(how)) return finish(/^(Enjoy|Discover|Good news)/.test(text) ? text : warmer(text, result.long));
        return finish(tighten(text));
    };

    // Two suggestions can come out the same once reworked, so only the first of each is kept
    const texts = [...new Set(result.suggestions.map((suggestion) => rework(suggestion.text)))];

    return { ...result, suggestions: texts.map((text) => ({ label: upperFirst(instruction.trim()), text })) };
}
