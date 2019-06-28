module.exports = function(value) {
	var striptags = require('striptags')

	return striptags(value);
};
