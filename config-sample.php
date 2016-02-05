<?php

/**
 * Configure Componentizer.
 * For configuration help, see the "Componentizer" page
 * in the Appearance section of the WordPress Admin.
 */

// Path to component directory relative to current theme directory
$controllers_path = 'controllers';

// Fields that aren't from ACF but should still be included.
$persistant_fields = array('content');

// Array of post types that should not have the Component Order metabox
$exclude_order_for_post_types = array('nav_menu_item', 'revision', 'attachment');

// Path the the Componentizer JSON directory relative to current theme directory
$componentizer_json_path = null;
