// Prototype: pretend an AI wrote suggestions for a field. Nothing here is real.
// An empty field gets drafts written from the entry's title. A filled one gets rewrites of what's there.

const sentence = (text) => {
    text = text.replace(/\s+/g, ' ').trim();
    if (!text) return text;
    text = text[0].toUpperCase() + text.slice(1);
    return /[.!?]$/.test(text) ? text : `${text}.`;
};

const lowerFirst = (text) => (text ? text[0].toLowerCase() + text.slice(1) : text);

const shorter = (text) => {
    const words = text.replace(/\s+/g, ' ').trim().split(' ');
    if (words.length <= 6) return words.join(' ');
    return words.slice(0, Math.max(6, Math.ceil(words.length * 0.6))).join(' ').replace(/[,;:]$/, '') + '…';
};

function write(topic, long) {
    if (!long) {
        return [
            { label: 'Direct', text: topic },
            { label: 'Explainer', text: `${topic}, explained` },
            { label: 'Curious', text: `Why ${lowerFirst(topic)} matters` },
        ];
    }

    return [
        { label: 'Overview', text: `${topic} brings everything together in one place. Here's a quick look at what's new and why it matters.` },
        { label: 'Friendly', text: `Good news: ${lowerFirst(topic)} is here. We've put together everything you need to know to get started.` },
        { label: 'Short', text: `Everything you need to know about ${lowerFirst(topic)}.` },
    ];
}

function improve(value, long) {
    const tidy = long ? sentence(value) : value.replace(/\s+/g, ' ').trim().replace(/^./, (c) => c.toUpperCase());

    return [
        { label: 'Clearer', text: tidy },
        { label: 'Shorter', text: shorter(tidy) },
        { label: 'Friendlier', text: long ? `Good news: ${lowerFirst(tidy)}` : `${tidy} — a closer look` },
    ];
}

export function suggestFor(config, value, values) {
    const long = ['textarea', 'markdown'].includes(config.type);
    const topic = (values?.title || config.display || 'This page').trim();
    const current = typeof value === 'string' ? value.trim() : '';

    return {
        mode: current ? 'improve' : 'write',
        suggestions: current ? improve(current, long) : write(topic, long),
    };
}
