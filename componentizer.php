<?php

// Require these files
$componenentizer_includes = array(
  'componentizer/options.php',
  'componentizer/components.php',
  'componentizer/admin/admin.php',
  'componentizer/admin/settings_page.php',
  'componentizer/admin/edit_page.php',
  'componentizer/template_parser.php',
);

foreach ($componenentizer_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'componentizer'), $file), E_USER_ERROR);
  }

  require_once $filepath;
}
unset($file, $filepath);