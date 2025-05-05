import { marked } from 'marked';
import { translate, translateChoice } from '../translations/translator';
import uid from 'uniqid';
import PreviewHtml from '../components/fieldtypes/replicator/PreviewHtml';

export function cp_url(url) {
    url = Statamic.$config.get('cpUrl') + '/' + url;
    return tidy_url(url);
}

export function docs_url(url) {
    return tidy_url('https://statamic.dev/' + url);
}

export function resource_url(url) {
    url = Statamic.$config.get('resourceUrl') + '/' + url;
    return tidy_url(url);
}

export function tidy_url(url) {
    return url.replace(/([^:])(\/\/+)/g, '$1/');
}

export function relative_url(url) {
    return url.replace(/^(?:\/\/|[^/]+)*\//, '/');
}

export function dd(args) {
    console.log(args);
}

export function data_get(obj, path, fallback = null) {
    // Source: https://stackoverflow.com/a/22129960
    var properties = Array.isArray(path) ? path : path.split('.');
    var value = properties.reduce((prev, curr) => prev && prev[curr], obj);
    return value !== undefined ? value : fallback;
}

export function data_set(obj, path, value) {
    // Source: https://stackoverflow.com/a/20240290
    var parts = path.split('.');
    while (parts.length - 1) {
        var key = parts.shift();
        var shouldBeArray = parts.length ? new RegExp('^[0-9]+$').test(parts[0]) : false;
        if (!(key in obj)) obj[key] = shouldBeArray ? [] : {};
        obj = obj[key];
    }
    obj[parts[0]] = value;
}

export function clone(value) {
    if (value === undefined) return undefined;

    return JSON.parse(JSON.stringify(value));
}

export function tailwind_width_class(width) {
    const widths = {
        25: 'w-full @lg:w-1/4',
        33: 'w-full @lg:w-1/3',
        50: 'w-full @lg:w-1/2',
        66: 'w-full @lg:w-2/3',
        75: 'w-full @lg:w-3/4',
        100: 'w-full',
    };

    return `${widths[width] || 'w-full'}`;
}

export function field_width_class(width) {
    const widths = {
        25: 'field-w-25',
        33: 'field-w-33',
        50: 'field-w-50',
        66: 'field-w-66',
        75: 'field-w-75',
        100: 'field-w-100',
    };

    return `${widths[width] || 'field-w-100'}`;
}

export function markdown(value) {
    return marked(value);
}

export function __(string, replacements) {
    return translate(string, replacements);
}
export function __n(string, number, replacements) {
    return translateChoice(string, number, replacements);
}

export function utf8btoa(stringToEncode) {
    // first we convert it to utf-8
    const utf8String = encodeURIComponent(stringToEncode).replace(/%([0-9A-F]{2})/g, (_, code) =>
        String.fromCharCode(`0x${code}`),
    );

    // return base64 encoded string
    return btoa(utf8String);
}

export function utf8atob(stringToDecode) {
    // Decode from base64 to UTF-8 byte representation
    const utf8String = atob(stringToDecode);

    // Convert the UTF-8 byte representation back to a regular string
    return decodeURIComponent(
        utf8String
            .split('')
            .map((c) => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
            .join(''),
    );
}

export function uniqid() {
    return uid();
}

export function truncate(string, length, ending = '...') {
    if (string.length <= length) return string;

    return string.substring(0, length - ending.length) + ending;
}

export function escapeHtml(string) {
    return string
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

export function replicatorPreviewHtml(html) {
    return new PreviewHtml(html);
}

export function closestVm(el, name) {
    let parent = el;
    while (parent) {
        if (parent.__vue__) break;
        parent = parent.parentElement;
    }
    let vm = parent.__vue__;
    while (vm !== vm.$root) {
        if (!name || name === vm.$options.name) return vm;
        vm = vm.$parent;
    }
}

export function str_slug(string) {
    return Statamic.$slug.create(string);
}

export function snake_case(string) {
    return Statamic.$slug.separatedBy('_').create(string);
}
