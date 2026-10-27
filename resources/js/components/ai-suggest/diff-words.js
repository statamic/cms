// A word-level diff, from the longest run of words the two have in common.
export default function diffWords(from, to) {
    const a = from.split(/\s+/).filter(Boolean);
    const b = to.split(/\s+/).filter(Boolean);
    const key = (word) => word.toLowerCase().replace(/[^\w'-]/g, '');
    const common = Array.from({ length: a.length + 1 }, () => Array(b.length + 1).fill(0));

    for (let i = a.length - 1; i >= 0; i--) {
        for (let j = b.length - 1; j >= 0; j--) {
            common[i][j] = key(a[i]) === key(b[j]) ? common[i + 1][j + 1] + 1 : Math.max(common[i + 1][j], common[i][j + 1]);
        }
    }

    const parts = [];
    let i = 0;
    let j = 0;

    while (i < a.length || j < b.length) {
        if (i < a.length && j < b.length && key(a[i]) === key(b[j])) {
            parts.push({ text: b[j++], type: 'same' });
            i++;
        } else if (j < b.length && (i === a.length || common[i][j + 1] >= common[i + 1][j])) {
            parts.push({ text: b[j++], type: 'added' });
        } else {
            parts.push({ text: a[i++], type: 'removed' });
        }
    }

    return parts;
}
