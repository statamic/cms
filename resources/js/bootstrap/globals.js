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
