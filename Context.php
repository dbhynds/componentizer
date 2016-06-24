<?php

namespace Componentizer;

class Context {

  public function set() {
    $numargs = func_num_args();
    if ($numargs) {
      $args = func_get_args();
      if ($numargs == 1 && is_array($args[0])) {
        $values = array_pop($args);
        foreach ($values as $key => $value) {
          $this->set($key,$value);
          $return[$key] = $this->$key;
        }
        return $values;
      } elseif ($numargs == 2 && is_string($args[0]) && is_string($args[1]) ) {
        $property = array_shift($args);
        $value = array_shift($args);
        $this->$property = $value;
        return $value;
      }
    }
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
