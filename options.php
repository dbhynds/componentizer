<?php

namespace Components;

$settings = get_option('componentizer_advanced_settings');
define(__NAMESPACE__.'\\COMPONENT_PATH',$settings['component_path']);
define(__NAMESPACE__.'\\JSON_PATH',get_stylesheet_directory().'/'.$settings['json_path']);
unset($settings);

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

  usort($local_components, __NAMESPACE__.'\\sort_'.$location);

  return $local_components;
}

function sort_top($a, $b) {
  $a_key = array_search($a['id'], $this->options['top_components']);
  $b_key = array_search($b['id'], $this->options['top_components']);
  return sort($a_key,$b_key);
}
function sort_bottom($a, $b) {
  $a_key = array_search($a['id'], $this->options['bottom_components']);
  $b_key = array_search($b['id'], $this->options['bottom_components']);
  return sort($a_key,$b_key);
}
function sort($a_key, $b_key) {
  if ($a_key == $b_key) {
    return 0;
  }
  return ($a_key < $b_key) ? -1 : 1;
}