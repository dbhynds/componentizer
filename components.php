<?php

namespace Components;

class Componentizer {

  private $post_id;
  private $suffixes = [];
  private $components = [];

  function __construct() {
    $this->post_id = $this->set_post_id();
    $this->components = $this->set_components();
    $this->suffixes = $this->set_suffixes();
  }

  /**
   * Build page using appropriate components.
   * @param  array $components  Optional. An array of components to load in order.
   * @param  mixed $suffixes    Optional. A string or array or suffixes which should override the template priority
   */
  public function build() {
    // Locate the approriate component files and load them
    if ($this->components) foreach ($this->components as $component) {
      $templates = array(COMPONENT_PATH.'/'.$component.'.php');
      foreach ($this->suffixes as $suffix) {
        array_unshift($templates, COMPONENT_PATH.'/'.$component.'-'.$suffix.'.php');
      }
      $file = locate_template($templates,false,false);
      if ($file) include($file);
    }
  }

  /**
   * [get_build description]
   * @return [type] [description]
   */
  public function get_build() {
    ob_start();
    $this->build();
    $contents = ob_get_clean();
    return $contents;
  }

  /**
   * Build a component by passing content to it
   * @param  mixed $component_content Required. A string or array of content to be used by the component.
   * @param  string $component        Required. The base component to build with.
   * @param  mixed $suffixes          Optional. A string or array or suffixes which should override the template priority
   */
  public static function build_with($context, $component, $suffixes = null) {
    $templates = [];
    array_unshift($templates, COMPONENT_PATH.'/'.$component.'.php');
    if ($suffixes) foreach ($suffixes as $suffix) {
      array_unshift($templates, COMPONENT_PATH.'/'.$component.'-'.$suffix.'.php');
    }
    $file = locate_template($templates,false,false);
    if ($file) include($file);
  }

  public function set_components($components = false) {
    if (!$this->post_id) $this->set_post_id();
    // Get the base components and their associated field groups
    $component_fields = get_option( 'componentizer_fields' );

    // If $components are specifically specified, use the posts' custom order.
    if ($components === false) {
      $location_orders = get_option('componentizer_location_orders');
      if ($location_orders === '') $location_orders = [];
      $visible_on_archive = get_option('componentizer_visible_on_archive');
      $components = [];
      $component_ids = get_post_meta( $this->post_id, '_field_order', true );

      if (!$component_ids) {
        $component_ids = FieldGroups::get_for_post($this->post_id);
      }
      
      // Set the base components to load as determined by the $component_ids
      $top_components = FieldGroups::sort_by_location('top',$component_ids);
      $bottom_components = FieldGroups::sort_by_location('bottom',$component_ids);
      $sortable_components = array_diff($component_ids,$top_components,$bottom_components);
      
      $ordered_component_ids = array_merge($top_components,$sortable_components,$bottom_components);
      foreach ($ordered_component_ids as $component_id) {
        if (array_key_exists($component_id,$component_fields)) {
          if (is_singular() || in_array($component_id,$visible_on_archive)) {
            array_push($components, $component_fields[$component_id]['template']);
          }
        }
      }
    }

    $this->components = $components;
    return $this->components;
  }
  public function get_components() {
    return $this->components;
  }

