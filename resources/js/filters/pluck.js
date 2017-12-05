module.exports = function(value, plucked) {
	return value.map(function(item) {
	    return item[plucked];
	});
};