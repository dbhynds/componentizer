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

  function test_components_object()
  {
    $this->assertInstanceOf(\Componentizer\Components::class,$this->components);
  }

  function test_post_setup()
  {
    $post_id = $this->setUpPost();
    $this->assertInternalType('integer',$post_id);
    $meta = get_post_meta($post_id,'_field_order',true);
    $this->assertContains('group_577bd8e2200a8',$meta);
    $this->assertContains('group_577bd43036a0f',$meta);
  }

  function test_getPostId()
  {
    $post_id = $this->setUpPost();
    $this->assertInternalType('integer',$this->components->getPostId());
    $this->assertEquals($post_id,$this->components->getPostId());
  }

  function test_setPostId()
  {
    $post_id = $this->setUpPost();
    $this->assertEquals($post_id,$this->components->getPostId());
    $new_id = $this->components->setPostId($post_id + 1);
    $this->assertEquals($new_id, $post_id + 1);
    $this->assertEquals($new_id, $this->components->getPostId());
  }

  function test_resetPostId()
  {
    $post_id = $this->setUpPost();
    $this->components->setPostId($post_id + 1);
    $this->components->resetPostId();
    $this->assertEquals($post_id, $this->components->getPostId());
  }

  function test_getComponents()
  {
    $post_id = $this->setUpPost();
    $components = $this->components->getComponents();
    $this->assertInternalType('array',$components);
    $this->assertCount(2,$components);
    $this->assertEquals(['PageHeader','Content'],$components);
  }

  function test_setComponents()
  {
    $post_id = $this->setUpPost();
    $components = $this->components->getComponents();
    $this->assertEquals(['PageHeader','Content'],$components);
    $new_components = $this->components->setComponents(['Content']);
    $this->assertEquals(['Content'],$new_components);
    $check_components = $this->components->getComponents();
    $this->assertEquals(['Content'],$check_components);
  }

  function test_resetComponents()
  {
    $post_id = $this->setUpPost();
    $new_components = $this->components->setComponents(['Content']);
    $this->assertEquals(['Content'],$new_components);
    $this->components->resetComponents();
    $components = $this->components->getComponents();
    $this->assertEquals(['PageHeader','Content'],$components);
  }

}
