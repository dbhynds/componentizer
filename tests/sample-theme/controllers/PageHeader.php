<?php

namespace Componentizer\Controllers;

use Componentizer\Context as Context;

use Roots\Sage\Titles;

Class PageHeader extends \Componentizer\Controller {

  function index()
  {
    $context = new Context;
    $context->link = get_the_permalink();
    $context->simpleRender();
  }

  function singular()
  {
    $context = new Context;
    $context->simpleRender();
  }

}