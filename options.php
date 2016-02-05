<?php

namespace Components\Options;

function add_componentizer_role() {
   add_role( 'edit_componentizer_options', __('Componentizer Options','componentizer'), array( 'level_10' => true ) );
}
register_activation_hook( __FILE__, 'add_componentizer_role' );

function config_missing() {
  echo '<div class="error"><p>'.__('<strong>Error:</strong> Missing <code>config.php</code> file found for Componentizer found in <code>'.get_stylesheet_directory().'/componentizer/</code>. Copy and rename <code>componentizer/config-sample.php</code> to get started.', 'componentizer').'</p></div>';
}

if (!$filepath = locate_template('componentizer/config.php')) {
  // trigger_error(sprintf(__('Error locating %s for inclusion', 'componentizer'), $file), E_USER_ERROR);
  add_action( 'admin_notices', __NAMESPACE__ . '\config_missing' );
  define(__NAMESPACE__ . '\COMPONENT_PATH','');
  return;
}
require_once $filepath;
unset($filepath);
define(__NAMESPACE__ . '\COMPONENT_PATH',$controllers_path);

if ($componentizer_json_path && file_exists(get_stylesheet_directory().'/'.$componentizer_json_path)) {
  define(__NAMESPACE__ . '\JSON_PATH',get_stylesheet_directory().'/'.$componentizer_json_path);
}


$fields = [];
foreach ($persistant_fields as $field) {
  $fields[$field] = null;
}
add_option('componentizer_fields', $fields);
add_option('componentizer_visible_on_archive',[]);
add_option('componentizer_location_orders',['top'=>[],'bottom'=>[]]);

// Set the above configuration options to an associative array for easy retrieval later
$options = array(
  'exclude_order_for_post_types' => $exclude_order_for_post_types, // Array of post types that should not have the Field Order metabox
  'persistant_fields' => $persistant_fields,
);
/**
 * Returns either the requested option or an array of all of the options
 * @param  string $key Array key of the option to get
 * @return array       Value of the option requested
 */
function get_options($key = false) {
  global $options;
  if ($key && array_key_exists($key,$options)) {
    return $options[$key];
  } else {
    return $options;
  }
}

