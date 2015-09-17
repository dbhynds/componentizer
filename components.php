<?php

namespace Components;

use Components\Options as Options;

/**
 * Build page using appropriate components.
 * @param  array $components  Optional. An array of components to load in order.
 * @param  mixed $suffixes    Optional. A string or array or suffixes which should override the template priority
 */
function build($components = false, $suffixes = null) {
  global $post;

  // Get the base components and their associated field groups
  $component_fields = get_option( 'componentizer_fields' );

  // If $components are specifically specified, use the posts' custom order.
  if ($components === false) {
    $location_orders = get_option('componentizer_location_orders');
    $visible_on_archive = get_option('componentizer_visible_on_archive');
    $components = array();
    $component_ids = get_post_meta( $post->ID, '_field_order', true );
    // Set the base components to load as determined by the $component_ids
    if ($component_ids) {
      $top_components = $sortable_components = $bottom_components = array();

      foreach ($location_orders['top'] as $value) {
        $component = array_search($value, $component_ids);
        if ($component !== false) {
          array_push($top_components, $value);
          unset($component_ids[$component]);
        }
        unset($component);
      }
      foreach ($location_orders['bottom'] as $value) {
        $component = array_search($value, $component_ids);
        if ($component !== false) {
          array_push($bottom_components, $value);
          unset($component_ids[$component]);
        }
        unset($component);
      }
      $ordered_component_ids = array_merge($top_components,$component_ids,$bottom_components);

      foreach ($ordered_component_ids as $component_id) {
        if (array_key_exists($component_id,$component_fields)) {
          if (is_singular() || in_array($component_id,$visible_on_archive)) {
            array_push($components, $component_fields[$component_id]['template']);
          }
        }
      }
    }
  }
  
  // Get the list of suffixes to try and load
  $suffixes = get_suffixes($suffixes);
  
  // Locate the approriate component files and load them
  if ($components) foreach ($components as $component) {
    $templates = array(Options\COMPONENT_PATH.'/'.$component.'.php');
    foreach ($suffixes as $suffix) {
      array_push($templates, Options\COMPONENT_PATH.'/'.$component.'-'.$suffix.'.php');
    }
    $file = locate_template($templates,false,false);
    if ($file) include($file);
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
function get_suffixes($last_suffix = false) {
  $suffixes = array('index');
  if (is_home()) {
    array_unshift($suffixes,'home');
    if (is_front_page()) {
      array_unshift($suffixes, 'front-page');
    } 
  } elseif (is_search()) {
    array_unshift($suffixes, 'search');
  } elseif (is_404()) {
    array_unshift($suffixes, '404');
  } elseif (is_archive()) {
    if (is_paged()) {
      array_unshift($suffixes, 'paged');
    }
    array_unshift($suffixes, 'archive');
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
  } elseif (is_singular()) {
    array_unshift($suffixes, 'singular');
    if (is_single()) {
      array_unshift($suffixes, 'single');
      if (is_attachment()) {
        array_unshift($suffixes, 'attachment');
      } elseif (get_post_type()) {
        array_unshift($suffixes, get_post_type());
        array_unshift($suffixes, 'single-'.get_post_type());
      }
    } elseif (is_page()) {
      array_unshift($suffixes, 'page');
      $page_template_slug = get_page_template_slug();
      if ($page_template_slug !== '') {
        $page_template_slug = str_replace('.php', '', $page_template_slug);
        array_unshift($suffixes, $page_template_slug);
      }
    }
  }
  if ($last_suffix) {
    if (is_string($last_suffix)) {
      array_unshift($suffixes, $last_suffix);
    } elseif (is_array($last_suffix)) {
      $suffixes = array_merge($suffixes, $last_suffix);
    }
  }
  return $suffixes;
}