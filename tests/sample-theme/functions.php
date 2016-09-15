<?php

add_filter('acf/settings/save_json', function() {
    return get_stylesheet_directory().'/models';
});
 
add_filter('acf/settings/load_json', function($paths) {
    $paths[] = get_stylesheet_directory().'/models';
    return $paths;
});