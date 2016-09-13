<?php

namespace Componentizer;

class FieldGroups {

  /**
   * Gets the title of an ACF field group by its ACF ID
   * @param  string $id ID of the ACF field to get
   * @return string     Title of the ACf field
   */
  public static function getTitleById($id)
  {
    $post = self::get_acf_by_id($id);
    $title = get_the_title($post);
    return $title;
  }
  /**
   * Get ACF field group post objects by their ACF ID
   * @param  string $id ID of the ACF field
   * @return post       ACF Field Group post object
   */
  private static function getAcfById($id) {
    $args = [
      'name' => $id,
      'post_type' => 'acf-field-group',
      'post_status' => 'publish',
      'posts_per_page' => 1,
    ];
    $post = get_posts($args);
    return array_pop($post);
  }

  /**
   * Get the field groups for a post
   * @param  integer $post_id The ID of the post. 
   * @return array            Array of AFC field group IDs
   */
  public static function getForPost($post_id) {
    $post_fields = get_post_meta($post_id,'_field_order',true);

    $all_field_groups = \acf_get_field_groups();
    $filtered_field_groups = acf_filter_field_groups($all_field_groups,array('post_id' => $post_id));
    $group_ids = array_column($filtered_field_groups,'key');
    $options = get_option( 'componentizer_fields' );
    foreach ($group_ids as $key => $group_id) {
      if (array_key_exists($group_id, $options) && !$options[$group_id]['component']) {
        unset($group_ids[$key]);
      }
    }
    $existing_ids = get_post_meta($post_id,'_field_order',true);
    if (!$existing_ids) $existing_ids = [];

    $merge = array_merge($existing_ids,$group_ids);
    $unique = array_unique($merge);
    $ids = array_intersect($unique,$group_ids);

    return $ids;
  }

  /**
   * Sort ACF Fields groups according to their location
   * @param  string $location      Can be 'top' or 'bottom'
   * @param  array  $component_ids ACF IDs
   * @return array                 List of ACF IDs belong to a location
   */
  public static function sortByLocation($location, $component_ids) {
    $local_components = [];
    $location_orders = get_option('componentizer_location_orders');
    if ($location_orders === '' || !$location_orders) $location_orders = [];
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

  /**
   * Do the actual sorting for the top
   */
  private static function sort_top($a, $b) {
    $orders = get_option('componentizer_location_orders');
    $top_components = $orders['top'];
    $a_key = array_search($a, $top_components);
    $b_key = array_search($b, $top_components);
    return self::sort($a_key,$b_key);
  }
  /**
   * Do the actual sorting for the bottom
   */
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

