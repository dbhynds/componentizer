<?php

namespace Components\Admin;

use Components\Options as Options;

// Don't bother on the front end or non-admins
if (!is_admin()) return;

class ComponentizerAdmin {
  
  // Options that will be loaded via config
  protected $component_templates = [];
  protected $location_orders = [];
  protected $ignore_files = ['.','..','.DS_Store'];

  const NS = 'componentizer';

  function __construct() {
    // Make sure ACF is enabled
    add_action( 'admin_init', array($this,'check_for_acf') );
    // Check for sync
    // add_action( 'admin_init', array($this,'check_for_sync_on_init') );
    // Enqueue admin scripts and styles
    add_action( 'admin_enqueue_scripts', array($this,'enqueue_scripts') );
  }

  // Make sure ACF is enabled
  function check_for_acf() {
    if (!is_plugin_active('advanced-custom-fields-pro/acf.php') ) {
      add_action( 'admin_notices', array($this,'require_acf') );
    }
  }
  // If not, show a warning
  function require_acf() {
    echo '<div class="error"><p>'.__('Error: Advanced Custom Fields must be active.', $this::NS).'</p></div>';
  }



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
  function get_acf_by_id($id) {
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
    if (!$title) $title = ucwords($top_field);
    return $title;
  }


  // Check if settings need to be synced
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
  }

  // Enqueue styles and scripts
  function enqueue_scripts() {
    $asset_base = plugins_url('componentizer/assets/','componentizer');
    wp_enqueue_style('componentizer', $asset_base.'componentizer.css' );
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('componentizer', $asset_base.'componentizer.js',array('jquery-ui-sortable'));
  }

  function get_json_files() {
    if (Options\JSON_PATH) {
      $json_files = scandir(Options\JSON_PATH);
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
    $f = fopen(Options\JSON_PATH."/{$file}.json", 'w');
    fwrite($f, $json);
    fclose($f);
    $this::save_json_to_db();
  }

  function get_json_file_timestamps() {

    $json_files = $this::get_json_files();
    $json_data = [];
    foreach ($json_files as $json_file) {
      $json_data[$json_file] = hash_file('md5', Options\JSON_PATH."/{$json_file}");
      $file_name = Options\JSON_PATH.'/'.$json_file;
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
  }

}

// Initialize Admin
new ComponentizerAdmin();
