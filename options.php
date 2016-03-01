<?php

namespace Components\Options;

$settings = get_option('componentizer_advanced_settings');

define(__NAMESPACE__.'\\COMPONENT_PATH',$settings['component_path']);
define(__NAMESPACE__.'\\JSON_PATH',get_stylesheet_directory().'/'.$settings['json_path']);

/**
 * Returns either the requested option or an array of all of the options
 * @param  string $key Array key of the option to get
 * @return array       Value of the option requested
 */
function get_options($key = false) {
  return;
  if ($key && array_key_exists($key,$options)) {
    return $options[$key];
  } else {
    return $options;
  }
}

function sort_groups_by_location($location, $component_ids) {
  $local_components = [];
  $location_orders = get_option('componentizer_location_orders');
  if ($location_orders === '') $location_orders = [];
  if (!array_key_exists($location, $location_orders)) $location_orders[$location] = [];

  foreach ($location_orders[$location] as $value) {
    $component = array_search($value, $component_ids);
    if ($component !== false) {
      array_push($local_components, $value);
      unset($component_ids[$component]);
    }
    unset($component);
  }

  return $local_components;
}