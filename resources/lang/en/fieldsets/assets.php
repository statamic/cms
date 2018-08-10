<?php

return [

    'image_manipulation_route' => 'Image assets route',
    'image_manipulation_route_instruct' => 'The URL where your resizable image assets will be served. If serving cached images directly, this should be the URL of the cached path.',

    'image_manipulation_secure' => 'Secure image assets',
    'image_manipulation_secure_instruct' => 'Should image resizing be secured? This will require that you generate keys using your tags.',

    'auto_crop' => 'Automatic image crop',
    'auto_crop_instruct' => 'Should images be automatically cropped? Their focal points will be used, if specified.',

    'image_manipulation_driver' => 'Image Manipulation Driver',
    'image_manipulation_driver_instruct' => "In some cases ImageMagick can be faster, but isn't available on all servers.",

    'image_manipulation_cached' => 'Serve cached images directly',
    'image_manipulation_cached_instruct' => 'Should images be generated before they are requested? <a href="https://docs.statamic.com/reference/tags/glide#serving-cached-images" target="_blank">Read more</a>.',

    'image_manipulation_cached_path' => 'Cached images path',
    'image_manipulation_cached_path_instruct' => 'When serving cached images directly, this is where they will be stored. This must be a publicly accessible location.',

    'image_manipulation_presets' => 'Image Manipulation Presets',
    'image_manipulation_presets_instruct' => 'Specify any <a href="http://glide.thephpleague.com/1.0/config/defaults-and-presets/#presets" target="_blank">Glide presets</a> (as YAML) that you want to be referenced within templates. These will be automatically generated when assets are uploaded.',

    'thumbnail_max_width' => 'Max image width for thumbnails',
    'thumbnail_max_width_instruct' => 'Images wider than this will not have thumbnails generated.',

    'thumbnail_max_height' => 'Max image height for thumbnails',
    'thumbnail_max_height_instruct' => 'Images taller than this will not have thumbnails generated.',


];
