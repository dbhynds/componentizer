<?php
/*
Plugin Name: Componentizer
Description: A tiny MVC for WordPress components.
Version: 1.4.0
Author URI: http://davohynds.com
Author: Davo Hynds
*/

// we look for Composer files first in the plugins dir.
// then in the wp-content dir (site install).
// and finally in the current themes directories.
if ( file_exists( $composer_autoload = __DIR__ . '/vendor/autoload.php' ) ) {
  $composer = require $composer_autoload;
  new \Componentizer\Componentizer;

  // Audoload Controllers directory
  $controllers_directory = get_template_directory().'/';
  $componentizer_advanced_settings = get_option('componentizer_advanced_settings');
  if ($componentizer_advanced_settings && array_key_exists('component_path', $componentizer_advanced_settings)) {
    $controllers_directory .= $componentizer_advanced_settings['component_path'];
  } else {
    $controllers_directory .= 'controllers';
  }
  if (file_exists($controllers_directory)) {
    $composer->add('\\Componentizer\\Controllers',$controllers_directory);
  } else {
    add_action('admin_notices',function(){
      echo '<div class="error"><p>'.__('Error: Controllers directory could not be found.', 'componentizer').'</p></div>';
    });
  }
}




register_activation_hook( __FILE__, 'componentizer_activate' );

function componentizer_activate() {
  
  $plugin_data = get_plugin_data(__FILE__);
  add_option('componentizer_db_version',$plugin_data['Version']);
  add_action('after_theme_setup', 'install');

  $exclude_post_types = get_post_types(['public' => false]);

  add_option('componentizer_advanced_settings',[
    'json_path' => 'componentizer-json',
    'component_path' => 'controllers',
    'exclude_post_types' => $exclude_post_types,
  ]);
  add_option('componentizer_fields',[]);
  add_option('componentizer_visible_on_archive',[]);
  add_option('componentizer_location_orders',[
    'top'=>[],
    'bottom'=>[]
  ]);

  $db_version = get_option( "componentizer_db_version" );
  if ( $db_version != $plugin_data['Version'] ) {
  }

  add_role( 'edit_componentizer_options', __('Componentizer Options','componentizer'), array( 'level_10' => true ) );

}