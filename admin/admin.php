<?php

namespace Components\Admin;

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
    // Enqueue admin scripts and styles
    add_action( 'admin_enqueue_scripts', array($this,'enqueue_scripts') );
  }

  // Make sure ACF is enabled
  function check_for_acf() {
    if (!is_plugin_active('advanced-custom-fields/acf.php') && !is_plugin_active('advanced-custom-fields-pro/acf.php')) {
      add_action( 'admin_notices', array($this,'require_acf') );
    }
  }
  // If not, show a warning
  function require_acf() {
    echo '<div class="error"><p>'.__('Error: Advanced Custom Fields must be active.', 'componentizer').'</p></div>';
  }

  // Enqueue styles and scripts
  function enqueue_scripts() {
    $asset_base = get_stylesheet_directory_uri().'/componentizer/assets/';
    wp_enqueue_style('componentizer', $asset_base.'componentizer.css' );
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('componentizer', $asset_base.'componentizer.js',array('jquery-ui-sortable'));
  }

}

// Initialize Admin
new ComponentizerAdmin();
