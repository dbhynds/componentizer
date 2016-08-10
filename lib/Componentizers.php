<?php

namespace Componentizer;

class Componentizer {

  function __construct()
  {
    if (is_admin()) {
      new Componentizer\Admin\Admin;
      new Componentizer\Admin\EditorPage;
      new Componentizer\Admin\SettingsPage;
    }
  }

}