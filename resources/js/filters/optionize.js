// Format option lists correctly, supporting both primitive and complex objects

module.exports = function(value) {
	options = JSON.parse(JSON.stringify(value));

	return format_input_options(options);
};