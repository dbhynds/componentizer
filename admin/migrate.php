<?php

namespace Componentizer\Admin;

// Don't bother on the front end or non-admins
if (!is_admin()) return;

class Migrate extends Admin {

  function __construct() {

    // Add the reference page to the admin menu
    // add_action( 'admin_menu', [$this,'add_menu_page'] );
    // add_action( 'admin_init', [$this,'set_allowed_post_types'], 0);
    // add_action( 'admin_init', [$this,'register_settings'], 10 );


    // Add metaboxes to the appropriate post types
    add_action( 'admin_init', array($this,'add_metaboxes_to_posts'));

  }

  function add_menu_page() {
    $slug = 'componentizer';
    $cap = 'manage_options';
    
    // add children
    add_submenu_page($slug, __('Migrate',$this::NS), __('Migrate',$this::NS), $cap, 'migrate', array($this,'migrate_page') );
    // add_submenu_page($slug, __('Migrate',$this::NS), __('Migrate',$this::NS), $cap, 'migrate', array($this,'assign_migrate') );
  }
  function migrate_page() {
    ?>
    <div id="componentizer-settings" class="wrap">
      <?php 
      echo '<h1>'.__('Compontentizer',$this::NS).'</h1>';
      echo '<form id="basic" action="options.php" method="post" style="clear:both;">';
        settings_fields( 'componentizerMigrate' );
        do_settings_sections( 'componentizerMigrate' );
      echo '</form>';
      ?>
    </div>
    <?php
  }
  function register_settings() {
    add_filter( 'update_option_componentizer_migrate', array($this, 'componentizer_migrate_update') );
    register_setting( 'componentizerMigrate', 'componentizer_migrate' );
    add_settings_section(
      'componentizer_migrate',
      __( 'Migrate Post Types to Componentizer', $this::NS ),
      array($this,'post_types_to_migrate'),
      'componentizerMigrate'
    );
  }
  function post_types_to_migrate() {
    $args = [
      'post_type' => 'acf',
      'posts_per_page' => -1,
      'post_status' => 'publish',
      'order' => 'ASC',
      'orderby' => 'title',
    ];
    $fields = $this->get_acfs($args);
    echo '<div id="migrate-destination" class="card">';
      echo '<label for="migration_destination">'.__('Destination for the content in ACF',$this::NS).'</label>';
      echo '<select id="migration_destination" name="componentizer_migrate[migration_destination]" />';
        echo '<option>-- Select --</option>';
        foreach ($fields as $field) {
          echo '<optgroup label="'.$field->post_title.'">';
            $field_metas = get_post_meta($field->ID);
            foreach ($field_metas as $meta_key => $meta_value) {
              if ( substr($meta_key,0,6) === 'field_') {
                $meta_value = unserialize($meta_value[0]);
                $selected = ($settings['migration_destination'] == $meta_value['key']) ? 'selected' : null;
                echo '<option value="'.$meta_value['key'].'" '.$selected.'>';
                  echo $meta_value['label'];
                echo '</option>';
              }
            }
            // echo '<option value="'.$field->post_excerpt.'" '.$selected.'>'.$field->post_title.' | ['.$field->post_excerpt.'] ('.$field->post_name.')</option>';
          echo '</optgroup>';
        }
      echo '</select>';
    echo '</div>';

    echo '<div id="migrate-post_types" class="card">';
    foreach ($this->allowed_post_types as $post_type) {
      echo '<p><label for="migrate-'.$post_type.'">';
        echo '<input type="checkbox" id="migrate-'.$post_type.'" name="componentizer_migrate[post_types]" value="'.$post_type.'">';
        echo $post_type;
      echo '</label></p>';
    }
    echo '</div>';

    submit_button();
  }
  function componentizer_migrate_update() {
    $migrate_settings = get_option('componentizer_migrate');
    $post_types = (array_key_exists('post_types', $migrate_settings)) ? $migrate_settings['post_types'] : false;
    if (!$post_types) return;
    foreach ($post_types as $post_type) {
      $args = [
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'post_status' => 'publish',
      ];
      $posts = get_posts($args);
      if (!is_array($posts)) $posts = [$posts];
      if ($posts) {
        global $post;
        foreach ($posts as $post) {
          $using_componentizer = get_post_meta($post->ID,'componentizer_use',true);
          if ($using_componentizer != 1) {
            setup_postdata($post);
            $built_content = Components\get_build();
            wp_reset_postdata();
            if ($built_content) {
              $old_content = get_the_content();
              update_field($migrate_settings['migration_destination'],$old_content,$post->ID);
              wp_update_post([
                'ID' => $post->ID,
                'post_content' => $built_content,
              ]);
            }
            update_post_meta($post->ID,'componentizer_use',true);
          }
        }
      }
    }

    remove_filter( 'update_option_componentizer_migrate', array($this, 'componentizer_migrate_update') );
    update_option('componentizer_migrate',null);
    add_filter( 'update_option_componentizer_migrate', array($this, 'componentizer_migrate_update') );
  }


  function add_metaboxes_to_posts() {
    $post_types = get_post_types();
    $settings = get_option('componentizer_advanced_settings');
    if ($settings['exclude_post_types']) {
      $this->allowed_post_types = array_diff($post_types, $settings['exclude_post_types']);
      // Add metaboxes to the appropriate post types
      foreach ($this->allowed_post_types as $post_type) {
        add_action( 'add_meta_boxes_'.$post_type, array($this,'add_component_migrate_box') );
      }
    }

    add_action( 'save_post', array($this,'migrate_save_meta_box_data'), 10 );

  }
  
  // Add the component order metabox to the editor page
  function add_component_migrate_box() {
    if (get_post_meta(get_the_ID(), 'componentizer_use')) return;
    add_meta_box( 'component_migrate', __('Use Componentizer','componentizer'), array($this,'migrate_box'), null, 'side', 'high' );
  }
  function migrate_box($post) {
    $use = get_post_meta(get_the_ID(), 'componentizer_use');
    wp_nonce_field( 'migrate_save_meta_box_data', 'migrate_meta_box_nonce' );

    echo '<input id="componentizer_use" name="componentizer_use" type="checkbox" value="1" />';
    echo '<label for="componentizer_use">'.__('Migrate post content to componentizer','componentizer').'</label>';
    $field_groups = new \Componentizer\FieldGroups;
    $group_ids = $field_groups->get_for_post(get_the_ID());
  }
  
  function migrate_save_meta_box_data($post_id) {

    // error_log($_POST['post_content']);
    if ( ! isset( $_POST['migrate_meta_box_nonce'] ) ) return;
    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['migrate_meta_box_nonce'], 'migrate_save_meta_box_data' ) ) return;
    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
      if ( ! current_user_can( 'edit_page', $post_id ) ) return;
    } else {
      if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    }
    
    if ( isset($_POST['componentizer_use'])) {
      // Update the meta field in the database.
      update_post_meta( $post_id, 'componentizer_use', true );
    }
  }

}

// Initialize settings page
new Migrate();
