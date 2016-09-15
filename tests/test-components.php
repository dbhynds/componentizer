<?php
/**
 * Class SampleTest
 *
 * @package Componentizer
 */

/**
 * Sample test case.
 */
class ComponentsTest extends WP_UnitTestCase {

  var $plugin_slug;
  var $components;
  var $post;

  function setUp()
  {
    $this->plugin_file = dirname( dirname( __FILE__ ) ) . '/componentizer.php';
    parent::setUp();

    $post = $this->factory->post->create();
    do_action('init');
    // $this->import_acf_data();
    $this->components = new \Componentizer\Components;
  }

  function create_post( $post_data = [] )
  {
    $defaults = [
      'post_title' => 'Test post',
      'post_status' => 'publish',
      'post_author' => 1,
    ];
    $post = wp_insert_post( array_merge($defaults,$post_data) );
    add_post_meta($post,'_field_order',['group_577bd8e2200a8','group_577bd43036a0f']);
    return $post;
  }

  function test_components_object()
  {
    $this->assertInstanceOf(\Componentizer\Components::class,$this->components);
  }

  function test_post_setup()
  {
    $post = $this->create_post();
    $this->assertInternalType('integer',$post);
    $meta = get_post_meta($post,'_field_order',true);
    $this->assertContains('group_577bd8e2200a8',$meta);
    $this->assertContains('group_577bd43036a0f',$meta);
  }

  function test_getPostId()
  {
    global $post;
    $post_id = $this->create_post();
    $post = get_post($post_id);
    setup_postdata($post);
    $this->assertInternalType('integer',$this->components->getPostId());
    $this->assertEquals($post_id,$this->components->getPostId());
  }

}
