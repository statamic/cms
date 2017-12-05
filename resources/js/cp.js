var $ = require('jquery');

global.cp_url = function(url) {
    url = Statamic.cpRoot + '/' + url;
    return url.replace(/\/+/g, '/');
};

global.resource_url = function(url) {
    url = Statamic.resourceUrl + '/' + url;
    return url.replace(/\/+/g, '/');
};

// Get url segments from the nth segment
global.get_from_segment = function(count) {
    return Statamic.urlPath.split('/').splice(count).join('/');
};

global.format_input_options = function(options) {

	if (typeof options[0] === 'string') {
		return options;
	}

	var formatted = [];
	_.each(options, function(value, key, list) {
	    formatted.push({'value': key, 'text': value});
	});

	return formatted;
};

global.file_icon = function(extension) {
    return resource_url('img/filetypes/'+ extension +'.png');
};

global.dd = function(args) {
    console.log(args);
};

global.data_get = function(obj, key) {
    return key.split(".").reduce(function(o, x) {
        return (typeof o == "undefined" || o === null) ? o : o[x];
    }, obj);
};

global.Cookies = require('cookies-js');

// String.includes() polyfill.
// See https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/includes
if (!String.prototype.includes) {
    String.prototype.includes = function(search, start) {
        'use strict';
        if (typeof start !== 'number') {
            start = 0;
        }

        if (start + search.length > this.length) {
            return false;
        } else {
            return this.indexOf(search, start) !== -1;
        }
    };
}

require('./l10n/l10n');
