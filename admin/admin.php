<?php

namespace Components\Admin;

use Components\Options as Options;

// Don't bother on the front end or non-admins
if (!is_admin()) return;

class ComponentizerAdmin {
  
  // Options that will be loaded via config
  protected $options = array();
  protected $component_templates = array();
  protected $location_orders = array();

  function __construct() {
    // Make sure ACF is enabled
    add_action( 'admin_init', array($this,'check_for_acf') );
    // Check for sync
    add_action( 'admin_init', array($this,'check_for_sync_on_init') );
    // Enqueue admin scripts and styles
    add_action( 'admin_enqueue_scripts', array($this,'enqueue_scripts') );
  }

  // Make sure ACF is enabled
  function check_for_acf() {
    if (!is_plugin_active('advanced-custom-fields/acf.php') && !is_plugin_active('advanced-custom-fields-pro/acf.php') ) {
      add_action( 'admin_notices', array($this,'require_acf') );
    }
  }
  // If not, show a warning
  function require_acf() {
    echo '<div class="error"><p>'.__('Error: Advanced Custom Fields must be active.', 'componentizer').'</p></div>';
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
    echo '<div class="error"><p>'.__('There is a sync available for Componentizer.','componentizer').'</p></div>';
  }

  // Enqueue styles and scripts
  function enqueue_scripts() {
    $asset_base = get_stylesheet_directory_uri().'/componentizer/assets/';
    wp_enqueue_style('componentizer', $asset_base.'componentizer.css' );
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('componentizer', $asset_base.'componentizer.js',array('jquery-ui-sortable'));
  }

  function get_json_files() {
    if (Options\JSON_PATH) {
      $json_files = scandir(Options\JSON_PATH);
      $return_files = [];
      $ignore_files = array('.','..','.DS_Store');
      foreach ($json_files as $json_file) {
        if (!in_array($json_file, $ignore_files)) {
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

  function get_json_file_hashes() {
    $json_files = $this::get_json_files();
    $json_data = [];
    foreach ($json_files as $json_file) {
      $json_data[$json_file] = hash_file('md5', Options\JSON_PATH."/{$json_file}");
    }
    return $json_data;
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
