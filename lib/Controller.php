<?php

namespace Componentizer;

class Controller {

  private $settings;

  public function initialize()
  {
    foreach (Suffixes::$suffixes as $suffix) {
      if (method_exists($this, $suffix)) {
        break;
      }
    }
    $reflect = new \ReflectionClass($this);
    $this->twig = $reflect->getShortName().'.twig';
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

}