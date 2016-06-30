<?php
/**
 * Class ComponentizerTest
 *
 * @package 
 */

/**
 * Sample test case.
 */
class ComponentizerTest extends WP_UnitTestCase {

  function __construct() {
    componentizer_activate();
  }

  function set_up_post($num = 1) {
    $post_id = $this->factory->post->create();
    $this->factory->post->create();
    $query = new WP_Query( array( 'post__in' => array( $post_id ) ) );
    while ( $query->have_posts() ) {
      $query->the_post();
    }
  }

	/**
	 * A single example test.
	 */
	function test_test_suite() {
		// Replace this with some actual testing code.
		$this->assertTrue( true );
	}

  function test_componentizer() {
    $this->assertInstanceOf(Componentizer\Components::class, new Componentizer\Components);
  }

  function test_suffixes() {
    $components = new Componentizer\Components();
    $suffixes = $components->get_suffixes();
    $this->assertInternalType('array',$suffixes);
    $components->set_suffixes(['last']);
    $suffixes = $components->get_suffixes();
    $this->assertEquals('last',array_shift($suffixes));
  }

  function test_components() {
    $components = new Componentizer\Components();
    $gotten_components = $components->get_components();
    $this->assertInternalType('array',$gotten_components);
    $components->set_components(['content']);
    $gotten_components = $components->get_components();
    $this->assertEquals('content',array_shift($gotten_components));
  }

  function test_post_id() {
    $this->set_up_post();
    $components = new Componentizer\Components();
    $post_id = $components->get_post_id();
    $this->assertInternalType('integer',$post_id);
    $components->set_post_id(6);
    $post_id = $components->get_post_id();
    $this->assertEquals(6,$post_id);
  }

  function test_build() {
    $this->set_up_post();
    $components = new Componentizer\Components();
    $components->set_components(['test']);
    $this->expectOutputString('test');
    $components->build();
  }

}