  /**
   * Setup a hierarchy of suffixes based loosely on WP's template hierarchy
   * @param  mixed $last_suffix   Optional. A string or array of suffixes that take priority over the generated hierarcy
   * @return array                An array of suffixes used to determine the template hierarcy
   * 
   * This page roughly details the hierarchy of suffixes created in this function:
   * https://developer.wordpress.org/themes/basics/template-hierarchy/#visual-overview
   * 
   * As a general rule, the suffix hierarchy generated captures all of the dark and light blue nodes
   * in the template hierarchy. Most suffixes associated with orange nodes will not be generated, notably
   * those representing the slug or id of a post. Exceptions are  'archive-$posttype.php' and
   * 'custom.php'. This function will generate a suffix for custom post type archives and custom page
   * templates
   */
  public function set_suffixes($first_suffix = false) {
    $suffixes = array('index');
    if (is_admin()) {
      array_unshift($suffixes, 'singular');
      $post_type = get_post_type();
      $post_id = get_the_ID();
      if ($post_id == get_option('page_for_posts')) {
        array_unshift($suffixes,'home');
      } elseif ($post_id == get_option('page_on_front')) {
        array_unshift($suffixes, 'front-page');
      } elseif ($post_type === 'page') {
        array_unshift($suffixes, 'page');
        $page_template_slug = get_page_template_slug();
        if ($page_template_slug !== '') {
          $page_template_slug = str_replace('.php', '', $page_template_slug);
          array_unshift($suffixes, $page_template_slug);
        }
      } else {
        array_unshift($suffixes, 'single');
        if ($post_type === 'attachment') {
          array_unshift($suffixes, 'attachment');
        } else {
          array_unshift($suffixes, $post_type);
          array_unshift($suffixes, 'single-'.$post_type);
        }
      }
    } else {
      if (is_search()) {
        array_unshift($suffixes, 'search');
      } elseif (is_404()) {
        array_unshift($suffixes, '404');
      } elseif (is_comments_popup()) {
        array_unshift($suffixes, 'comments-popup');
      } elseif (is_home()) {
        array_unshift($suffixes,'home');
        if (is_front_page()) {
          array_unshift($suffixes, 'front-page');
        } 
      } elseif (is_singular()) {
        array_unshift($suffixes, 'singular');
        if (is_page()) {
          array_unshift($suffixes, 'page');
          $page_template_slug = get_page_template_slug();
          if ($page_template_slug !== '') {
            $page_template_slug = str_replace('.php', '', $page_template_slug);
            array_unshift($suffixes, $page_template_slug);
          }
        } elseif (is_single()) {
          array_unshift($suffixes, 'single');
          if (is_attachment()) {
            array_unshift($suffixes, 'attachment');
          } elseif (get_post_type()) {
            array_unshift($suffixes, get_post_type());
            array_unshift($suffixes, 'single-'.get_post_type());
          }
        }
      } elseif (is_archive()) {
        array_unshift($suffixes, 'archive');
        if (is_paged()) {
          array_unshift($suffixes, 'paged');
        }
        if (is_author()) {
          array_unshift($suffixes, 'author');
        } elseif (is_category()) {
          array_unshift($suffixes, 'category');
        } elseif (is_tag()) {
          array_unshift($suffixes, 'tag');
        } elseif (is_tax()) {
          array_unshift($suffixes, 'taxonomy');
          $queried_object = get_queried_object();
          if ($queried_object && isset($queried_object->taxonomy)) {
            array_unshift($suffixes, 'taxonomy-'.$queried_object->taxonomy);
          }
        } elseif (is_date()) {
          array_unshift($suffixes, 'date');
        } elseif (get_post_type()) {
          array_unshift($suffixes, get_post_type());
          array_unshift($suffixes, 'archive-'.get_post_type());
        }
      }
    }
    if ($first_suffix) {
      if (is_string($first_suffix)) {
        array_unshift($suffixes, $first_suffix);
      } elseif (is_array($first_suffix)) {
        $suffixes = array_merge($first_suffix, $suffixes);
      }
    }
    $this->suffixes = $suffixes;
    return $this->suffixes;
  }
  public function get_suffixes() {
    return $this->suffixes;
  }

  public function set_post_id($id = null) {
    if ($id) {
      return $this->post_id = $id;
    } else {
      return $this->post_id = get_the_ID();
    }
  }
  public function get_post_id() {
    return $this->post_id;
  }

  private function sort_groups_by_location($location, $component_ids) {
    $local_components = [];
    $location_orders = get_option('componentizer_location_orders');
    if ($location_orders === '') $location_orders = [];
    if (!array_key_exists($location, $location_orders)) $location_orders[$location] = [];

    foreach ($location_orders[$location] as $value) {
      $component = array_search($value, $component_ids);
      if ($component !== false) {
        array_push($local_components, $value);
        unset($component_ids[$component]);
      }
      unset($component);
    }

    return $local_components;
  }

}