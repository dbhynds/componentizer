<?php

namespace Components\Admin;

// Don't bother on the front end or non-admins
if (!is_admin()) return;

use Components\Options as Options;

class EditorPage extends ComponentizerAdmin {

  function __construct() {
    // Load up options
    $this->options = Options\get_options();
    $this->location_orders = get_option('componentizer_location_orders');

    // If no config file, die.
    if ($this->options == null) return;

    add_action('init',array($this,'setup_edit_page'));

  }

  function setup_edit_page() {
    // Add metaboxes to the appropriate post types
    $post_types = get_post_types(array('public' => true));
    $post_types = array_diff($post_types, $this->options['exclude_order_for_post_types']);
    foreach ($post_types as $post_type) {
      add_action( 'add_meta_boxes_'.$post_type, array($this,'add_component_order_box') );
    }
    add_action( 'save_post', array($this,'component_order_save_meta_box_data') );
  }
  
  // Add the component order metabox to the editor page
  function add_component_order_box() {
    add_meta_box( 'mb_component_field_order', 'Component Order', array($this,'component_order_box'), null, 'side', 'high' );
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
    $current_fields = $fields_top = $fields_middle = $fields_bottom = $fields = array();

    // Get the metabox IDs of ACF field groups on this page
    $filter = array( 'post_id' => $post->ID );
    $metabox_ids = array();
    $groups = acf_get_field_groups($filter);
    foreach ($groups as $group) {
      array_push($metabox_ids, $group['ID']);
    }
    
    // Include persistent fields and ACF field groups
    // We'll iterate through the various fields and unset them here if they exist.
    $all_fields = array_merge($metabox_ids,$this->options['persistant_fields']);
    // var_dump($all_fields);

    // Get the saved order, if any.
    $field_ids = get_post_meta( $post->ID, '_field_order', true );
    // var_dump($field_ids);

    // If there is a saved order, sort the fields into top, middle, or bottom
    
    $options = get_option( 'componentizer_fields' );
    if ($field_ids) {
      foreach ($field_ids as $field_id) {
        if (in_array($field_id, $all_fields)) {
          // If this field exists, unset it in $all_fields
          $all_fields = array_diff($all_fields, [$field_id]);
          // Setup the field data
          $field_title = get_the_title($field_id);
          if ($field_title === '') $field_title = ucwords($field_id);
          $field_args = array(
              'id' => $field_id,
              'name' => $field_title,
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
  
    // Sort all of the ACF fields into top, middle, and bottom.
    if (count($all_fields)) {
      $acf_field_posts = get_posts(array('post__in' => $all_fields,'post_type' => 'acf-field-group'));
      foreach ($acf_field_posts as $acf_field_post) {
        // If this field exists, unset it in $all_fields
        $all_fields = array_diff($all_fields, array($acf_field_post->ID));
        $field_id = $acf_field_post->ID;
        // Setup the field data
        $field_args = array(
            'id' => $field_id,
            'name' => $acf_field_post->post_title,
          );
        // Add it to the appropriate section
        if ($options[$field_id]['location'] == 'top') {
          array_push($fields_top,$field_args);
        } elseif ($options[$field_id]['location'] == 'bottom') {
          array_push($fields_bottom,$field_args);
        } else {
          array_push($fields_middle,$field_args);
        }
      }
    }

    // Now, if there are any remaining fields, sort them into the correct buckets
    foreach ($all_fields as $all_field) {
      // Unset it in $all_fields
      $all_fields = array_diff($all_fields, array($all_field));
      // Setup the field data
      $field_args = [
          'id' => $all_field,
          'name' => ucwords($all_field),
        ];
      // Add it to the appropriate section
      if ($options[$all_field]['location'] ==  'top') {
        array_push($fields_top,$field_args);
      } elseif ($options[$all_field]['location'] == 'bottom') {
        array_push($fields_bottom,$field_args);
      } else {
        array_push($fields_middle,$field_args);
      }
    }

    $location_orders = get_option('componentizer_location_orders');
    
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
