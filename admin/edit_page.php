<?php

namespace Components\Admin;

// Don't bother on the front end or non-admins
if (!is_admin()) return;

use Components\Options as Options;

class EditorPage extends ComponentizerAdmin {

  function __construct() {
    // Load up options
    $this->location_orders = get_option('componentizer_location_orders');

    // Add metaboxes to the appropriate post types
    add_action( 'admin_init', array($this,'add_metaboxes_to_posts'));

  }

  function add_metaboxes_to_posts() {

    // Add metaboxes to the appropriate post types
    $post_types = get_post_types();
    if ($exclude_post_types = get_option('componentizer_exclude_post_types')) {
      $post_types = array_diff($post_types, $exclude_post_types);
    }
    foreach ($post_types as $post_type) {
      add_action( 'add_meta_boxes_'.$post_type, array($this,'add_component_order_box') );
    }
    add_action( 'save_post', array($this,'component_order_save_meta_box_data') );

  }
  
  // Add the component order metabox to the editor page
  function add_component_order_box() {
    add_meta_box( 'mb_component_field_order', __('Component Order','componentizer'), array($this,'component_order_box'), null, 'side', 'high' );
  }
  function component_order_box($post) {
    // Add a nonce
    wp_nonce_field( 'component_order_save_meta_box_data', 'component_order_meta_box_nonce' );

    // Get a list components on the page
    $fields = $this->admin_get_sortable_fields($post);
    // var_dump($fields);

    // List the components
    echo '<div class="component-order-sort-wrap">';
    // List the top components
    foreach ($fields['top'] as $field) {
      // var_dump($field['sortable']
      echo '<div class="postbox component">';
      echo '<input type="checkbox" name="component_order_field_order[]" value="'.$field['id'].'" checked style="display: none;" />';
      echo '<span>'.$field['name'].'</span>';
      echo '</div>';
    }
    // List sortable components
    echo '<div id="order-components" class="order-components component-order-sort">';
    foreach ($fields['middle'] as $field) {
      // var_dump($field['sortable']
      echo '<div class="postbox component">';
      echo '<input type="checkbox" name="component_order_field_order[]" value="'.$field['id'].'" checked style="display: none;" />';
      echo '<span class="sortable ui-sortable-handle">'.$field['name'].'</span>';
      echo '</div>';
    }
    echo '</div>';
    // List the bottom components
    foreach ($fields['bottom'] as $field) {
      // var_dump($field['sortable']
      echo '<div class="postbox component">';
      echo '<input type="checkbox" name="component_order_field_order[]" value="'.$field['id'].'" checked style="display: none;" />';
      echo '<span>'.$field['name'].'</span>';
      echo '</div>';
    }
    echo '</div>';
  }
  function admin_get_sortable_fields($post) {
    // Set everything to empty arrays
    $current_fields = $fields_top = $fields_middle = $fields_bottom = $fields = [];

    // Get the ACF field groups on this page
    $all_field_groups = acf_get_field_groups();
    $filtered_field_groups = acf_filter_field_groups($all_field_groups,array('post_id' => $post->ID));
    $field_groups = [];
    foreach ($filtered_field_groups as $filtered_field_group) {
      array_push($field_groups, $filtered_field_group['key']);
    }

    // Get the saved order, if any.
    $field_ids = get_post_meta( $post->ID, '_field_order', true );
    // var_dump($field_ids);

    // If there is a saved order, sort the fields into top, middle, or bottom
    
    $options = get_option( 'componentizer_fields' );
    if ($field_ids) {
      foreach ($field_ids as $field_id) {
        if (in_array($field_id, $field_groups)) {
          // If this field exists, unset it in $field_groups
          $field_groups = array_diff($field_groups, [$field_id]);
          // Setup the field data
          $field_args = array(
              'id' => $field_id,
              'name' => $this->get_title_by_id($field_id),
            );
          // Add it to the appropriate section
          if ($options[$field_id]['location'] == 'top') {
            array_push($fields_top,$field_args);
          } elseif ($options[$field_id]['location'] == 'bottom') {
            array_push($fields_bottom,$field_args);
          } else {
            array_push($fields_middle,$field_args);
          }
        } else {
        }
      }
    }

    // Now, if there are any remaining fields, sort them into the correct buckets
    foreach ($field_groups as $field_group) {
      // Unset it in $all_fields
      $field_groups = array_diff($field_groups, array($field_group));
      $name = (is_int($field_group)) ? get_the_title($field_group) : ucwords($field_group);
      // Setup the field data
      $field_args = [
          'id' => $field_group,
          'name' => $this->get_title_by_id($field_group),
        ];
      // Add it to the appropriate section
      if ($options[$field_group]['location'] ==  'top') {
        array_push($fields_top,$field_args);
      } elseif ($options[$field_group]['location'] == 'bottom') {
        array_push($fields_bottom,$field_args);
      } else {
        array_push($fields_middle,$field_args);
      }
    }

    $location_orders = get_option('componentizer_location_orders');
    if (!$location_orders) $location_orders = [];
    $this->options['top_components'] = (array_key_exists('top', $location_orders)) ? $location_orders['top'] : array();
    $this->options['bottom_components'] = (array_key_exists('bottom', $location_orders)) ? $location_orders['bottom'] : array();
    // Sort the top and bottom according to the order specified in the config file
    usort($fields_top, array($this,'sort_top'));
    usort($fields_bottom, array($this,'sort_bottom'));

    // var_dump($fields_top);
    // var_dump($fields_top);
    // var_dump($fields_middle);
    // var_dump($fields_bottom);
    
    // Return the field groupings
    $fields = array(
      'top' => $fields_top,
      'middle' => $fields_middle,
      'bottom' => $fields_bottom
    );
    // var_dump($fields);
    return $fields;
  }
  function sort_top($a, $b) {
    // Sort by array key (the order specified in the config file)
    $a_key = array_search($a['id'], $this->options['top_components']);
    $b_key = array_search($b['id'], $this->options['top_components']);
    if ($a_key == $b_key) {
      return 0;
    }
    return ($a_key < $b_key) ? -1 : 1;
  }
  function sort_bottom($a, $b) {
    // Sort by array key (the order specified in the config file)
    $a_key = array_search($a['id'], $this->options['bottom_components']);
    $b_key = array_search($b['id'], $this->options['bottom_components']);
    if ($a_key == $b_key) {
      return 0;
    }
    return ($a_key < $b_key) ? -1 : 1;
  }
  function component_order_save_meta_box_data($post_id) {

    if ( ! isset( $_POST['component_order_meta_box_nonce'] ) ) return;
    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['component_order_meta_box_nonce'], 'component_order_save_meta_box_data' ) ) return;
    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
      if ( ! current_user_can( 'edit_page', $post_id ) ) return;
    } else {
      if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    }

    /* OK, it's safe for us to save the data now. */
    // Sanitize user input.
    $field_order = array_map( 'sanitize_text_field', $_POST['component_order_field_order'] );
    // Update the meta field in the database.
    update_post_meta( $post_id, '_field_order', $field_order );

  }

}

// Initialize settings page
new EditorPage();
