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
if ( file_exists( $composer_autoload = __DIR__ . '/vendor/autoload.php' ) /* check in self */
  || file_exists( $composer_autoload = WP_CONTENT_DIR.'/vendor/autoload.php') /* check in wp-content */
  || file_exists( $composer_autoload = plugin_dir_path( __FILE__ ).'vendor/autoload.php') /* check in plugin directory */
  || file_exists( $composer_autoload = get_stylesheet_directory().'/vendor/autoload.php') /* check in child theme */
  || file_exists( $composer_autoload = get_template_directory().'/vendor/autoload.php') /* check in parent theme */
) {
  $composer = require_once $composer_autoload;
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


 
// Require these files
/*$componenentizer_includes = array(
  'FieldGroups.php',
  'Components.php',
  'Context.php',
  'admin/Admin.php',
  'admin/SettingsPage.php',
  'admin/EditPage.php',
);

$plugin_directory = plugin_dir_path(__FILE__);
foreach ($componenentizer_includes as $file) {
  $filepath = $plugin_directory.$file;
  if (!file_exists($filepath)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'componentizer'), $filepath), E_USER_ERROR);
  }

  require_once $filepath;
}
unset($file, $filepath);*/

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