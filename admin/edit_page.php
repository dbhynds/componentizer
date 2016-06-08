<?php

namespace Components\Admin;

// Don't bother on the front end or non-admins
if (!is_admin()) return;

class EditorPage extends ComponentizerAdmin {

  function __construct() {
    // Load up options
    $this->location_orders = get_option('componentizer_location_orders');

    // Add metaboxes to the appropriate post types
    add_action( 'admin_init', array($this,'set_allowed_post_types'), 0);
    add_action( 'admin_init', array($this,'add_metaboxes_to_posts'), 10);
    // Save Componentizer\build() to the_content
    add_action( 'save_post', array($this,'register_query_var'), 999);
    add_action( 'admin_enqueue_scripts', array( $this, 'save_componentizer_build' ) );

  }


  function add_metaboxes_to_posts() {
    // Add metaboxes to the appropriate post types
    foreach ($this->allowed_post_types as $post_type) {
      add_action( 'add_meta_boxes_'.$post_type, array($this,'add_component_order_box') );
      remove_post_type_support($post_type,'editor');
    }
    add_action( 'save_post', array($this,'component_order_save_meta_box_data'), 10 );

  }
  
  // Add the component order metabox to the editor page
  function add_component_order_box() {
    add_meta_box( 'mb_component_field_order', __('Component Order','componentizer'), array($this,'component_order_box'), null, 'side', 'high' );
  }
  function component_order_box() {
    // Add a nonce
    wp_nonce_field( 'component_order_save_meta_box_data', 'component_order_meta_box_nonce' );

    // Get a list components on the page
    $all_field_groups = acf_get_field_groups();
    $filtered_field_groups = acf_filter_field_groups($all_field_groups,array('post_id' => get_the_ID()));
    $field_groups = array_column($filtered_field_groups,'key');

    $top = \Components\sort_groups_by_location('top',$field_groups);
    $bottom = \Components\sort_groups_by_location('bottom',$field_groups);
    $middle = array_diff($field_groups,$top,$bottom);
    usort($top, array($this,'sort_top'));
    usort($bottom, array($this,'sort_bottom'));
    $fields = compact('top','middle','bottom');
    // var_dump($fields);

    // List the components
    echo '<div class="component-order-sort-wrap">';
    // List the top components
    foreach ($fields['top'] as $field) {
      // var_dump($field['sortable']
      echo '<div class="postbox component">';
      echo '<input type="checkbox" name="component_order_field_order[]" value="'.$field.'" checked style="display: none;" />';
      echo '<span>'.$this->get_title_by_id($field).'</span>';
      echo '</div>';
    }
    // List sortable components
    echo '<div id="order-components" class="order-components component-order-sort">';
    foreach ($fields['middle'] as $field) {
      // var_dump($field['sortable']
      echo '<div class="postbox component">';
      echo '<input type="checkbox" name="component_order_field_order[]" value="'.$field.'" checked style="display: none;" />';
      echo '<span class="sortable ui-sortable-handle">'.$this->get_title_by_id($field).'</span>';
      echo '</div>';
    }
    echo '</div>';
    // List the bottom components
    foreach ($fields['bottom'] as $field) {
      // var_dump($field['sortable']
      echo '<div class="postbox component">';
      echo '<input type="checkbox" name="component_order_field_order[]" value="'.$field.'" checked style="display: none;" />';
      echo '<span>'.$$this->get_title_by_id($field).'</span>';
      echo '</div>';
    }
    echo '</div>';
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

  function register_query_var($post_id) {
    add_filter( 'redirect_post_location', array( $this, 'add_componentizer_query_var' ), 99 );
  }
  function add_componentizer_query_var( $location ) {
    remove_filter( 'redirect_post_location', array( $this, 'add_componentizer_query_var' ), 99 );
    return add_query_arg( array( 'build_components' => true ), $location );
  }
  function save_componentizer_build() {
    if ( (defined('DOING_AJAX') && DOING_AJAX) || !isset($_GET['build_components']) ) {
      return;
    }
    remove_action( 'admin_enqueue_scripts', array( $this, 'save_componentizer_build' ) );
    if (in_array(get_post_type(), $this->allowed_post_types)) {
      $componentizer = new \Components\Componentizer;
      $built_content = $componentizer->get_build();
      if ($built_content) {
        remove_action( 'save_post', array($this,'register_query_var'), 999);
        wp_update_post([
          'ID' => get_the_ID(),
          'post_content' => $built_content,
        ]);
        add_action( 'save_post', array($this,'register_query_var'), 999);
      }
    }
  }

}

// Initialize settings page
new EditorPage();
