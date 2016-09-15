<?php

namespace Componentizer\Controllers;

use Componentizer\Context as Context;

Class Content extends \Componentizer\Controller {

  function index()
  { 
    $context = new Context;
    $context->simpleRender();
  }

}
