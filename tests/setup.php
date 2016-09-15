<?php

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

Class SetupTestDb {

  function __construct()
  {
    tests_add_filter( 'muplugins_loaded', [$this,'manually_load_plugins'] );
    tests_add_filter( 'muplugins_loaded', [$this,'import_acf_data'] );
    tests_add_filter( 'muplugins_loaded', [$this,'set_componentizer_options'] );
    tests_add_filter( 'muplugins_loaded', [$this,'set_theme'] );
  }

  function manually_load_plugins()
  {
    $plugins = [
      'componentizer/componentizer.php',
      'advanced-custom-fields-pro/acf.php',
      'timber-library/timber.php'
    ];
    foreach ($plugins as $plugin) {
      require dirname( dirname( dirname( __FILE__ ) ) ) .'/'. $plugin;
    }
    update_option( 'active_plugins', $plugins );
  }

  function set_theme()
  {
    register_theme_directory( dirname( __FILE__ ) );
    $theme = switch_theme('sample-theme');
  }

  function set_componentizer_options()
  {
    $componentizer_fields = [
      "group_577bd8e2200a8" => [
        "component" => "1",
        "template" => "PageHeader",
        "location" => "top",
      ],
      "group_577bd43036a0f" => [
        "component" => "1",
        "template" => "Content",
        "location" => "sortable",
      ]
    ];
    update_option('componentizer_fields',$componentizer_fields);
    $componentizer_visible_on_archive = ['group_577bd8e2200a8','group_577bd43036a0f'];
    update_option('componentizer_visible_on_archive',$componentizer_visible_on_archive);
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