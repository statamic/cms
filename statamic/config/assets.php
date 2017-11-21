<?php

return [

    // The route that Glide serves images through.
    // If using the cached option, this should be the URL of the cached_path.
    'image_manipulation_route' => 'img',

    // Whether Glide requires a security token
    'image_manipulation_secure' => true,

    // The image driver Glide will use. Can be gd or imagemagick
    'image_manipulation_driver' => 'gd',

    // Whether Glide should serve cached images directly.
    // This is a performance feature which prevents requests going back through Statamic, the web
    // server will serve the images without needing to hit PHP. The downside is that you will
    // lose the dynamic nature of Glide and need to delete manipulated images manually.
    'image_manipulation_cached' => false,

    // When using the cached option, this is where Glide should save the images
    'image_manipulation_cached_path' => 'img',

    // Whether assets should automatically get cropped when using image manipulations.
    'auto_crop' => true,

    // Prevent thumbnails being generated for images greater than these dimensions.
    'thumbnail_max_width' => 6000,
    'thumbnail_max_height' => 6000,

];
