<?php

namespace Componentizer;

class Context {

  public function setProperty($property, $value) {
    $this->$property = $value;
    return $this->$property;
  }
  public function setProperties(Array $values) {
    $return = [];
    foreach ($values as $key => $value) {
      $this->setProperty($key,$value);
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
