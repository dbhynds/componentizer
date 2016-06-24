<?php

namespace Componentizer\Admin;

// Don't bother on the front end or non-admins
if (!is_admin()) return;

class Admin {
  
  // Options that will be loaded via config
  protected $component_templates = [];
  protected $location_orders = [];
  protected $ignore_files = ['.','..','.DS_Store'];
  protected $allowed_post_types;
  protected $settings;
  // protected $json_path;

  const NS = 'componentizer';

  function __construct() {
    // Make sure ACF is enabled
    add_action( 'admin_init', array($this,'check_for_acf') );
    // Check for sync
    // add_action( 'admin_init', array($this,'check_for_sync_on_init') );
    // Enqueue admin scripts and styles
    add_action( 'admin_enqueue_scripts', array($this,'enqueue_scripts') );
    $this->settings = get_option('componentizer_advanced_settings');
    // $this->json_path = get_stylesheet_directory().'/'.$this->settings['json_path'];
  }

  /**
   * Check if ACF Pro is enabled
   */
  function check_for_acf() {
    if (!is_plugin_active('advanced-custom-fields-pro/acf.php') ) {
      add_action( 'admin_notices', array($this,'require_acf') );
    }
  }
  /**
   * Add an admin notice that ACF Pro is required
   */
  function require_acf() {
    echo '<div class="error"><p>'.__('Error: Advanced Custom Fields Pro must be active.', $this::NS).'</p></div>';
  }

  /**
   * Enqueue admin scripts and styles
   */
  function enqueue_scripts() {
    $asset_base = plugins_url('componentizer/assets/','componentizer');
    wp_enqueue_style('componentizer', $asset_base.'componentizer.css' );
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('componentizer', $asset_base.'componentizer.js',array('jquery-ui-sortable'));
  }

  /**
   * Get the post types to use Componentizer for
   */
  function set_allowed_post_types() {
    $post_types = get_post_types();
    $settings = get_option('componentizer_advanced_settings');
    if ($settings['exclude_post_types']) {
      $this->allowed_post_types = array_diff($post_types, $settings['exclude_post_types']);
    }
  }

  /**
   * Get all ACF Group posts
   * @param  boolean $args Default $args for get_posts()
   * @return array         Array of ACF Group posts of type $post
   */
  function get_acfs($args = false) {
    if (!$args) {
      $args = [
        'post_type' => 'acf-field-group',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'order' => 'ASC',
        'orderby' => 'title',
      ];
    }
    return get_posts($args);
  }
  /*function get_acf_by_id($id) {
    $args = [
      'name' => $id,
      'post_type' => 'acf-field-group',
      'post_status' => 'publish',
      'posts_per_page' => 1,
    ];
    $post = get_posts($args);
    return array_pop($post);
  }
  function get_title_by_id($id) {
    $post = $this->get_acf_by_id($id);
    $title = get_the_title($post);
    return $title;
  }*/


  /*// Check if settings need to be synced
  function check_for_sync() {
    $json_times_db = get_option('componentizer_json_timestamps');
    $json_times_files = $this::get_json_file_timestamps();
    return ($json_times_db !== $json_times_files);
  }
  function check_for_sync_on_init() {
    if ($this::check_for_sync()) {
      add_action( 'admin_notices', array($this,'sync_needed') );
    }
  }
  // Notify if a sync is needed
  function sync_needed() {
    echo '<div class="error"><p>'.__('There is a sync available for Componentizer.', $this::NS).'</p></div>';
  }*/

  /*function get_json_files() {
    if ($this->json_path) {
      $json_files = scandir($this->json_path);
      $return_files = [];
      foreach ($json_files as $json_file) {
        if (!in_array($json_file, $this->ignore_files)) {
          array_push($return_files, $json_file);
        }
      }
      return $return_files;
    } else {
      return;
    }
  }

  function save_json_file($file) {
    $json_dir = $this::get_json_files();
    $arr_to_json = [
      $file => get_option('componentizer_'.$file),
      'timestamp' => time(),
    ];
    $json = acf_json_encode($arr_to_json);
    $f = fopen($this->json_path."/{$file}.json", 'w');
    fwrite($f, $json);
    fclose($f);
    $this::save_json_to_db();
  }

  function get_json_file_timestamps() {

    $json_files = $this::get_json_files();
    $json_data = [];
    foreach ($json_files as $json_file) {
      $json_data[$json_file] = hash_file('md5', $this->json_path."/{$json_file}");
      $file_name = $this->json_path.'/'.$json_file;
      $f = fopen($file_name, 'r');
      $json_file_data = fread($f, filesize($file_name));
      fclose($f);
      $obj_file_data = json_decode($json_file_data, true);
      $json_data[$json_file] = $obj_file_data['timestamp'];
    }
    return $json_data;

  }

  function save_json_to_db() {
    $json_data = $this::get_json_file_timestamps();
    update_option('componentizer_json_timestamps',$json_data);
  }*/

}

// Initialize Admin
new Admin();
