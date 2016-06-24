<?php

namespace Componentizer;

class Components {

  private $post_id;
  private $suffixes = [];
  private $components = [];

  private $settings;
  private $componentizer_fields;
  private $location_orders;


  function __construct() {
    $this->component_fields = get_option( 'componentizer_fields' );
    $this->settings = get_option('componentizer_advanced_settings');
    $this->location_orders = get_option('componentizer_location_orders');
    if ($this->location_orders === '') $this->location_orders = [];
  }


  /**
   * Build page using appropriate components.
   * @param  array $components  Optional. An array of components to load in order.
   * @param  mixed $suffixes    Optional. A string or array or suffixes which should override the template priority
   */
  public function build() {
    // Locate the approriate component files and load them
    $components = $this->get_components();
    $suffixes = $this->get_suffixes();
    if ($components) foreach ($components as $component) {
      $templates = array($this->settings['component_path'].'/'.$component.'.php');
      foreach ($suffixes as $suffix) {
        array_unshift($templates, $this->settings['component_path'].'/'.$component.'-'.$suffix.'.php');
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


  // /**
  //  * Build a component by passing content to it
  //  * @param  mixed $component_content Required. A string or array of content to be used by the component.
  //  * @param  string $component        Required. The base component to build with.
  //  * @param  mixed $suffixes          Optional. A string or array or suffixes which should override the template priority
  //  */
  // public function build_with($context, $component, $suffixes = null) {
  //   $templates = [];
  //   array_unshift($templates, $this->settings['component_path'].'/'.$component.'.php');
  //   if ($suffixes) foreach ($suffixes as $suffix) {
  //     array_unshift($templates, $this->settings['component_path'].'/'.$component.'-'.$suffix.'.php');
  //   }
  //   $file = locate_template($templates,false,false);
  //   if ($file) include($file);
  // }

  /**
   * [get_components description]
   * @return [type] [description]
   */
  public function get_components() {
    if ($this->components) {
      return $this->components;
    } else {
      $post_id = $this->get_post_id();
      $visible_on_archive = get_option('componentizer_visible_on_archive');
      $components = [];
      $component_ids = get_post_meta( $post_id, '_field_order', true );

      $field_groups = new FieldGroups();
      if (!$component_ids) {
        $component_ids = $field_groups->get_for_post($post_id);
      }
      
      // Set the base components to load as determined by the $component_ids
      $top_components = $field_groups->sort_by_location('top',$component_ids);
      $bottom_components = $field_groups->sort_by_location('bottom',$component_ids);
      $sortable_components = array_diff($component_ids,$top_components,$bottom_components);
      
      $ordered_component_ids = array_merge($top_components,$sortable_components,$bottom_components);
      foreach ($ordered_component_ids as $component_id) {
        if (array_key_exists($component_id,$this->component_fields)) {
          if (is_singular() || in_array($component_id,$visible_on_archive)) {
            array_push($components, $this->component_fields[$component_id]['template']);
          }
        }
      }
      return $components;
    }
  }
  public function set_components(Array $components) {
    return $this->components = $components;
  }
  public function reset_components() {
    $this->components = null;
  }


  public function get_suffixes() {
    if ($this->suffixes) {
      return $this->suffixes;
    } else {
      $suffixes = array('index');
      if (is_admin()) {
        array_unshift($suffixes, 'singular');
        $post_type = get_post_type();
        $post_id = $this->get_post_id();
        if ($post_id == get_option('page_for_posts')) {
          array_unshift($suffixes,'home');
        } elseif ($post_id == get_option('page_on_front')) {
          array_unshift($suffixes, 'front-page');
        } elseif ($post_type === 'page') {
          array_unshift($suffixes, 'page');
          $page_template_slug = get_page_template_slug($post_id);
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
      return $suffixes;
    }
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
  public function set_suffixes(Array $suffixes) {
    return $this->suffixes = $suffixes;
  }
  public function add_suffixes($suffix) {
    $suffixes = $this->get_suffixes();
    if (is_string($suffix)) {
      array_unshift($suffixes, $suffix);
    } elseif (is_array($suffix)) {
      $suffixes = array_merge($suffix, $suffixes);
    }
    return $this->suffixes = $suffixes;
  }
  public function reset_suffixes() {
    $this->suffixes = null;
  }

  public function get_post_id() {
    if ($this->post_id) {
      return $this->post_id;
    } else {
      return get_the_ID();
    }
  }
  public function set_post_id($id) {
    return $this->post_id = $id;
  }
  public function reset_post_id() {
    $this->post_id = null;
  }


  private function sort_groups_by_location($location, $component_ids) {
    $local_components = [];
    if (!array_key_exists($location, $this->location_orders)) $this->location_orders[$location] = [];

    foreach ($this->location_orders[$location] as $value) {
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