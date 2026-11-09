export default function resetMetaFromResponse(responseMeta, container) {
    // The slug fieldtype tracks whether the slug still follows its source. The server never sends it.
    const auto = container.meta.slug?.auto;

    if (auto === undefined) return responseMeta;

    return { ...responseMeta, slug: { ...responseMeta.slug, auto } };
}
