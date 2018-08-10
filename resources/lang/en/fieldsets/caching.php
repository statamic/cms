<?php

return [

    'stache' => 'The "Stache"',
    'stache_instruct' => 'Our affectionate name for Statamic\'s internal cache.',

    'stache_always_update' => 'Update the Stache on every request?',
    'stache_always_update_instruct' => 'Updating on every request will allow changes directly to files be detected, but it comes with a performance hit.
                                       If you\'re using the control panel to manage content, you can disable this as the Stache will get updated when you publish.',

   'static_caching' => 'Static Page Caching',
   'static_caching_instruct' => 'Static Page Caching allows your web pages to be saved as flat HTML files for incredible speed benefits.',

   'static_caching_enabled' => 'Enable',

   'static_caching_length' => 'Default Cache length',
   'static_caching_length_instruct' => 'How long should each page be cached, in minutes. This only applies when using the "cache" type.',

   'static_caching_type' => 'Caching Type',
   'static_caching_type_instruct' => 'Saving to file will generate html files at `static`, and you will need to set up rewrite rules on your server.
                                      <a href="https://docs.statamic.com/caching#static-page" target="_blank">Read more</a>.  
                                      Otherwise, the standard cache will be used.',

    'static_caching_file_path' => 'Static Files Path',
    'static_caching_file_path_instruct' => 'The location where files will be written when using file-based caching.',

    'static_caching_ignore_query_strings' => 'Ignore query strings',
    'static_caching_ignore_query_strings_instruct' => 'When enabled, a page will be treated as the same URL regardless of any query string parameters.  
                                                       Cannot be disabled when using file-based static caching.',

    'static_caching_exclude' => 'Excluded URLs',
    'static_caching_exclude_instruct' => 'A list of URLs that should be excluded from caching.',

    'static_caching_invalidation' => 'Invalidation Rules',
    'static_caching_invalidation_instruct' => 'An array of invalidation rules.',


];
