export default function resetMetaFromResponse(responseMeta, container) {
    const existingMeta = container.meta;

    if (!responseMeta) return existingMeta;

    // The slug fieldtype tracks whether the slug still follows its source. The server never sends it.
    const auto = existingMeta?.slug?.auto;

    if (auto === undefined) return responseMeta;

    return { ...responseMeta, slug: { ...responseMeta.slug, auto } };
}
