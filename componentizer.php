<?php
/*
Plugin Name: Componentizer
Description: A tiny MVC for WordPress components.
Version: 0.6
Author URI: http://davohynds.com
Author: Davo Hynds
*/
 
// Require these files
$componenentizer_includes = array(
  'options.php',
  'FieldGroup.php',
  'components.php',
  'admin/admin.php',
  'admin/settings_page.php',
  'admin/edit_page.php',
  'admin/migrate.php',
);

$plugin_directory = plugin_dir_path(__FILE__);
foreach ($componenentizer_includes as $file) {
  $filepath = $plugin_directory.$file;
  if (!file_exists($filepath)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'componentizer'), $filepath), E_USER_ERROR);
  }

  require_once $filepath;
}
unset($file, $filepath);

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