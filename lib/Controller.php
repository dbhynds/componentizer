<?php

namespace Componentizer;

class Controller {

  private $settings;

  function __construct()
  {
    
  }


  /**
   * Build page using appropriate components.
   * @param  array $components  Optional. An array of components to load in order.
   * @param  mixed $suffixes    Optional. A string or array or suffixes which should override the template priority
   */
  public function render()
  {
    // Locate the approriate component files and load them
    $components = $this->get_components();
    $suffixes = $this->get_suffixes();
    $settings = get_option('componentizer_advanced_settings');
    $file = $this->get_template_file($settings['component_path'],$component,$suffixes);
    if ($file) {
      include($file);
    } else {
      $file = $this->get_template_file(\Timber::$dirname,$component,$suffixes,'twig');
      if ($file) {
        $context = new Context($file);
        $context->simple_render();
      }
    }
  }

  /**
   * Quickly render the content, without fussing with the fields
   * @param  integer $id The ID of the post you wish to render
   */
  public function simple_render($id = null)
  {
    $content = get_fields($id);
    return \Timber::render($this->twig,$content);
  }

  /**
   * Renders a twig using the current context
   * @param  string $twig Optional, defaults to name of the file calling the
   *                      render method.
   * @return null         Echos the twig.
   */
  /*public function render($twig = null)
  {
    if (!isset($twig)) {
      $twig = $this->twig;
    }
    return \Timber::render($twig,$this->get());
  }*/

  /**
   * Locate the best template file for a component and it's suffixes
   * @param  string $path      Directory to search within the theme
   * @param  string $component Basename of of the component
   * @param  array  $suffixes  Suffixes
   * @param  string $extension File extension of the component
   * @return string|boolean    Path of the file to include or false
   */
  private function get_template_file($path, $component, $suffixes, $extension = 'php') {
    $templates = [];
    foreach ($suffixes as $suffix) {
      array_push($templates, $path.'/'.$component.'-'.$suffix.'.'.$extension);
    }
    array_push($templates, $path.'/'.$component.'.'.$extension);
    $file = locate_template($templates,false,false);
    return $file;
  }

  /**
   * Setup a hierarchy of suffixes based loosely on WP's template hierarchy
   * @return array An array of suffixes used to determine the template hierarcy
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
  public function get_suffixes()
  {
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
   * Manually set the list of suffixes, disregarding any current template suffixes
   * @param array $suffixes New list of suffixes
   * @return array        The new list of suffixes
   */
  public function set_suffixes(Array $suffixes)
  {
    return $this->suffixes = $suffixes;
  }
  /**
   * Append a suffix or suffixes the current list of suffixes
   * @param mixed $suffix A string or array of suffix names
   * @return array        The new list of suffixes
   */
  public function add_suffixes($suffix)
  {
    $suffixes = $this->get_suffixes();
    if (is_string($suffix)) {
      array_unshift($suffixes, $suffix);
    } elseif (is_array($suffix)) {
      $suffixes = array_merge($suffix, $suffixes);
    }
    return $this->suffixes = $suffixes;
  }
  /**
   * Reset the suffixes back to their original state after calling the
   * set_suffixes() or add_suffixes() methods.
   */
  public function reset_suffixes()
  {
    $this->suffixes = null;
  }

  /**
   * Get the ID of the current post
   * @return integer ID of the current post
   */
  public function get_post_id()
  {
    if ($this->post_id) {
      return $this->post_id;
    } else {
      return get_the_ID();
    }
  }
  /**
   * Set the ID of the post to use componentizer for
   * @param integer $id ID of the new post
   */
  public function set_post_id($id)
  {
    return $this->post_id = $id;
  }
  /**
   * Reset the ID of the post.
   */
  public function reset_post_id()
  {
    $this->post_id = null;
  }


  /**
   * Sort a list of component IDs according to their appropriate location
   * @param  string $location     Can be 'top' or 'bottom'
   * @param  array $component_ids A list of component IDs
   * @return array                Component IDs matching this location
   */
  private function sort_groups_by_location($location, $component_ids)
  {
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