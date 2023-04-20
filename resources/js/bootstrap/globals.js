import { marked } from 'marked';
import { translate, translateChoice } from '../translations/translator';

export function cp_url(url) {
    url = Statamic.$config.get('cpUrl') + '/' + url;
    return tidy_url(url);
};

export function docs_url(url) {
    return tidy_url('https://statamic.dev/' + url);
};

export function resource_url(url) {
    url = Statamic.$config.get('resourceUrl') + '/' + url;
    return tidy_url(url);
};

export function tidy_url(url) {
    return url.replace(/([^:])(\/\/+)/g, '$1/')
}

export function relative_url(url) {
    return url.replace(/^(?:\/\/|[^/]+)*\//, '/');
}

export function dd(args) {
    console.log(args);
};

export function data_get(obj, path, fallback=null) {
    // Source: https://stackoverflow.com/a/22129960
    var properties = Array.isArray(path) ? path : path.split('.');
    var value = properties.reduce((prev, curr) => prev && prev[curr], obj);
    return value !== undefined ? value : fallback;
};

export function data_set(obj, path, value) {
    // Source: https://stackoverflow.com/a/20240290
    var parts = path.split('.');
    while (parts.length - 1) {
        var key = parts.shift();
        var shouldBeArray = parts.length ? new RegExp('^[0-9]+$').test(parts[0]) : false;
        if (! (key in obj)) obj[key] = shouldBeArray ? [] : {};
        obj = obj[key];
    }
    obj[parts[0]] = value;
};

export function clone(value) {
    if (value === undefined) return undefined;

    return JSON.parse(JSON.stringify(value));
}

export function tailwind_width_class(width) {
    const widths = {
        25: '1/4',
        33: '1/3',
        50: '1/2',
        66: '2/3',
        75: '3/4',
        100: 'full'
    };

    return `w-${widths[width] || 'full'}`;
}

export function markdown(value) {
    return marked(value);
};

export function __(string, replacements) {
    return translate(string, replacements);
}
export function __n(string, number, replacements) {
    return translateChoice(string, number, replacements);
}

export function utf8btoa(stringToEncode) {
    // first we convert it to utf-8
    const utf8String = encodeURIComponent(stringToEncode)
      .replace(/%([0-9A-F]{2})/g, (_, code) => String.fromCharCode(`0x${code}`));

    // return base64 encoded string
    return btoa(utf8String);
}
