<?php

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

Class SetupTestDb {

  function __construct()
  {
    tests_add_filter( 'muplugins_loaded', [$this,'manually_load_plugins'] );
    tests_add_filter( 'muplugins_loaded', [$this,'import_acf_data'] );
  }

  function manually_load_plugins()
  {
    $sage = switch_theme('sage');
    $plugins = [
      'componentizer/componentizer.php',
      'advanced-custom-fields-pro/acf.php',
      'timber-library/timber.php'
    ];
    foreach ($plugins as $plugin) {
      require dirname( dirname( __FILE__ ) ) . '/../' . $plugin;
    }
    update_option( 'active_plugins', $plugins );
  }

  function import_acf_data()
  {
    //Include the /acf folder in the places to look for ACF Local JSON files
    add_filter('acf/settings/load_json', function($paths) {
      $paths[] = dirname( __FILE__ ).'/models';
      return $paths;
    });
    add_action('init',[$this,'sync_acf_data']);
  }

  function sync_acf_data() {
    $groups = acf_get_field_groups();
    foreach( $groups as $group ) {
      acf_import_field_group($group);
    }
  }

}

new SetupTestDb;