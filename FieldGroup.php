<?php

namespace Components;

class FieldGroups {

  public static function get_title_by_id($id) {
    $post = self::get_acf_by_id($id);
    $title = get_the_title($post);
    return $title;
  }
  private static function get_acf_by_id($id) {
    $args = [
      'name' => $id,
      'post_type' => 'acf-field-group',
      'post_status' => 'publish',
      'posts_per_page' => 1,
    ];
    $post = get_posts($args);
    return array_pop($post);
  }

  public static function get_for_post($post_id) {
    $all_field_groups = acf_get_field_groups();
    $filtered_field_groups = acf_filter_field_groups($all_field_groups,array('post_id' => $post_id));
    $group_ids = array_column($filtered_field_groups,'key');
    return $group_ids;
  }

  public static function sort_by_location($location, $component_ids) {
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

    usort($local_components, __NAMESPACE__.'\\FieldGroups::sort_'.$location);

    return $local_components;
  }

  private static function sort_top($a, $b) {
    $orders = get_option('componentizer_location_orders');
    $top_components = $orders['top'];
    $a_key = array_search($a, $top_components);
    $b_key = array_search($b, $top_components);
    return self::sort($a_key,$b_key);
  }
  private static function sort_bottom($a, $b) {
    $orders = get_option('componentizer_location_orders');
    $bottom_components = $orders['bottom'];
    $a_key = array_search($a, $bottom_components);
    $b_key = array_search($b, $bottom_components);
    return self::sort($a_key,$b_key);
  }
  private static function sort($a_key, $b_key) {
    if ($a_key == $b_key) {
      return 0;
    }
    return ($a_key < $b_key) ? -1 : 1;
  }
}

