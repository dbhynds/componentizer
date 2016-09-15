<?php
/**
 * Class SampleTest
 *
 * @package Componentizer
 */

/**
 * Sample test case.
 */
class ContextTest extends WP_UnitTestCase {

  var $plugin_slug;
  var $context;
  var $post;

  function setUp()
  {
    $this->plugin_slug = dirname( dirname( __FILE__ ) ) . '/componentizer.php';
    parent::setUp();

    $post = $this->factory->post->create();
    do_action('init');
    $this->context = new \Componentizer\Context;
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

  function setUpPost($post_id = null)
  {
    global $post;
    if (!$post_id) {
      $post_id = $this->create_post();
    }
    $post = get_post($post_id);
    setup_postdata($post);
    return $post_id;
  }

  function test_instantiation()
  {
    $this->assertInstanceOf(\Componentizer\Context::class,$this->context);
    $this->assertClassHasAttribute('twig',\Componentizer\Context::class);
    $twig = 'different.twig';
    $context_with_twig = new \Componentizer\Context($twig);
    $attributes = $context_with_twig->get();
    $this->assertEquals($attributes['twig'],$twig);
  }

  function test_set()
  {
    $test_data = ['key' => 'value', 'hash' => 'd41d8cd98f00b204e9800998ecf8427e'];
    $returned_data = $this->context->set($test_data);
    $this->assertEquals($returned_data,$test_data);
  }

  function test_get()
  {
    $test_data = ['key' => 'value', 'hash' => 'd41d8cd98f00b204e9800998ecf8427e'];
    $this->context->set($test_data);
    $test_data['twig'] = 'test-context.twig';
    $returned_data = $this->context->get();
    $this->assertEquals($returned_data,$test_data);
  }

}
