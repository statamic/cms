export default function extractBardText(
    prosemirrorNodes,
    limit = 150,
    setConfigs = null,
) {
    if (!Array.isArray(prosemirrorNodes)) return "";

    const stack = [...prosemirrorNodes];
    let text = "";
    while (stack.length && text.length < limit) {
        const node = stack.shift();
        if (node.type === 'text') {
            text += ` ${node.text || ''}`;
        } else if (node.type === 'set' && setConfigs) {
            const handle = node.attrs?.values?.type;
            const set = setConfigs.find((s) => s.handle === handle);
            text += ` [${__(set ? set.display : handle)}]`;
        } else {
            if (node.content) stack.unshift(...node.content);
        }
    }
    return text.trim();
}
