<?php

namespace Componentizer;

class Context {

  protected $twig;

  public function __construct() {
    $backtrace = debug_backtrace();
    $last_call = array_shift($backtrace);
    $this->twig = basename($last_call['file'],'.php').'.twig';
  }

  public function set($properties) {
    foreach ($properties as $key => $value) {
      $this->$key = $value;
      $return[$key] = $this->$key;
    }
    return $return;
  }

  public function get() {
    return get_object_vars($this);
  }
  public function render($twig = null) {
    if (!isset($twig)) {
      $twig = $this->twig;
    }
    return \Timber::render($twig,$this->get());
  }
  public function compile($twig = null) {
    if (!isset($twig)) {
      $twig = $this->twig;
    }
    return \Timber::compile($twig,$this->get());
  }
  
}
