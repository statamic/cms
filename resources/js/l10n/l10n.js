// Bring in the Lang library
global.Lang = require('./lang');

// Global aliases
global.translate = function(key, replacements) {
    var message = Lang.get(key, replacements);

    if (message === key) {
        return makeTitle(message.replace('cp.', ''));
    }

    return message;
};

global.translate_choice = function(key, count, replacements) {
    return Lang.choice(key, count, replacements);
};

// Set the translation messages. The object will be in the page body.
Lang.setMessages(Statamic.translations);

function makeTitle(slug) {
    var words = slug.split(/[-_]/);

    for(var i = 0; i < words.length; i++) {
      var word = words[i];
      words[i] = word.charAt(0).toUpperCase() + word.slice(1);
    }

    return words.join(' ');
}
