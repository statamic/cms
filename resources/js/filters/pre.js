module.exports = function(value) {
    return '<pre><code>'+JSON.stringify(value, null, 2)+'</code></pre>';
};
