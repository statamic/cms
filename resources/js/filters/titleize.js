module.exports = function(value) {
    if(!value.split) return value;

    var _titleizeWord = function(string) {
        return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
    },
    result = [];

    value.split(" ").forEach(function(w) {
        result.push(_titleizeWord(w));
    });

    return result.join(" ");
};
