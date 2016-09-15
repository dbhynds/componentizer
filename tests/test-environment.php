<?php
/**
 * Class SampleTest
 *
 * @package Componentizer
 */

/**
 * Sample test case.
 */
class EnvironmentTest extends WP_UnitTestCase {

  var $plugin_slug;

  function setUp()
  {
    // You must set the path to your plugin here.
    $this->plugin_file = dirname( dirname( __FILE__ ) ) . '/componentizer.php';

    // Don't forget to call the parent's setUp(), or the plugin won't get installed.
    parent::setUp();
  }

  function test_plugins()
  {
    $plugins_to_active = [
      'componentizer/componentizer.php',
      'advanced-custom-fields-pro/acf.php',
      'timber-library/timber.php'
    ];
    foreach ($plugins_to_active as $plugin) {
      $this->assertTrue(is_plugin_active($plugin));
    }
  }

  function test_theme()
  {
    $this->assertEquals('sage',wp_get_theme());
  }
}
