// import orderby from '../filters/orderby';
import deslugify from '../filters/deslugify';
import markdown from '../filters/markdown';
import parse from '../filters/parse';
import pre from '../filters/pre';
import pluck from '../filters/pluck';
import reverse from '../filters/reverse';
import striptags from '../filters/striptags';
import titleize from '../filters/titleize';

export default {
    // @todo(jasonvarga): remove this if it's not used, or fix implementation.
    // caseInsensitiveOrderBy: orderby,
    deslugify,
    markdown,
    parse,
    pre,
    pluck,
    reverse,
    striptags,
    titleize,
}
