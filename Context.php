<?php

namespace Componentizer;

class Context {

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
  public function render($twig) {
    return \Timber::render($twig,$this->get());
  }
  public function compile($twig) {
    return \Timber::compile($twig,$this->get());
  }
  
}
