export const MENTION_TOKEN = /^\[\[\s*([\w.-]+)\s*\]\]/;

export function isValidMentionValue(value) {
    if (typeof value !== 'string') return false;

    return MENTION_TOKEN.exec(`[[ ${value} ]]`)?.[1] === value;
}
