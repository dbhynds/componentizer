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
define(__NAMESPACE__ . '\COMPONENT_PATH', $component_path);

// Set the HTML Template Path
$template_path = ($html_template_path) ? get_stylesheet_directory().'/'.$html_template_path : false;
define('Templates\\TEMPLATE_PATH', $template_path);

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

