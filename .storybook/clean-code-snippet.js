export default function cleanCodeSnippet(code) {
    // Extract template content from render functions or template tags
    let cleaned = code;

    // Handle render function format
    const renderMatch = code.match(/template:\s*`([\s\S]*?)`/);
    if (renderMatch) {
        cleaned = renderMatch[1];
    } else {
        // Handle simple template format
        cleaned = code.replace(/<template>([\s\S]*?)<\/template>/, '$1');
    }

    // Split into lines and remove common leading whitespace
    const lines = cleaned.split('\n').filter(line => line.length > 0 || line.trim().length > 0);

    // Find minimum indentation (ignoring empty lines)
    const indents = lines
        .filter(line => line.trim().length > 0)
        .map(line => line.match(/^(\s*)/)?.[1].length || 0);

    const minIndent = indents.length > 0 ? Math.min(...indents) : 0;

    // Remove the common indentation and trim
    return lines
        .map(line => line.slice(minIndent))
        .join('\n')
        .trim();
}
