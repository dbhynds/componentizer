<?php

namespace Componentizer;

class Controller {

  private $settings;
  protected $context;

  public function initialize()
  {
    foreach (Suffixes::$suffixes as $suffix) {
      if (method_exists($this, $suffix)) {
        break;
      }
    }
    $reflect = new \ReflectionClass($this);
    $this->twig = $reflect->getShortName().'.twig';
    $this->context = new Context($this->twig);
    $this->$suffix();
  }

  /**
   * Quickly render the content, without fussing with the fields
   * @param  integer $id The ID of the post you wish to render
   */
  public function simpleRender($id = null)
  {
    $content = get_fields($id);
    if ($content) {
      return \Timber::render($this->twig,$content);
    }
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
  private function getTemplateFile($path, $component, $suffixes, $extension = 'php') {
    $templates = [];
    foreach ($suffixes as $suffix) {
      array_push($templates, $path.'/'.$component.'-'.$suffix.'.'.$extension);
    }
    array_push($templates, $path.'/'.$component.'.'.$extension);
    $file = locate_template($templates,false,false);
    return $file;
  }


  /**
   * Sort a list of component IDs according to their appropriate location
   * @param  string $location     Can be 'top' or 'bottom'
   * @param  array $component_ids A list of component IDs
   * @return array                Component IDs matching this location
   */
  private function sortGroupsByLocation($location, $component_ids)
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