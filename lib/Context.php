<?php

namespace Componentizer;

class Context {

  protected $twig;

  /**
   * By default, set the twig to the same name as the file that called it
   */
  public function __construct()
  {
    $backtrace = debug_backtrace();
    $last_call = array_shift($backtrace);
    $this->twig = basename($last_call['file'],'.php').'.twig';
  }

  /**
   * Set the various properties to pass to the twig using an associative array.
   * The key name corresponds to the variable name in the twig file.
   * 
   * @param   array $properties  An associative array of properties to set.
   * @return  array              An array of successfully set values, which
   *                             should match $properties.
   */
  public function set($properties)
  {
    foreach ($properties as $key => $value) {
      $this->$key = $value;
      $return[$key] = $this->$key;
    }
    return $return;
  }

  /**
   * Get an array of properties currently set for this context
   * @return  array  An array of properties set for this context
   */
  public function get()
  {
    return get_object_vars($this);
  }

  /**
   * Quickly render the content, without fussing with the fields
   */
  public function simple_render()
  {
    $content = get_fields();
    $this->set($content);
    return \Timber::render($this->twig,$this->get());
  }

  /**
   * Renders a twig using the current context
   * @param  string $twig Optional, defaults to name of the file calling the
   *                      render method.
   * @return null         Echos the twig.
   */
  public function render($twig = null)
  {
    if (!isset($twig)) {
      $twig = $this->twig;
    }
    return \Timber::render($twig,$this->get());
  }

  /**
   * Compiles a twig and returns it as a string using the current context
   * @param  string $twig Optional, defaults to name of the file calling the
   *                      render method.
   * @return string       Returns the output buffer of the twig.
   */
  public function compile($twig = null) {
    if (!isset($twig)) {
      $twig = $this->twig;
    }
    return \Timber::compile($twig,$this->get());
  }
  
}
