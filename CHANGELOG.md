# Release Notes

## Unreleased

### What's new
- The `form` tags get a `submission_created` boolean. #2285
- The `template` fieldtype will ignore views in the `partials` directory when `hide_partials` is enabled. #2249
- The "first child" option is only in `link` fieldtypes if the entry is in a structured collection. #2209
- A Blueprint's `parent` will be the Collection/Taxonomy when creating an Entry/Term.
- PHP short tags will be sanitized in Antlers templates.

### What's fixed
- Vuex store gets the site when creating entries. #2237
- Entry locale defaults to the default site. #2275
- Entry inherits its layout from an origin entry, if one exists. #1830
- Global site selector is scrollable. #1838
- Rogue closing tag removed. #2253
- The `FormSubmitted` event gets a `submission` property. #2271
- Images are inline in Replicator previews. #2267
- Addon thumbnail alignment. #2272
- Translations. #2282 #2256



## v3.0.0 (2020-08-19)

### Statamic 3 is Official! 🎉
The day has finally come. Statamic 3 is out of beta and into the wild!

**Learn more in our [launch announcement](https://statamic.com/blog/statamic-3-launch-announcement)**
