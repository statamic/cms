var marked = require('marked');

marked.setOptions({
    gfm: true,
    breaks: Statamic.markdownHardWrap,
    tables: true
});

module.exports = function(value) {
    return marked(value);
};
