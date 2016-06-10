<?php

namespace Componentizer;

class Context {

  private $lock = false;

  public function __set($name, $value) {
    if ($this->lock) {
      $this->$name = $value;
    }
    return $this->$name;
  }
  public function setProperty($property, $value, $unlock = false) {
    if (!property_exists($this, $property) || $unlock) {
      $this->lock = true;
      $this->$property = $value;
      $this->lock = false;
    }
    return $this->$property;
  }
  public function setProperties(Array $values, $unlock = false) {
    $return = [];
    foreach ($values as $key => $value) {
      $this->setProperty($key,$value,$unlock);
      $return[$key] = $this->$key;
    }
    return $return;
  }

  public function toArray() {
    return get_object_vars($this);
  }
  public function render($twig) {
    return \Timber::render($twig,$this->toArray());
  }
  
}
