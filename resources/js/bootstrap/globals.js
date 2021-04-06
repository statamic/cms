import marked from 'marked';
import { translate, translateChoice } from '../translations/translator';

global.cp_url = function(url) {
    url = Statamic.$config.get('cpUrl') + '/' + url;
    return tidy_url(url);
};

global.docs_url = function(url) {
    return tidy_url('https://statamic.dev/' + url);
};

global.resource_url = function(url) {
    url = Statamic.$config.get('resourceUrl') + '/' + url;
    return tidy_url(url);
};

global.tidy_url = function(url) {
    return url.replace(/([^:])(\/\/+)/g, '$1/')
}

global.relative_url = function(url) {
    return url.replace(/^(?:\/\/|[^/]+)*\//, '/');
}

global.file_icon = function(extension) {
    return resource_url('img/filetypes/'+ extension +'.png');
};

global.dd = function(args) {
    console.log(args);
};

global.data_get = function(obj, path, fallback=null) {
    // Source: https://stackoverflow.com/a/22129960
    var properties = Array.isArray(path) ? path : path.split('.');
    var value = properties.reduce((prev, curr) => prev && prev[curr], obj);
    return value !== undefined ? value : fallback;
};

global.clone = function (value) {
    if (value === undefined) return undefined;

    return JSON.parse(JSON.stringify(value));
}

global.Cookies = require('cookies-js');

global.tailwind_width_class = function (width) {
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

global.markdown = function (value) {
    return marked(value);
};

global.__ = function (string, replacements) {
    return translate(string, replacements);
}
global.__n = function (string, number, replacements) {
    return translateChoice(string, number, replacements);
}

global.utf8btoa = function (stringToEncode) {
    // first we convert it to utf-8
    const utf8String = encodeURIComponent(stringToEncode)
      .replace(/%([0-9A-F]{2})/g, (_, code) => String.fromCharCode(`0x${code}`));

    // return base64 encoded string
    return btoa(utf8String);
}
